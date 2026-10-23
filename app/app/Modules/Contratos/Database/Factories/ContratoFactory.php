<?php

declare(strict_types=1);

namespace App\Modules\Contratos\Database\Factories;

use App\Modules\Contratos\Domain\Enums\Destinacao;
use App\Modules\Contratos\Domain\Enums\IndiceReajuste;
use App\Modules\Contratos\Domain\Enums\PrazoVigencia;
use App\Modules\Contratos\Domain\Enums\StatusContrato;
use App\Modules\Contratos\Models\Contrato;
use App\Modules\Imoveis\Models\Imovel;
use App\Modules\Inquilinos\Models\Inquilino;
use App\Modules\Modelos\Models\Modelo;
use App\Modules\Usuarios\Models\User;
use App\Shared\Domain\Enums\Garantia;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Contrato>
 */
final class ContratoFactory extends Factory
{
    protected $model = Contrato::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $dataInicio = now()->startOfMonth()->format('Y-m-d');

        return [
            'numero' => fake()->unique()->numerify('2026/####'),
            'tipo_modelo' => 'Contrato',
            'status' => StatusContrato::Rascunho->value,
            'imovel_id' => Imovel::factory(),
            'inquilino_id' => Inquilino::factory(),
            'criado_por' => User::factory(),
            'modelo_id' => Modelo::factory(),
            'valor_mensal' => fake()->randomFloat(2, 800, 5000),
            'desconto_mensal' => 0,
            'multa_por_atraso' => 2,
            'multa_rescisao' => 3,
            'garantia' => Garantia::SemGarantia->value,
            'valor_caucao' => null,
            'destinacao' => Destinacao::Residencial->value,
            'prazo_vigencia' => PrazoVigencia::Determinado->value,
            'data_inicio' => $dataInicio,
            'data_fim' => now()->addYear()->format('Y-m-d'),
            'indice_reajuste' => IndiceReajuste::IPCA->value,
            'anexo_contrato_nome' => null,
            'anexo_contrato' => null,
            'observacoes' => null,
            'motivo_cancelamento' => null,
            'documento_fisico_confirmado' => false,
            'dia_vencimento_parcela' => 5,
            'hash_blockchain' => null,
            'data_registro_blockchain' => null,
            'status_blockchain' => 'Aguardando registro',
        ];
    }

    public function comStatus(StatusContrato $status): static
    {
        return $this->state(fn (): array => ['status' => $status->value]);
    }

    public function comGarantia(Garantia $garantia): static
    {
        return $this->state(fn (): array => [
            'garantia' => $garantia->value,
            'valor_caucao' => $garantia === Garantia::Caucao ? 2000 : null,
        ]);
    }

    public function indeterminado(): static
    {
        return $this->state(fn (): array => [
            'prazo_vigencia' => PrazoVigencia::Indeterminado->value,
            'data_fim' => null,
        ]);
    }
}
