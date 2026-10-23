<?php

declare(strict_types=1);

namespace App\Modules\Usuarios\Http\Controllers;

use App\Modules\Usuarios\Contracts\UsuarioServiceInterface;
use App\Modules\Usuarios\DTOs\Input\AlterarUsuarioData;
use App\Modules\Usuarios\DTOs\Input\CriarUsuarioData;
use App\Modules\Usuarios\DTOs\Input\ListarUsuariosFiltros;
use App\Modules\Usuarios\Http\Requests\AlterarSenhaDeUsuarioRequest;
use App\Modules\Usuarios\Http\Requests\AlterarUsuarioRequest;
use App\Modules\Usuarios\Http\Requests\CriarUsuarioRequest;
use App\Modules\Usuarios\Http\Requests\ListarOperadoresRequest;
use App\Modules\Usuarios\Http\Requests\ListarUsuariosRequest;
use App\Modules\Usuarios\Http\Requests\MostrarUsuarioRequest;
use App\Modules\Usuarios\Http\Resources\UsuarioResource;
use App\Shared\Application\Controllers\ApiController;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response as HttpResponse;
use Throwable;

final class UsuarioController extends ApiController
{
    public function __construct(
        private readonly UsuarioServiceInterface $usuarios,
    ) {}

    public function index(ListarUsuariosRequest $request): JsonResponse
    {
        try {
            $filtros = ListarUsuariosFiltros::fromValidated($request->validated());
            $resultado = $this->usuarios->listar($filtros);

            return $this->respostaLista($request, $resultado, UsuarioResource::class);
        } catch (Throwable $erro) {
            return $this->falha($erro, 'usuarios.listar');
        }
    }

    public function show(MostrarUsuarioRequest $request): JsonResponse
    {
        try {
            $usuario = $this->usuarios->obter((int) $request->route('id'));

            return $this->resposta($request, new UsuarioResource($usuario));
        } catch (Throwable $erro) {
            return $this->falha($erro, 'usuarios.obter');
        }
    }

    public function store(CriarUsuarioRequest $request): JsonResponse
    {
        try {
            $dados = CriarUsuarioData::fromValidated($request->validated());
            $usuario = $this->usuarios->armazenar($dados, $this->identificadorDoAutor());

            return $this->resposta($request, new UsuarioResource($usuario), HttpResponse::HTTP_CREATED);
        } catch (Throwable $erro) {
            return $this->falha($erro, 'usuarios.armazenar');
        }
    }

    public function update(AlterarUsuarioRequest $request): JsonResponse
    {
        try {
            $dados = AlterarUsuarioData::fromValidated([
                ...$request->validated(),
                'id' => (int) $request->route('id'),
            ]);
            $usuario = $this->usuarios->alterar($dados, $this->identificadorDoAutor());

            return $this->resposta($request, new UsuarioResource($usuario));
        } catch (Throwable $erro) {
            return $this->falha($erro, 'usuarios.alterar');
        }
    }

    public function alterarSenha(AlterarSenhaDeUsuarioRequest $request): JsonResponse
    {
        try {
            $this->usuarios->alterarSenha(
                (int) $request->route('id'),
                $request->string('novaSenha')->toString(),
                $this->identificadorDoAutor(),
            );

            return new JsonResponse(['dados' => ['mensagem' => 'Senha alterada.']], HttpResponse::HTTP_OK);
        } catch (Throwable $erro) {
            return $this->falha($erro, 'usuarios.alterar_senha');
        }
    }

    public function operadores(ListarOperadoresRequest $request): JsonResponse
    {
        try {
            $operadores = $this->usuarios->listarOperadores();

            return new JsonResponse([
                'dados' => UsuarioResource::collection($operadores)->resolve($request),
            ], HttpResponse::HTTP_OK);
        } catch (Throwable $erro) {
            return $this->falha($erro, 'usuarios.operadores');
        }
    }

    private function identificadorDoAutor(): int
    {
        return (int) auth()->id();
    }
}
