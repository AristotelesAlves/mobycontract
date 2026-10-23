<?php

declare(strict_types=1);

namespace App\Modules\Contratos\Repositories\Eloquent;

use App\Modules\Contratos\Contracts\DividaRepositoryInterface;
use App\Modules\Contratos\DTOs\Output\DividaResult;
use App\Modules\Contratos\Models\Divida;

final readonly class DividaRepository implements DividaRepositoryInterface
{
    public function listarPorContrato(int $contratoId): array
    {
        $dividas = Divida::query()
            ->where('contrato_id', $contratoId)
            ->orderBy('id')
            ->get();

        return array_values($dividas
            ->map(static fn (Divida $divida): DividaResult => DividaResult::fromModel($divida))
            ->all());
    }

    public function listarEmAbertoPorContrato(int $contratoId): array
    {
        return array_values(array_filter(
            $this->listarPorContrato($contratoId),
            static fn (DividaResult $divida): bool => $divida->status === 'Em aberto',
        ));
    }

    public function buscarPorId(int $contratoId, int $dividaId): ?DividaResult
    {
        $divida = Divida::query()
            ->where('contrato_id', $contratoId)
            ->find($dividaId);

        return $divida === null ? null : DividaResult::fromModel($divida);
    }

    /**
     * @param  array<string, mixed>  $atributos
     */
    public function criar(int $contratoId, array $atributos): DividaResult
    {
        $divida = Divida::query()->create([...$atributos, 'contrato_id' => $contratoId]);

        return DividaResult::fromModel($divida->refresh());
    }

    public function quitar(int $contratoId, int $dividaId, string $dataQuitacao): ?DividaResult
    {
        $divida = Divida::query()
            ->where('contrato_id', $contratoId)
            ->find($dividaId);

        if ($divida === null) {
            return null;
        }

        $divida->fill([
            'status' => 'Quitada',
            'data_quitacao' => $dataQuitacao,
        ])->save();

        return DividaResult::fromModel($divida->refresh());
    }
}
