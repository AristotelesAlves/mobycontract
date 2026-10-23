<?php

declare(strict_types=1);

namespace App\Modules\Imoveis\Database\Factories;

use App\Modules\Imoveis\Models\Imovel;
use App\Modules\Imoveis\Models\Interessado;
use App\Modules\Leads\Models\Lead;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Interessado>
 */
final class InteressadoFactory extends Factory
{
    protected $model = Interessado::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'imovel_id' => Imovel::factory(),
            'lead_id' => null,
            'contrato_id' => null,
            'nome' => fake()->name(),
            'telefone' => fake()->numerify('(##) #####-####'),
            'email' => fake()->safeEmail(),
            'tipo' => 'prospect',
            'status' => 'Novo',
        ];
    }

    public function paraLead(Lead $lead): static
    {
        return $this->state(fn (): array => ['lead_id' => $lead->id]);
    }

    public function paraContrato(int $contratoId): static
    {
        return $this->state(fn (): array => ['contrato_id' => $contratoId]);
    }
}
