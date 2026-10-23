<?php

declare(strict_types=1);

namespace App\Modules\Contratos\Database\Factories;

use App\Modules\Contratos\Domain\Enums\ResponsavelDespesa;
use App\Modules\Contratos\Domain\Enums\TipoUtilidade;
use App\Modules\Contratos\Models\Contrato;
use App\Modules\Contratos\Models\ContratoUtilidade;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ContratoUtilidade>
 */
final class ContratoUtilidadeFactory extends Factory
{
    protected $model = ContratoUtilidade::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'contrato_id' => Contrato::factory(),
            'tipo' => fake()->randomElement(array_column(TipoUtilidade::cases(), 'value')),
            'responsabilidade' => ResponsavelDespesa::Inquilino->value,
            'valor_estimado' => fake()->randomFloat(2, 50, 500),
            'observacoes' => null,
            'ativo' => true,
        ];
    }
}
