<?php

declare(strict_types=1);

namespace App\Modules\Imoveis\Http\Controllers;

use App\Modules\Imoveis\Contracts\ConsultaEnderecoServiceInterface;
use App\Modules\Imoveis\Http\Requests\ConsultarCepRequest;
use App\Modules\Imoveis\Http\Resources\EnderecoCepResource;
use App\Shared\Application\Controllers\ApiController;
use Illuminate\Http\JsonResponse;
use Throwable;

final class EnderecoController extends ApiController
{
    public function __construct(
        private readonly ConsultaEnderecoServiceInterface $servico,
    ) {}

    public function consultarPorCep(ConsultarCepRequest $request): JsonResponse
    {
        try {
            $cep = $request->string('cep')->toString();
            $resultado = $this->servico->consultarPorCep($cep);

            return $this->resposta($request, new EnderecoCepResource($resultado));
        } catch (Throwable $erro) {
            return $this->falha($erro, 'enderecos.consultar_cep');
        }
    }
}
