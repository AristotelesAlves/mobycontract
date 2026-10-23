<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Modules\Usuarios\Models\User;
use App\Shared\Domain\Enums\Papel;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
final class UserFactory extends Factory
{
    protected $model = User::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nome' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'email_verificado_em' => now(),
            'password' => Hash::make('senha1234'),
            'papel' => Papel::Operador->value,
            'ativo' => true,
            'remember_token' => Str::random(10),
        ];
    }

    public function administrador(): static
    {
        return $this->state(fn (array $atributos): array => [
            'papel' => Papel::Administrador->value,
        ]);
    }

    public function desativado(): static
    {
        return $this->state(fn (array $atributos): array => [
            'ativo' => false,
        ]);
    }
}
