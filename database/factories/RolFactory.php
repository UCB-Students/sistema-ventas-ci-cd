<?php

namespace Database\Factories;

use App\Models\Rol;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<Rol>
 */
class RolFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Rol::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'codigo' => 'ROL'.Str::random(5), // Generar un código único
            'nombre' => $this->faker->unique()->jobTitle(),
            'descripcion' => $this->faker->sentence(),
            'estado' => $this->faker->boolean(),
        ];
    }
}
