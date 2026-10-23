<?php

declare(strict_types=1);

namespace App\Modules\Regras\Repositories\Eloquent;

use App\Modules\Regras\Contracts\RegraImobiliariaRepositoryInterface;
use App\Modules\Regras\DTOs\Output\RegraImobiliariaResult;
use App\Modules\Regras\Models\RegraImobiliaria;
use App\Shared\Application\Exceptions\RegraNegocioException;

final readonly class RegraImobiliariaRepository implements RegraImobiliariaRepositoryInterface
{
    public function obter(): RegraImobiliariaResult
    {
        $regra = RegraImobiliaria::query()->orderBy('id')->first();

        if ($regra instanceof RegraImobiliaria) {
            return RegraImobiliariaResult::fromModel($regra);
        }

        /**
         * Guarda de consistência: a migração semeia a linha única.
         */
        $criada = RegraImobiliaria::query()->create([
            'contrato_unico_por_imovel' => true,
            'contrato_unico_por_inquilino' => true,
        ]);

        return RegraImobiliariaResult::fromModel($criada);
    }

    /**
     * @param  array<string, mixed>  $atributos
     */
    public function atualizar(int $id, array $atributos): RegraImobiliariaResult
    {
        $regra = RegraImobiliaria::query()->find($id);

        if (! $regra instanceof RegraImobiliaria) {
            throw new RegraNegocioException('Regras imobiliárias não encontradas.', []);
        }

        $regra->fill($atributos)->save();

        return RegraImobiliariaResult::fromModel($regra->refresh());
    }
}
