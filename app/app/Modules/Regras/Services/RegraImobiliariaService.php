<?php

declare(strict_types=1);

namespace App\Modules\Regras\Services;

use App\Modules\Regras\Contracts\RegraImobiliariaRepositoryInterface;
use App\Modules\Regras\Contracts\RegraImobiliariaServiceInterface;
use App\Modules\Regras\DTOs\Input\AtualizarRegrasData;
use App\Modules\Regras\DTOs\Output\RegraImobiliariaResult;

final readonly class RegraImobiliariaService implements RegraImobiliariaServiceInterface
{
    public function __construct(
        private RegraImobiliariaRepositoryInterface $regras,
    ) {}

    public function obter(): RegraImobiliariaResult
    {
        return $this->regras->obter();
    }

    public function atualizar(AtualizarRegrasData $dados): RegraImobiliariaResult
    {
        $atual = $this->regras->obter();

        return $this->regras->atualizar($atual->id, [
            'contrato_unico_por_imovel' => $dados->contratoUnicoPorImovel,
            'contrato_unico_por_inquilino' => $dados->contratoUnicoPorInquilino,
        ]);
    }
}
