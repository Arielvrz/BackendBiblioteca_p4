<?php

use App\Models\User;
use App\Models\Book;
use Spatie\Permission\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    Role::firstOrCreate(['name' => 'bibliotecario']);
    Role::firstOrCreate(['name' => 'estudiante']);
    Role::firstOrCreate(['name' => 'docente']);
});

test('estudiante no puede crear un libro (403)', function () {
    $estudiante = User::factory()->create();
    $estudiante->assignRole('estudiante');

    $response = $this->actingAs($estudiante)->postJson('/api/v1/books', [
        'title' => 'Test Book',
        'description' => 'Test Description',
        'ISBN' => '1234567890',
        'total_copies' => 5,
        'available_copies' => 5,
    ]);

    $response->assertStatus(403);
});

test('bibliotecario puede crear un libro (201)', function () {
    $bibliotecario = User::factory()->create();
    $bibliotecario->assignRole('bibliotecario');

    $response = $this->actingAs($bibliotecario)->postJson('/api/v1/books', [
        'title' => 'Test Book',
        'description' => 'Test Description',
        'ISBN' => '0987654321',
        'total_copies' => 5,
        'available_copies' => 5,
    ]);

    $response->assertStatus(201);
});

test('docente no puede crear un libro (403)', function () {
    $docente = User::factory()->create();
    $docente->assignRole('docente');

    $response = $this->actingAs($docente)->postJson('/api/v1/books', [
        'title' => 'Test Book',
        'description' => 'Test Description',
        'ISBN' => '1111111111',
        'total_copies' => 5,
        'available_copies' => 5,
    ]);

    $response->assertStatus(403);
});

test('listar libros funciona para todos los roles (200)', function () {
    $user = User::factory()->create();
    $user->assignRole('estudiante');
    
    Book::factory()->count(3)->create();

    $response = $this->actingAs($user)->getJson('/api/v1/books');

    $response->assertStatus(200);
});

test('ver detalle de un libro funciona para todos los roles (200)', function () {
    $user = User::factory()->create();
    $user->assignRole('docente');
    
    $book = Book::factory()->create();

    $response = $this->actingAs($user)->getJson('/api/v1/books/' . $book->id);

    $response->assertStatus(200);
});

test('bibliotecario puede actualizar un libro (200)', function () {
    $bibliotecario = User::factory()->create();
    $bibliotecario->assignRole('bibliotecario');
    
    $book = Book::factory()->create();

    $response = $this->actingAs($bibliotecario)->putJson('/api/v1/books/' . $book->id, [
        'title' => 'Updated Title',
    ]);

    $response->assertStatus(200);
    $this->assertDatabaseHas('books', ['title' => 'Updated Title']);
});

test('estudiante no puede actualizar un libro (403)', function () {
    $estudiante = User::factory()->create();
    $estudiante->assignRole('estudiante');
    
    $book = Book::factory()->create();

    $response = $this->actingAs($estudiante)->putJson('/api/v1/books/' . $book->id, [
        'title' => 'Updated Title',
    ]);

    $response->assertStatus(403);
});

test('bibliotecario puede eliminar un libro (200/204)', function () {
    $bibliotecario = User::factory()->create();
    $bibliotecario->assignRole('bibliotecario');
    
    $book = Book::factory()->create();

    $response = $this->actingAs($bibliotecario)->deleteJson('/api/v1/books/' . $book->id);

    $response->assertStatus(204);
    $this->assertDatabaseMissing('books', ['id' => $book->id]);
});

test('estudiante no puede eliminar un libro (403)', function () {
    $estudiante = User::factory()->create();
    $estudiante->assignRole('estudiante');
    
    $book = Book::factory()->create();

    $response = $this->actingAs($estudiante)->deleteJson('/api/v1/books/' . $book->id);

    $response->assertStatus(403);
});
