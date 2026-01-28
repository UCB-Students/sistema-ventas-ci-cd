<?php

namespace Database\Seeders;

use App\Models\Rol;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserRoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Asegurarse de que los roles existan (PermisosSeeder debe ejecutarse antes)
        $adminRole = Rol::where('codigo', 'ADMIN')->first();
        $vendedorRole = Rol::where('codigo', 'VENDEDOR')->first();

        // Si los roles no existen, lanzar una excepción o advertencia
        if (! $adminRole || ! $vendedorRole) {
            $this->command->error('Los roles ADMIN o VENDEDOR no existen. Asegúrate de ejecutar PermisosSeeder primero.');

            return;
        }

        // Crear usuario Administrador
        $adminUser = User::updateOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Admin User',
                'password' => Hash::make('0123456789'),
                'estado' => true,
            ]
        );
        $adminUser->roles()->syncWithoutDetaching([$adminRole->id]);
        $this->command->info('✓ Usuario Administrador creado y asignado al rol ADMIN.');

        // Crear usuario Vendedor
        $vendedorUser = User::updateOrCreate(
            ['email' => 'vendedor@example.com'],
            [
                'name' => 'Vendedor User',
                'password' => Hash::make('0123456789'),
                'estado' => true,
            ]
        );
        $vendedorUser->roles()->syncWithoutDetaching([$vendedorRole->id]);
        $this->command->info('✓ Usuario Vendedor creado y asignado al rol VENDEDOR.');
    }
}
