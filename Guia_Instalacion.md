# Guía de Configuración Local (Para Desarrolladores)

Si acabas de clonar este repositorio (Backend Biblioteca) y necesitas correrlo en tu máquina local para continuar desarrollando o probando, sigue estos pasos al pie de la letra.

## 1. Requisitos Previos

Asegúrate de tener instalado en tu computadora:
- **PHP** (Versión 8.2 o superior recomendada).
- **Composer** (Gestor de dependencias de PHP).
- **Laravel Herd** (Recomendado para Mac/Windows) o **XAMPP/MAMP** si usas otro entorno.
- **Git** (Para clonar y manejar ramas).
- Un gestor de bases de datos como **DBngin** (si usas Mac con Herd) o **MySQL/MariaDB**.

## 2. Pasos para la Instalación

### Paso 1: Clonar el Repositorio
Abre tu terminal y clona el proyecto en la carpeta de tu preferencia (si usas Herd, idealmente en la carpeta de sitios de Herd).
```bash
git clone <URL_DEL_REPOSITORIO>
cd BackendBiblioteca_p4
```

### Paso 2: Instalar Dependencias de PHP
El código que clonaste no incluye los paquetes y librerías de terceros (la carpeta `vendor` está ignorada por Git). Debes instalarlos:
```bash
composer install
```

### Paso 3: Configurar el Archivo de Entorno (`.env`)
Git tampoco sube el archivo `.env` por seguridad (contiene contraseñas y claves). Sin embargo, Laravel provee un archivo de ejemplo. Haz una copia de ese archivo:

**En Mac/Linux:**
```bash
cp .env.example .env
```

**En Windows (PowerShell/CMD):**
```bash
copy .env.example .env
```

### Paso 4: Generar la Clave de Aplicación (App Key)
Laravel necesita una clave de encriptación única para asegurar las sesiones y datos sensibles. Genera una nueva ejecutando:
```bash
php artisan key:generate
```
*Esto añadirá texto automáticamente a la variable `APP_KEY` dentro de tu nuevo archivo `.env`.*

### Paso 5: Configurar la Base de Datos
Abre el archivo `.env` que acabas de crear en tu editor de código (VS Code, etc.) y busca la sección de base de datos.
Asegúrate de que refleje las credenciales de tu gestor de base de datos local (Herd usa por defecto: usuario `root` y contraseña vacía). 

Ejemplo de configuración común:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=biblioteca_backend  # Asegúrate de crear esta base de datos vacía primero en tu gestor (DBngin, phpMyAdmin, etc)
DB_USERNAME=root
DB_PASSWORD=
```
👉 **IMPORTANTE:** Antes del siguiente paso, debes ir a tu gestor de base de datos y **crear una base de datos** vacía con el nombre que pusiste en `DB_DATABASE` (ej: `biblioteca_backend`).

### Paso 6: Ejecutar las Migraciones y Seeders
Ahora que la base de datos existe, hay que crear las tablas y llenarlas con datos de prueba (Roles, Permisos, Usuarios y Libros). Ejecuta:
```bash
php artisan migrate --seed
```
*Si te pregunta si deseas crear la base de datos (dependiendo de la versión de Laravel), dile que "Yes".*

### Paso 7: Levantar el Servidor Local
Si **NO** estás usando Laravel Herd (que sirve la app automáticamente en `http://backendbiblioteca_p4.test`), entonces necesitas arrancar el servidor de desarrollo integrado de PHP mediante artisan:
```bash
php artisan serve
```
La aplicación estará disponible típicamente en `http://127.0.0.1:8000` o `http://localhost:8000`.

---

## 3. Comandos Útiles

*   **Para correr las pruebas automatizadas (Tests):**
    ```bash
    php artisan test
    ```
*   **Para limpiar las cachés (si notas que tus cambios no se reflejan):**
    ```bash
    php artisan optimize:clear
    ```
*   **Para reiniciar la base de datos desde cero (¡Borra todo!):**
    ```bash
    php artisan migrate:fresh --seed
    ```

## 4. Problemas Comunes (Solución de errores)

1.  **Error 500 al entrar a una ruta:** Verifica nuevamente que ejecutaste `php artisan key:generate`.
2.  **Error de conexión a la Base de Datos (Connection Refused):** Asegúrate de que el servicio de tu base de datos local (DBngin, XAMPP, MySQL) esté "Corriendo / Start".
3.  **No se encuentra una clase o archivo (Class Not Found):** Corre el comando `composer dump-autoload` en la terminal.
