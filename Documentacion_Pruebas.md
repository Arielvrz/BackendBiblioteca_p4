# Guía de Explicación de Pruebas Automatizadas (Backend Biblioteca)

Este documento está diseñado para explicar detalladamente la estructura, propósito y funcionamiento de las pruebas automatizadas (tests) implementadas en el proyecto. Te servirá como guía y respaldo en caso de que necesites explicarle al maestro cómo y por qué se construyó el sistema de pruebas.

---

## 1. ¿Qué son, dónde están y cómo se ejecutan? 

Las pruebas automatizadas son scripts de código que verifican automáticamente que nuestro sistema haga lo que se supone que debe hacer, sin requerir que un humano lo pruebe manualmente botón por botón o endpoint por endpoint usando Postman.

*   **¿Dónde están ubicadas?** 
    En Laravel, las pruebas de integración (que prueban peticiones completas a la API) se encuentran en la carpeta `tests/Feature/`. En nuestro proyecto, están ahí mismo: `tests/Feature/AuthTest.php`, `tests/Feature/LibroTest.php` y `tests/Feature/PrestamoTest.php`.
*   **¿Cómo se ejecutan?** 
    Para correr todas las pruebas y ver los resultados, simplemente se abre la terminal en la raíz del proyecto y se ejecuta el comando:
    ```bash
    php artisan test
    ```
    Esto levantará el entorno, ejecutará cada archivo de prueba uno por uno, y mostrará en la terminal un reporte verde ("PASS") si todo está bien, o rojo ("FAIL") si alguna funcionalidad falló.

---

## 2. ¿Qué cubre exactamente cada archivo de prueba?

Hemos construido las pruebas de manera modular para evaluar los 3 flujos más importantes del sistema. Cada archivo se encarga de un dominio específico:

### A. `tests/Feature/AuthTest.php`
Este archivo se asegura de que la puerta de entrada al sistema (la autenticación vía tokens) funcione correctamente. Cubre lo siguiente:
1.  **Login Exitoso:** Verifica que un usuario con credenciales correctas reciba un Token de acceso (`access_token`) y un OK (Status 200).
2.  **Login Fallido:** Comprueba que si se envía una contraseña incorrecta, el sistema no deje pasar al usuario y lance un error de validación (Status 422).
3.  **Logout:** Verifica que un usuario logueado pueda cerrar sesión (invalidar su token) y reciba un Status 200.
4.  **Ver Perfil:** Comprueba que al enviar el token de autorización en la cabecera (Header), el sistema responda con los detalles del usuario actual.

### B. `tests/Feature/LibroTest.php`
Verifica la gestión del inventario y garantiza que el sistema **respete estrictamente los roles y permisos** (Estudiante, Docente, Bibliotecario) utilizando reglas estrictas de autorización (Status 403 Forbidden). Cubre:
1.  **Crear Libro:** Verifica que *solo* el bibliotecario pueda crear libros nuevos (Status 201). Comprueba que a los estudiantes y docentes se les bloquee el paso (Status 403).
2.  **Listar y Ver Libros:** Verifica que *todos* los roles puedan ver el catálogo general de libros y los detalles de cada uno (Status 200).
3.  **Actualizar Libro:** Verifica que *solo* el bibliotecario pueda editar la información de un libro. Se rechaza al resto de roles.
4.  **Eliminar Libro:** Confirma que *solo* el bibliotecario pueda borrar un libro del sistema. También valida que el libro desaparezca físicamente de la base de datos de pruebas.

### C. `tests/Feature/PrestamoTest.php`
Comprueba que el flujo de circulación de libros (prestar y devolver) funcione según las reglas de negocio de la biblioteca:
1.  **Solicitar Préstamo:** Verifica que los 'estudiantes' y 'docentes' puedan solicitar un libro exitosamente (Status 201), pero comprueba que un 'bibliotecario' no tiene permitido crear préstamos para sí mismo (Status 403).
2.  **Devolver Libro:** Valida que un estudiante o docente pueda marcar como "devuelto" (`return_at`) un préstamo que tenía asignado (Status 200).
3.  **Historial de Préstamos:** Comprueba que se pueda consultar la lista de préstamos activos exitosamente.

---

## 3. ¿Por qué se hizo? (El "Por qué")

Las pruebas automatizadas no son solo un requisito técnico, sino una garantía de calidad. Las realizamos por las siguientes razones clave que puedes exponer al maestro:

- **Prevención de Regresiones**: Aseguran que si en el futuro se añade nuevo código o se modifica el existente, las funciones vitales (como el inicio de sesión o la creación de préstamos) no fallen por error.
- **Validación de la Seguridad (Roles y Permisos)**: La API utiliza el paquete *Spatie Permission*. Las pruebas garantizan estrictamente que las reglas de negocio se cumplan. Probar el código HTTP con el error `403 Forbidden` confirma que el sistema está protegido y no permite a roles equivocados realizar acciones críticas.
- **Ahorro de tiempo a largo plazo**: En lugar de usar herramientas manuales como Postman cada vez que se hace un cambio, con un solo comando en la terminal se evalúan decenas de escenarios en cuestión de segundos.

---

## 4. ¿Cómo funcionan las pruebas por dentro? (El "Cómo")

Las pruebas en Laravel se realizan levantando una versión "simulada" de la aplicación y realizando peticiones HTTP internamente sin afectar el servidor real ni la base de datos que está usando el front-end. 

Estos son los conceptos clave de cómo están programadas. Cabe destacar que usamos **Pest PHP** (sintaxis moderna) para Libros y Préstamos, y **PHPUnit** (sintaxis tradicional) para la Autenticación:

### A. La Base de Datos Fresca (`RefreshDatabase`)
En todos y cada uno de los archivos de prueba notarás el uso de `RefreshDatabase`.
* **¿Qué hace?** Antes de ejecutar cada prueba individual, borra todas las tablas de la base de datos temporal de testing y ejecuta las migraciones desde cero. 
* **¿Por qué?** Para garantizar un entorno "limpio", sin que la "basura" creada por una prueba afecte a la siguiente.

### B. Fabricación de Datos Ficticios (Factories)
* **¿Qué es?** En lugar de inventar correos y contraseñas manualmente, utilizamos *Model Factories* (por ejemplo, `User::factory()->create()`). Esto crea registros basura válidos directamente en la base de datos.
* **Beneficio:** Nos permite escribir pruebas muy cortas y enfocadas.

### C. La Configuración Inicial (`beforeEach`)
En los test de Pest (`LibroTest` y `PrestamoTest`), inicializamos reglas en el bloque `beforeEach()`:
* **¿Qué hace?** Se asegura de que *antes* de que corra cualquier prueba, los roles ('bibliotecario', 'estudiante', 'docente') existan en la base de datos.

### D. La Simulación de Identidad (`actingAs`)
Al hacer peticiones protegidas verás código como `$this->actingAs($user)->postJson(...)`.
* **¿Qué significa?** Le ordena a Laravel: *"Inicia sesión automáticamente con el usuario X y dispara esta petición API"*. Esto nos salva de tener que mandar tokens HTTP manualmente en cada prueba de la API.

### E. Aserciones (Comprobaciones finales)
La prueba debe validar el resultado para pasar o fallar, usando "Aserciones":
1. **Validación de Respuestas HTTP**:
   * `$response->assertStatus(200 / 201)`: Petición o Creación exitosa.
   * `$response->assertStatus(403)`: Prohibido (Rol no válido).
   * `$response->assertStatus(422)`: Error validando campos requeridos.
2. **Validación en Base de Datos**:
   * `$this->assertDatabaseHas(...)` / `$this->assertDatabaseMissing(...)`: Confirma que el registro guardado o borrado efectivamente impactó a la base de datos física, no solo en la respuesta HTTP.

---

## 5. Guion Resumen para el Maestro

Si el maestro te pregunta: **"¿Me puedes explicar tu estrategia de pruebas en los controladores de la API?"**
Puedes responderle algo como:

> *"Implementamos un sistema automatizado de Feature Tests ubicado en `tests/Feature`. Cubrimos los tres pilares del backend: Autenticación, Gestión de Libros y Préstamos. 
> Usamos PHPUnit y Pest para simular peticiones HTTP en un entorno aislado limpiando la base de datos con `RefreshDatabase`. Mediante 'Factories' generamos datos sintéticos al vuelo. 
> El objetivo principal de las pruebas no es solo ver que los controladores guarden datos (status 200/201), sino comprobar estrictamente la seguridad y middleware de nuestro sistema de Roles. Evaluamos intencionalmente que estudiantes, docentes y bibliotecarios sean rechazados con el error HTTP 403 Forbidden cuando intentan acceder a rutas que no les corresponden, y verificamos los cambios con aserciones directas a la base de datos."*
