<?php

declare(strict_types=1);

namespace App\Modules\Modelos\Http\Controllers;

use App\Modules\Modelos\Contracts\ModeloServiceInterface;
use App\Modules\Modelos\DTOs\Input\AlterarModeloData;
use App\Modules\Modelos\DTOs\Input\CriarModeloData;
use App\Modules\Modelos\DTOs\Input\ListarModelosFiltros;
use App\Modules\Modelos\DTOs\Input\ValidarConteudoData;
use App\Modules\Modelos\Http\Requests\AlterarModeloRequest;
use App\Modules\Modelos\Http\Requests\AtivarModeloRequest;
use App\Modules\Modelos\Http\Requests\ClonarModeloRequest;
use App\Modules\Modelos\Http\Requests\CriarModeloRequest;
use App\Modules\Modelos\Http\Requests\InativarModeloRequest;
use App\Modules\Modelos\Http\Requests\ListarModelosRequest;
use App\Modules\Modelos\Http\Requests\MostrarModeloRequest;
use App\Modules\Modelos\Http\Requests\ValidarConteudoRequest;
use App\Modules\Modelos\Http\Resources\AnaliseDeConteudoResource;
use App\Modules\Modelos\Http\Resources\ModeloResource;
use App\Shared\Application\Controllers\ApiController;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response as HttpResponse;
use Throwable;

final class ModeloController extends ApiController
{
    public function __construct(
        private readonly ModeloServiceInterface $modelos,
    ) {}

    public function index(ListarModelosRequest $request): JsonResponse
    {
        try {
            $filtros = ListarModelosFiltros::fromValidated($request->validated());

            return $this->respostaLista($request, $this->modelos->listar($filtros), ModeloResource::class);
        } catch (Throwable $erro) {
            return $this->falha($erro, 'modelos.listar');
        }
    }

    public function show(MostrarModeloRequest $request): JsonResponse
    {
        try {
            $modelo = $this->modelos->obter((int) $request->route('id'));

            return $this->resposta($request, new ModeloResource($modelo));
        } catch (Throwable $erro) {
            return $this->falha($erro, 'modelos.obter');
        }
    }

    public function store(CriarModeloRequest $request): JsonResponse
    {
        try {
            $dados = CriarModeloData::fromValidated($request->validated());
            $modelo = $this->modelos->armazenar($dados, $this->identificadorDoAutor());

            return $this->resposta($request, new ModeloResource($modelo), HttpResponse::HTTP_CREATED);
        } catch (Throwable $erro) {
            return $this->falha($erro, 'modelos.armazenar');
        }
    }

    public function update(AlterarModeloRequest $request): JsonResponse
    {
        try {
            $dados = AlterarModeloData::fromValidated([
                ...$request->validated(),
                'id' => (int) $request->route('id'),
            ]);
            $modelo = $this->modelos->alterar($dados, $this->identificadorDoAutor());

            return $this->resposta($request, new ModeloResource($modelo));
        } catch (Throwable $erro) {
            return $this->falha($erro, 'modelos.alterar');
        }
    }

    public function clonar(ClonarModeloRequest $request): JsonResponse
    {
        try {
            $modelo = $this->modelos->clonar((int) $request->route('id'), $this->identificadorDoAutor());

            return $this->resposta($request, new ModeloResource($modelo), HttpResponse::HTTP_CREATED);
        } catch (Throwable $erro) {
            return $this->falha($erro, 'modelos.clonar');
        }
    }

    public function ativar(AtivarModeloRequest $request): JsonResponse
    {
        try {
            $modelo = $this->modelos->ativar((int) $request->route('id'), $this->identificadorDoAutor());

            return $this->resposta($request, new ModeloResource($modelo));
        } catch (Throwable $erro) {
            return $this->falha($erro, 'modelos.ativar');
        }
    }

    public function inativar(InativarModeloRequest $request): JsonResponse
    {
        try {
            $modelo = $this->modelos->inativar((int) $request->route('id'), $this->identificadorDoAutor());

            return $this->resposta($request, new ModeloResource($modelo));
        } catch (Throwable $erro) {
            return $this->falha($erro, 'modelos.inativar');
        }
    }

    public function validarConteudo(ValidarConteudoRequest $request): JsonResponse
    {
        try {
            $dados = ValidarConteudoData::fromValidated($request->validated());
            $analise = $this->modelos->validarConteudo($dados);

            return $this->resposta($request, new AnaliseDeConteudoResource($analise));
        } catch (Throwable $erro) {
            return $this->falha($erro, 'modelos.validar_conteudo');
        }
    }

    private function identificadorDoAutor(): int
    {
        return (int) auth()->id();
    }
}
