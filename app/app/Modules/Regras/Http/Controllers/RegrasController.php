<?php

declare(strict_types=1);

namespace App\Modules\Regras\Http\Controllers;

use App\Modules\Regras\Contracts\RegraImobiliariaServiceInterface;
use App\Modules\Regras\DTOs\Input\AtualizarRegrasData;
use App\Modules\Regras\Http\Requests\AtualizarRegrasRequest;
use App\Modules\Regras\Http\Requests\MostrarRegrasRequest;
use App\Modules\Regras\Http\Resources\RegraResource;
use App\Shared\Application\Controllers\ApiController;
use Illuminate\Http\JsonResponse;
use Throwable;

final class RegrasController extends ApiController
{
    public function __construct(
        private readonly RegraImobiliariaServiceInterface $regras,
    ) {}

    public function show(MostrarRegrasRequest $request): JsonResponse
    {
        try {
            return $this->resposta($request, new RegraResource($this->regras->obter()));
        } catch (Throwable $erro) {
            return $this->falha($erro, 'regras.obter');
        }
    }

    public function update(AtualizarRegrasRequest $request): JsonResponse
    {
        try {
            $dados = AtualizarRegrasData::fromValidated($request->validated());

            return $this->resposta($request, new RegraResource($this->regras->atualizar($dados)));
        } catch (Throwable $erro) {
            return $this->falha($erro, 'regras.alterar');
        }
    }
}
