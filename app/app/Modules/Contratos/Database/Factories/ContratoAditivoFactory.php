<?php

declare(strict_types=1);

namespace App\Modules\Contratos\Database\Factories;

use App\Modules\Contratos\Domain\Enums\MotivoAditivo;
use App\Modules\Contratos\Domain\Enums\StatusAditivo;
use App\Modules\Contratos\Models\Contrato;
use App\Modules\Contratos\Models\ContratoAditivo;
use App\Modules\Modelos\Domain\Enums\TipoModelo;
use App\Modules\Modelos\Models\Modelo;
use App\Modules\Usuarios\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ContratoAditivo>
 */
final class ContratoAditivoFactory extends Factory
{
    protected $model = ContratoAditivo::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'contrato_id' => Contrato::factory(),
            'numero' => 'ADT 001/'.date('Y'),
            'titulo' => fake()->sentence(3),
            'data' => now()->toDateString(),
            'descricao' => fake()->paragraph(),
            'criado_por' => User::factory(),
            'status' => StatusAditivo::Rascunho->value,
            'motivos' => [MotivoAditivo::Clausula->value],
            'modelo_id' => Modelo::factory()->comTipo(TipoModelo::Aditivo),
            'dados_extras' => [],
            'upload_confirmado' => false,
            'anexo_nome' => null,
            'hash_blockchain' => null,
            'data_registro_blockchain' => null,
            'status_blockchain' => 'Aguardando registro',
            'motivo_cancelamento' => null,
        ];
    }

    public function comStatus(StatusAditivo $status): static
    {
        return $this->state(fn (): array => ['status' => $status->value]);
    }
}
