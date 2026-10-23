<?php

declare(strict_types=1);

namespace App\Modules\Usuarios\Http\Controllers;

use App\Modules\Usuarios\Http\Middleware\AutenticacaoPorCookieMiddleware;
use App\Modules\Usuarios\Http\Requests\LoginRequest;
use App\Modules\Usuarios\Http\Requests\RefreshTokenRequest;
use App\Modules\Usuarios\Http\Resources\UsuarioResource;
use App\Modules\Usuarios\Models\User;
use App\Modules\Usuarios\Services\AuthService;
use App\Modules\Usuarios\Services\RedisRefreshTokenStore;
use App\Modules\Usuarios\Services\RevocationService;
use App\Shared\Application\Controllers\ApiController;
use App\Shared\Contracts\TokenIssuer;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Symfony\Component\HttpFoundation\Response as HttpResponse;
use Throwable;

final class SessaoController extends ApiController
{
    public function __construct(
        private readonly AuthService $servicoDeAutenticacao,
        private readonly TokenIssuer $emissorDeToken,
        private readonly RevocationService $revocationService,
        private readonly RedisRefreshTokenStore $refreshTokens,
    ) {}

    public function entrar(LoginRequest $request): JsonResponse
    {
        try {
            $autenticacao = $this->servicoDeAutenticacao->autenticar(
                $request->string('email')->toString(),
                $request->string('senha')->toString(),
            );

            $resposta = new JsonResponse([
                'dados' => [
                    'expira_em' => $autenticacao['expiraEm'],
                    'usuario' => UsuarioResource::make($autenticacao['usuario'])->resolve($request),
                ],
            ], HttpResponse::HTTP_OK);

            return $this->comCookieDeAcesso($resposta, $autenticacao['token']);
        } catch (Throwable $falha) {
            return $this->falha($falha, 'usuarios.sessao.entrar');
        }
    }

    public function renovar(RefreshTokenRequest $request): JsonResponse
    {
        try {
            $refreshToken = $request->string('refresh_token')->toString();
            $user = $request->user();

            if (! $user instanceof User) {
                throw new AuthenticationException;
            }

            $sessao = $this->refreshTokens->get($refreshToken);
            if ($sessao === null || (string) ($sessao['user_id'] ?? '') !== (string) $user->id
                || ! empty($sessao['revoked_at'])) {
                throw new AuthenticationException;
            }
            if (! $this->revocationService->revokeRefreshToken($refreshToken, 'refresh_rotation')) {
                throw new AuthenticationException;
            }

            $autenticacao = $this->emissorDeToken->emitir($user);

            $resposta = new JsonResponse([
                'dados' => [
                    'expira_em' => $autenticacao['expiraEm'],
                    'usuario' => UsuarioResource::make($user)->resolve($request),
                ],
            ], HttpResponse::HTTP_OK);

            return $this->comCookieDeAcesso($resposta, $autenticacao['token']);
        } catch (Throwable $falha) {
            return $this->falha($falha, 'usuarios.sessao.renovar');
        }
    }

    public function consultar(Request $request): JsonResponse
    {
        return new JsonResponse([
            'dados' => UsuarioResource::make($request->user())->resolve($request),
        ], HttpResponse::HTTP_OK);
    }

    public function sair(Request $request): JsonResponse
    {
        $usuario = $request->user();

        if (! $usuario instanceof User) {
            throw new AuthenticationException;
        }

        try {
            $this->emissorDeToken->revogar($usuario);

            return $this->removerCookieDeAcesso(new JsonResponse([
                'dados' => ['mensagem' => 'Sessão encerrada.'],
            ], HttpResponse::HTTP_OK));
        } catch (Throwable $falha) {
            return $this->falha($falha, 'usuarios.sessao.sair');
        }
    }

    public function sairTodas(Request $request): JsonResponse
    {
        $usuario = $request->user();

        if (! $usuario instanceof User) {
            throw new AuthenticationException;
        }

        try {
            $this->revocationService->revokeAllUserTokens($usuario, 'logout_all_devices');

            return $this->removerCookieDeAcesso(new JsonResponse([
                'dados' => ['mensagem' => 'Todas as sessões foram encerradas.'],
            ], HttpResponse::HTTP_OK));
        } catch (Throwable $falha) {
            return $this->falha($falha, 'usuarios.sessao.sair_todas');
        }
    }

    private function comCookieDeAcesso(JsonResponse $resposta, string $token): JsonResponse
    {
        return $resposta->withCookie(cookie(
            AutenticacaoPorCookieMiddleware::NOME_COOKIE,
            $token,
            Config::integer('mobycontract.autenticacao.duracao_cookie_minutos', 60),
            '/',
            null,
            Config::boolean('mobycontract.autenticacao.cookie_secure', false),
            true,
            false,
            Config::string('mobycontract.autenticacao.cookie_same_site', 'lax'),
        ));
    }

    private function removerCookieDeAcesso(JsonResponse $resposta): JsonResponse
    {
        return $resposta->withoutCookie(AutenticacaoPorCookieMiddleware::NOME_COOKIE);
    }
}
