<?php

declare(strict_types=1);

namespace App\Modules\Inquilinos\Http\Controllers;

use App\Modules\Inquilinos\Contracts\InquilinoServiceInterface;
use App\Modules\Inquilinos\DTOs\Input\AlterarInquilinoData;
use App\Modules\Inquilinos\DTOs\Input\CriarInquilinoData;
use App\Modules\Inquilinos\DTOs\Input\ListarInquilinosFiltros;
use App\Modules\Inquilinos\Http\Requests\AlterarInquilinoRequest;
use App\Modules\Inquilinos\Http\Requests\ConsultarIndicadoresRequest;
use App\Modules\Inquilinos\Http\Requests\CriarInquilinoRequest;
use App\Modules\Inquilinos\Http\Requests\ExcluirInquilinoRequest;
use App\Modules\Inquilinos\Http\Requests\ListarInquilinosRequest;
use App\Modules\Inquilinos\Http\Requests\MostrarInquilinoRequest;
use App\Modules\Inquilinos\Http\Resources\IndicadoresInquilinoResource;
use App\Modules\Inquilinos\Http\Resources\InquilinoResource;
use App\Shared\Application\Controllers\ApiController;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response as HttpResponse;
use Throwable;

final class InquilinoController extends ApiController
{
    public function __construct(
        private readonly InquilinoServiceInterface $inquilinos,
    ) {}

    public function index(ListarInquilinosRequest $request): JsonResponse
    {
        try {
            $filtros = ListarInquilinosFiltros::fromValidated($request->validated());
            $resultado = $this->inquilinos->listar($filtros);

            return $this->respostaLista($request, $resultado, InquilinoResource::class);
        } catch (Throwable $erro) {
            return $this->falha($erro, 'inquilinos.listar');
        }
    }

    public function show(MostrarInquilinoRequest $request): JsonResponse
    {
        try {
            $inquilino = $this->inquilinos->obter((int) $request->route('id'));

            return $this->resposta($request, new InquilinoResource($inquilino));
        } catch (Throwable $erro) {
            return $this->falha($erro, 'inquilinos.obter');
        }
    }

    public function store(CriarInquilinoRequest $request): JsonResponse
    {
        try {
            $dados = CriarInquilinoData::fromValidated($request->validated());
            $inquilino = $this->inquilinos->armazenar($dados, $this->identificadorDoAutor());

            return $this->resposta($request, new InquilinoResource($inquilino), HttpResponse::HTTP_CREATED);
        } catch (Throwable $erro) {
            return $this->falha($erro, 'inquilinos.armazenar');
        }
    }

    public function update(AlterarInquilinoRequest $request): JsonResponse
    {
        try {
            $dados = AlterarInquilinoData::fromValidated([
                ...$request->validated(),
                'id' => (int) $request->route('id'),
            ]);
            $inquilino = $this->inquilinos->alterar($dados, $this->identificadorDoAutor());

            return $this->resposta($request, new InquilinoResource($inquilino));
        } catch (Throwable $erro) {
            return $this->falha($erro, 'inquilinos.alterar');
        }
    }

    public function destroy(ExcluirInquilinoRequest $request): JsonResponse
    {
        try {
            $this->inquilinos->excluir((int) $request->route('id'), $this->identificadorDoAutor());

            return new JsonResponse(null, HttpResponse::HTTP_NO_CONTENT);
        } catch (Throwable $erro) {
            return $this->falha($erro, 'inquilinos.excluir');
        }
    }

    public function indicadores(ConsultarIndicadoresRequest $request): JsonResponse
    {
        try {
            $indicadores = $this->inquilinos->indicadores((int) $request->route('id'));

            return $this->resposta($request, new IndicadoresInquilinoResource($indicadores));
        } catch (Throwable $erro) {
            return $this->falha($erro, 'inquilinos.indicadores');
        }
    }

    private function identificadorDoAutor(): int
    {
        return (int) auth()->id();
    }
}
