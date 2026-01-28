<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class HealthCheckTest extends TestCase
{
    public function test_health_check_returns_ok_when_services_are_up(): void
    {
        $response = $this->getJson('/api/health');

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'ok',
                'checks' => [
                    'database' => ['ok' => true],
                    'cache' => ['ok' => true],
                    'storage' => ['ok' => true],
                ]
            ]);
    }

    public function test_health_check_returns_503_when_database_fails(): void
    {
        // Simulamos un fallo en la base de datos desconectando o lanzando excepción
        // Nota: DB::shouldReceive es complicado con conexiones reales en integration tests.
        // Una forma rápida de simular fallo es cambiar la config en tiempo de ejecución:
        config(['database.default' => 'mysql_fake_connection']);

        $response = $this->getJson('/api/health');

        $response->assertStatus(503)
            ->assertJson([
                'status' => 'degraded',
                'checks' => [
                    'database' => ['ok' => false],
                ]
            ]);
    }

    public function test_it_hides_error_details_when_debug_is_false(): void
    {
        config(['app.debug' => false]);
        config(['database.default' => 'mysql_fake_connection']);

        $response = $this->getJson('/api/health');

        // Verificamos que falle, pero que NO incluya la clave 'error'
        $response->assertStatus(503);
        $this->assertArrayNotHasKey('error', $response->json('checks.database'));
    }
}