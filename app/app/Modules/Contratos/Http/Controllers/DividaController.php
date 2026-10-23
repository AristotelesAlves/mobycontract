<?php

declare(strict_types=1);

namespace App\Modules\Contratos\Http\Controllers;

use App\Modules\Contratos\Contracts\DividaServiceInterface;
use App\Modules\Contratos\Http\Requests\ListarDividasRequest;
use App\Modules\Contratos\Http\Requests\QuitarDividaRequest;
use App\Modules\Contratos\Http\Resources\DividaResource;
use App\Shared\Application\Controllers\ApiController;
use Illuminate\Http\JsonResponse;
use Throwable;

final class DividaController extends ApiController
{
    public function __construct(
        private readonly DividaServiceInterface $dividas,
    ) {}

    public function index(ListarDividasRequest $request): JsonResponse
    {
        try {
            $dividas = $this->dividas->listar((int) $request->route('id'));

            return new JsonResponse([
                'dados' => DividaResource::collection($dividas)->resolve($request),
            ]);
        } catch (Throwable $erro) {
            return $this->falha($erro, 'dividas.listar');
        }
    }

    public function quitar(QuitarDividaRequest $request): JsonResponse
    {
        try {
            $divida = $this->dividas->quitar(
                (int) $request->route('id'),
                (int) $request->route('dividaId'),
                (int) auth()->id(),
            );

            return $this->resposta($request, new DividaResource($divida));
        } catch (Throwable $erro) {
            return $this->falha($erro, 'dividas.quitar');
        }
    }
}
