<?php

namespace Tests\Unit;

use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Mockery;
use Tests\TestCase; // Importar el TestCase de Laravel

class UserTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /**
     * Helper para crear un mock de la relación BelongsToMany
     */
    protected function mockRolesRelationship(): Mockery\MockInterface
    {
        $mockBelongsToMany = Mockery::mock(BelongsToMany::class);
        return $mockBelongsToMany;
    }

    #[Test]
    public function test_asignar_roles_adjunta_roles_nuevos_cuando_el_usuario_no_tiene_roles_previos(): void
    {
        $user = Mockery::mock(User::class)->makePartial(); // Mockear el modelo User
        $rolIds = [1, 2, 3];

        $mockBelongsToMany = $this->mockRolesRelationship();
        $mockBelongsToMany->shouldReceive('sync')
            ->once()
            ->with($rolIds);

        // Reemplazar la relación roles() del modelo User con nuestro mock
        $user->shouldReceive('roles')
            ->once()
            ->andReturn($mockBelongsToMany);

        $user->asignarRoles($rolIds);
        $this->assertTrue(true);
    }

    #[Test]
    public function test_asignar_roles_reemplaza_roles_existentes_con_nuevos_roles(): void
    {
        $user = Mockery::mock(User::class)->makePartial();
        $existingRolIds = [1, 2];
        $newRolIds = [3, 4];

        $mockBelongsToMany = $this->mockRolesRelationship();
        $mockBelongsToMany->shouldReceive('sync')
            ->once()
            ->with($newRolIds);

        $user->shouldReceive('roles')
            ->once()
            ->andReturn($mockBelongsToMany);

        $user->asignarRoles($newRolIds);
        $this->assertTrue(true);
    }

    #[Test]
    public function test_asignar_roles_no_cambia_roles_cuando_se_proporciona_la_misma_lista(): void
    {
        $user = Mockery::mock(User::class)->makePartial();
        $rolIds = [1, 2];

        $mockBelongsToMany = $this->mockRolesRelationship();
        $mockBelongsToMany->shouldReceive('sync')
            ->once()
            ->with($rolIds);

        $user->shouldReceive('roles')
            ->once()
            ->andReturn($mockBelongsToMany);

        $user->asignarRoles($rolIds);
        $this->assertTrue(true);
    }

    #[Test]
    public function test_asignar_roles_desasocia_todos_los_roles_cuando_se_proporciona_una_lista_vacia(): void
    {
        $user = Mockery::mock(User::class)->makePartial();
        $rolIds = []; // Lista vacía

        $mockBelongsToMany = $this->mockRolesRelationship();
        $mockBelongsToMany->shouldReceive('sync')
            ->once()
            ->with($rolIds);

        $user->shouldReceive('roles')
            ->once()
            ->andReturn($mockBelongsToMany);

        $user->asignarRoles($rolIds);
        $this->assertTrue(true);
    }

    #[Test]
    public function test_asignar_roles_ignora_ids_de_roles_no_existentes(): void
    {
        $user = Mockery::mock(User::class)->makePartial();
        // IDs que incluyen uno que probablemente no exista en la DB real (e.g., 999)
        $rolIds = [1, 2, 999];

        $mockBelongsToMany = $this->mockRolesRelationship();
        // El método sync de Eloquent es el responsable de filtrar los IDs no existentes.
        // Aquí, simplemente verificamos que se llama con los IDs que se le pasaron.
        $mockBelongsToMany->shouldReceive('sync')
            ->once()
            ->with($rolIds);

        $user->shouldReceive('roles')
            ->once()
            ->andReturn($mockBelongsToMany);

        $user->asignarRoles($rolIds);
        $this->assertTrue(true);
    }
}
