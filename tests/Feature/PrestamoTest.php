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

test('estudiante puede solicitar un prestamo (201)', function () {
    $estudiante = User::factory()->create();
    $estudiante->assignRole('estudiante');

    $book = Book::factory()->create([
        'available_copies' => 5,
        'is_available' => true,
    ]);

    $response = $this->actingAs($estudiante)->postJson('/api/v1/loans', [
        'requester_name' => 'Juan',
        'book_id' => $book->id,
    ]);

    $response->assertStatus(201);
});

test('docente puede solicitar un prestamo (201)', function () {
    $docente = User::factory()->create();
    $docente->assignRole('docente');

    $book = Book::factory()->create([
        'available_copies' => 5,
        'is_available' => true,
    ]);

    $response = $this->actingAs($docente)->postJson('/api/v1/loans', [
        'requester_name' => 'Pedro',
        'book_id' => $book->id,
    ]);

    $response->assertStatus(201);
});

test('bibliotecario no puede solicitar un prestamo (403)', function () {
    $bibliotecario = User::factory()->create();
    $bibliotecario->assignRole('bibliotecario');

    $book = Book::factory()->create([
        'available_copies' => 5,
        'is_available' => true,
    ]);

    $response = $this->actingAs($bibliotecario)->postJson('/api/v1/loans', [
        'requester_name' => 'Maria',
        'book_id' => $book->id,
    ]);

    $response->assertStatus(403);
});

test('estudiante o docente puede devolver un libro prestado (200)', function () {
    $estudiante = User::factory()->create();
    $estudiante->assignRole('estudiante');

    $book = Book::factory()->create();
    $loan = \App\Models\Loan::factory()->create([
        'book_id' => $book->id,
        'requester_name' => 'Juan'
    ]);

    $response = $this->actingAs($estudiante)->postJson("/api/v1/loans/{$loan->id}/return");

    $response->assertStatus(200);
    $this->assertDatabaseHas('loans', ['id' => $loan->id]);
    $loan->refresh();
    $this->assertNotNull($loan->return_at);
});

test('ver historial de prestamos funciona para estudiante/docente (200)', function () {
    $docente = User::factory()->create();
    $docente->assignRole('docente');

    $response = $this->actingAs($docente)->getJson('/api/v1/loans');

    $response->assertStatus(200);
});
