<?php

declare(strict_types=1);

namespace App\Modules\Usuarios\Http\Middleware;

use App\Modules\Usuarios\Models\AuditLog;
use App\Modules\Usuarios\Models\User;
use App\Modules\Usuarios\Services\RedisRefreshTokenStore;
use App\Modules\Usuarios\Services\RevocationService;
use Closure;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final readonly class TokenBindingMiddleware
{
    public function __construct(
        private RedisRefreshTokenStore $refreshTokenStore,
        private RevocationService $revocationService,
    ) {}

    /** @param Closure(Request): Response $next */
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->bearerToken();

        if (! $token) {
            return $next($request);
        }

        try {
            $publicKey = file_get_contents(storage_path('oauth-public.key'));
            if (! is_string($publicKey) || $publicKey === '') {
                throw new \RuntimeException('Chave pública de autenticação indisponível.');
            }
            $payload = JWT::decode($token, new Key($publicKey, 'RS256'));
            $payload = (array) $payload;

            $jtiAccess = is_string($payload['jti'] ?? null) ? $payload['jti'] : '';
            $jtiRefresh = $this->refreshTokenStore->refreshIdPorAccessToken($jtiAccess);
            $userId = is_numeric($payload['sub'] ?? null) ? (int) $payload['sub'] : null;
            $ip = $request->ip() ?? '';
            $userAgent = $request->userAgent() ?? '';

            if ($jtiAccess && $this->refreshTokenStore->isAccessTokenBlacklisted($jtiAccess)) {
                AuditLog::log('token_rejected_blacklist', $userId, $jtiAccess, $jtiRefresh, $ip, $userAgent, [
                    'reason' => 'access_token_blacklisted',
                ]);

                return response()->json([
                    'message' => 'Token revogado.',
                    'error' => 'token_revoked',
                ], Response::HTTP_UNAUTHORIZED);
            }

            $respostaRefresh = $this->validarVinculoDoRefresh($jtiAccess, $jtiRefresh, $userId, $ip, $userAgent);
            if ($respostaRefresh !== null) {
                return $respostaRefresh;
            }
        } catch (\Throwable) {
            return response()->json([
                'mensagem' => 'Não foi possível validar a sessão. Faça login novamente.',
                'codigo' => 'INVALID_SESSION',
            ], Response::HTTP_UNAUTHORIZED);
        }

        return $next($request);
    }

    private function validarVinculoDoRefresh(
        string $jtiAccess,
        ?string $jtiRefresh,
        ?int $userId,
        string $ip,
        string $userAgent,
    ): ?Response {
        if (! $jtiRefresh || ! $userId) {
            return null;
        }

        $data = $this->refreshTokenStore->get($jtiRefresh);
        if (! $data) {
            return null;
        }

        if ($data['revoked_at']) {
            AuditLog::log('token_rejected_revoked', $userId, $jtiAccess, $jtiRefresh, $ip, $userAgent, [
                'reason' => 'refresh_token_revoked',
                'revoked_at' => $data['revoked_at'],
                'revoke_reason' => $data['revoke_reason'] ?? 'unknown',
            ]);

            return response()->json([
                'message' => 'Sessão expirada. Faça login novamente.',
                'error' => 'session_expired',
            ], Response::HTTP_UNAUTHORIZED);
        }

        $currentBind = hash('sha256', $ip.'|'.$userAgent);
        if (! hash_equals($data['bind'], $currentBind)) {
            $this->revogarUsuarioSeExistir($userId, $ip, $userAgent);

            AuditLog::log('token_seized', $userId, $jtiAccess, $jtiRefresh, $ip, $userAgent, [
                'reason' => 'binding_violation',
                'expected_bind' => $data['bind'],
                'actual_bind' => $currentBind,
                'original_ip' => $data['ip'],
                'original_ua' => $data['user_agent'],
                'current_ip' => $ip,
                'current_ua' => $userAgent,
            ]);

            return response()->json([
                'message' => 'Atividade suspeita detectada. Sessão encerrada por segurança.',
                'error' => 'token_seized',
            ], Response::HTTP_UNAUTHORIZED);
        }

        $this->refreshTokenStore->updateLastUsed($jtiRefresh);

        return null;
    }

    private function revogarUsuarioSeExistir(int $userId, string $ip, string $userAgent): void
    {
        $user = User::find($userId);
        if (! $user) {
            return;
        }

        $this->revocationService->revokeOnBindingViolation($user, $ip, $userAgent);
    }
}
