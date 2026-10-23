<?php

declare(strict_types=1);

namespace App\Modules\Imoveis\Database\Factories;

use App\Modules\Imoveis\Models\Agendamento;
use App\Modules\Imoveis\Models\Imovel;
use App\Modules\Imoveis\Models\Interessado;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Agendamento>
 */
final class AgendamentoFactory extends Factory
{
    protected $model = Agendamento::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'imovel_id' => Imovel::factory(),
            'interessado_id' => Interessado::factory(),
            'data' => now()->addDays(random_int(1, 30))->format('Y-m-d'),
            'hora' => fake()->randomElement(['09:00', '10:00', '14:00', '15:00', '16:00']),
            'telefone_contato' => fake()->numerify('(##) #####-####'),
            'status' => 'Agendado',
        ];
    }
}
