<?php

declare(strict_types=1);

namespace App\Modules\Contratos\Database\Factories;

use App\Modules\Contratos\Domain\Enums\ResponsavelDespesa;
use App\Modules\Contratos\Models\Contrato;
use App\Modules\Contratos\Models\ContratoEncargoFixo;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ContratoEncargoFixo>
 */
final class ContratoEncargoFixoFactory extends Factory
{
    protected $model = ContratoEncargoFixo::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'contrato_id' => Contrato::factory(),
            'tipo' => fake()->randomElement(['iptu', 'condominio']),
            'responsabilidade' => ResponsavelDespesa::Inquilino->value,
            'valor_estimado' => fake()->randomFloat(2, 100, 1000),
            'ativo' => true,
        ];
    }
}
