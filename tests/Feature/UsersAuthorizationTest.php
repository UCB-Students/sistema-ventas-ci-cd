<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\PermisosSeeder;
use Database\Seeders\UserRoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class UsersAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    protected $adminUser;
    protected $vendedorUser;
    protected $testUser; // El usuario sin rol específico

    /**
     * Configuración inicial para cada prueba.
     */
    protected function setUp(): void
    {
        parent::setUp();

        // Ejecutar los seeders necesarios para roles, permisos y usuarios de prueba
        $this->seed(PermisosSeeder::class);
        $this->seed(UserRoleSeeder::class);

        // Obtener los usuarios de prueba
        $this->adminUser = User::where('email', 'admin@example.com')->first();
        $this->vendedorUser = User::where('email', 'vendedor@example.com')->first();
        $this->testUser = User::where('email', 'test@example.com')->first(); // Asumiendo que existe o es creado por otro seeder/factory
        
        // Asegurarse de que el usuario testUser no tenga roles de admin o vendedor si no se desea.
        // Si la factory de User::factory()->create() lo crea sin roles, esto está bien.
        // En caso contrario, se podría hacer $this->testUser->roles()->detach();
    }

    /*
    |--------------------------------------------------------------------------
    | Pruebas para Usuarios No Autenticados (Unauthenticated)
    |--------------------------------------------------------------------------
    */

    /** @test */
    public function test_unauthenticated_users_cannot_access_user_index(): void
    {
        $this->getJson('/api/v1/usuarios')
            ->assertUnauthorized(); // 401
    }

    /** @test */
    public function test_unauthenticated_users_cannot_create_users(): void
    {
        $userData = [
            'name' => 'New User',
            'email' => 'newuser@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'estado' => true,
        ];
        $this->postJson('/api/v1/usuarios', $userData)
            ->assertUnauthorized();
    }

    /** @test */
    public function test_unauthenticated_users_cannot_view_single_user(): void
    {
        // Crear un usuario temporal para intentar verlo
        $user = User::factory()->create();
        $this->getJson('/api/v1/usuarios/' . $user->id)
            ->assertUnauthorized();
    }

    /** @test */
    public function test_unauthenticated_users_cannot_update_users(): void
    {
        $user = User::factory()->create();
        $this->putJson('/api/v1/usuarios/' . $user->id, ['name' => 'Updated Name'])
            ->assertUnauthorized();
    }

    /** @test */
    public function test_unauthenticated_users_cannot_delete_users(): void
    {
        $user = User::factory()->create();
        $this->deleteJson('/api/v1/usuarios/' . $user->id)
            ->assertUnauthorized();
    }

    /** @test */
    public function test_unauthenticated_users_cannot_get_roles(): void
    {
        $this->getJson('/api/v1/usuarios/roles')
            ->assertUnauthorized();
    }

    /** @test */
    public function test_unauthenticated_users_cannot_assign_roles(): void
    {
        $user = User::factory()->create();
        $this->patchJson('/api/v1/usuarios/' . $user->id . '/roles', ['roles' => [1]])
            ->assertUnauthorized();
    }

    /** @test */
    public function test_unauthenticated_users_cannot_toggle_user_status(): void
    {
        $user = User::factory()->create();
        $this->patchJson('/api/v1/usuarios/' . $user->id . '/toggle-estado')
            ->assertUnauthorized();
    }

    /*
    |--------------------------------------------------------------------------
    | Pruebas para Usuarios Administradores (Admin User)
    |--------------------------------------------------------------------------
    */

    /** @test */
    public function test_admin_can_access_user_index(): void
    {
        $this->actingAs($this->adminUser, 'sanctum')
            ->getJson('/api/v1/usuarios')
            ->assertOk(); // 200
    }

    /** @test */
    public function test_admin_can_create_users(): void
    {
        $userData = [
            'name' => 'New Admin Created User',
            'email' => 'newadmincreated@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'estado' => true,
        ];

        $response = $this->actingAs($this->adminUser, 'sanctum')
            ->postJson('/api/v1/usuarios', $userData)
            ->assertCreated(); // 201

        $this->assertDatabaseHas('users', ['email' => 'newadmincreated@example.com']);
    }

    /** @test */
    public function test_admin_can_view_single_user(): void
    {
        $user = User::factory()->create();
        $this->actingAs($this->adminUser, 'sanctum')
            ->getJson('/api/v1/usuarios/' . $user->id)
            ->assertOk()
            ->assertJsonFragment(['email' => $user->email]);
    }

    /** @test */
    public function test_admin_can_update_users(): void
    {
        $user = User::factory()->create();
        $updatedName = 'Nombre Actualizado';
        $this->actingAs($this->adminUser, 'sanctum')
            ->putJson('/api/v1/usuarios/' . $user->id, ['name' => $updatedName, 'email' => $user->email]) // email es requerido en update, asumimos que no cambia
            ->assertOk();

        $this->assertDatabaseHas('users', ['id' => $user->id, 'name' => $updatedName]);
    }

    /** @test */
    public function test_admin_can_delete_users(): void
    {
        $user = User::factory()->create();
        $this->actingAs($this->adminUser, 'sanctum')
            ->deleteJson('/api/v1/usuarios/' . $user->id)
            ->assertOk() // 200
            ->assertJson(['message' => 'Usuario eliminado exitosamente']);

        $this->assertDatabaseMissing('users', ['id' => $user->id]);
    }

    /** @test */
    public function test_admin_can_get_roles(): void
    {
        $response = $this->actingAs($this->adminUser, 'sanctum')
            ->getJson('/api/v1/usuarios/roles');

        if ($response->status() === 500) {
            $this->fail('Received 500 error: ' . $response->json('message') . ' - ' . $response->json('error'));
        }

        $response->assertOk()
            ->assertJsonStructure([['id', 'codigo', 'nombre']]); // Asumiendo que devuelve una lista de roles con estos campos
    }

    /** @test */
    public function test_admin_can_assign_roles(): void
    {
        $userToAssign = User::factory()->create();
        $someRole = \App\Models\Rol::factory()->create(); // Crear un rol para asignar

        $this->actingAs($this->adminUser, 'sanctum')
            ->patchJson('/api/v1/usuarios/' . $userToAssign->id . '/roles', ['roles' => [$someRole->id]])
            ->assertOk();

        $this->assertTrue($userToAssign->fresh()->roles->contains($someRole));
    }

    /** @test */
    public function test_admin_can_toggle_user_status(): void
    {
        $userToToggle = User::factory()->create(['estado' => true]); // Crea un usuario activo
        $this->actingAs($this->adminUser, 'sanctum')
            ->patchJson('/api/v1/usuarios/' . $userToToggle->id . '/toggle-estado')
            ->assertOk()
            ->assertJson(['message' => 'Usuario desactivado']); // Espera el mensaje de desactivación, ya que se creó activo

        $this->assertFalse($userToToggle->fresh()->estado); // Verifica que el estado haya cambiado
    }

    /*
    |--------------------------------------------------------------------------
    | Pruebas para Usuarios Sin Permiso (Vendedor User)
    |--------------------------------------------------------------------------
    */

    /** @test */
    public function test_vendedor_cannot_access_user_index(): void
    {
        $this->actingAs($this->vendedorUser, 'sanctum')
            ->getJson('/api/v1/usuarios')
            ->assertForbidden(); // 403
    }

    /** @test */
    public function test_vendedor_cannot_create_users(): void
    {
        $userData = [
            'name' => 'Vendedor Created User',
            'email' => 'vendedorcreated@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'estado' => true,
        ];
        $this->actingAs($this->vendedorUser, 'sanctum')
            ->postJson('/api/v1/usuarios', $userData)
            ->assertForbidden();
    }

    /** @test */
    public function test_vendedor_cannot_view_single_user(): void
    {
        $user = User::factory()->create();
        $this->actingAs($this->vendedorUser, 'sanctum')
            ->getJson('/api/v1/usuarios/' . $user->id)
            ->assertForbidden();
    }

    /** @test */
    public function test_vendedor_cannot_update_users(): void
    {
        $user = User::factory()->create();
        $this->actingAs($this->vendedorUser, 'sanctum')
            ->putJson('/api/v1/usuarios/' . $user->id, ['name' => 'Updated Name', 'email' => $user->email])
            ->assertForbidden();
    }

    /** @test */
    public function test_vendedor_cannot_delete_users(): void
    {
        $user = User::factory()->create();
        $this->actingAs($this->vendedorUser, 'sanctum')
            ->deleteJson('/api/v1/usuarios/' . $user->id)
            ->assertForbidden();
    }

    /** @test */
    public function test_vendedor_cannot_get_roles(): void
    {
        $this->actingAs($this->vendedorUser, 'sanctum')
            ->getJson('/api/v1/usuarios/roles')
            ->assertForbidden();
    }

    /** @test */
    public function test_vendedor_cannot_assign_roles(): void
    {
        $user = User::factory()->create();
        $this->actingAs($this->vendedorUser, 'sanctum')
            ->patchJson('/api/v1/usuarios/' . $user->id . '/roles', ['roles' => [1]])
            ->assertForbidden();
    }

    /** @test */
    public function test_vendedor_cannot_toggle_user_status(): void
    {
        $user = User::factory()->create(['estado' => true]);
        $this->actingAs($this->vendedorUser, 'sanctum')
            ->patchJson('/api/v1/usuarios/' . $user->id . '/toggle-estado')
            ->assertForbidden();
    }

    // Nota: El usuario 'test@example.com' no tiene roles, por lo que su comportamiento
    // debería ser idéntico al del 'vendedor' (403 Forbidden) ya que no tiene los permisos.
    // Podrías duplicar las pruebas de vendedor para este usuario si deseas cobertura explícita.
}
