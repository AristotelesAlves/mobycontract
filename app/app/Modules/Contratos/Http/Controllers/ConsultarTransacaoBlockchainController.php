<?php

declare(strict_types=1);

namespace App\Modules\Contratos\Http\Controllers;

use App\Modules\Contratos\Http\Requests\ConsultarTransacaoBlockchainRequest;
use App\Modules\Contratos\Services\VerificadorDocumentoBlockchain;
use App\Shared\Application\Controllers\ApiController;
use Illuminate\Http\JsonResponse;
use Throwable;

final class ConsultarTransacaoBlockchainController extends ApiController
{
    public function __construct(private readonly VerificadorDocumentoBlockchain $verificador) {}

    public function __invoke(ConsultarTransacaoBlockchainRequest $request): JsonResponse
    {
        try {
            return response()->json([
                'dados' => $this->verificador->consultarTransacao($request->string('hashTransacao')->toString()),
            ])->header('Cache-Control', 'private, no-store');
        } catch (Throwable $erro) {
            return $this->falha($erro, 'contratos.consultar_transacao_blockchain');
        }
    }
}
