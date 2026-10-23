<?php

declare(strict_types=1);

namespace App\Modules\Usuarios\Http\Middleware;

use App\Modules\Usuarios\Models\AuditLog;
use App\Modules\Usuarios\Services\RedisRefreshTokenStore;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final readonly class SingleSessionPerIpMiddleware
{
    public function __construct(
        private RedisRefreshTokenStore $refreshTokenStore,
    ) {}

    /** @param Closure(Request): Response $next */
    public function handle(Request $request, Closure $next): Response
    {
        $ip = $request->ip() ?? '';
        $user = $request->user();

        if (! $user) {
            return $next($request);
        }

        $sessionOwner = $this->refreshTokenStore->getSessionOwner($ip);

        if ($sessionOwner !== null && $sessionOwner !== $user->id) {
            AuditLog::log('session_blocked_ip_conflict', $user->id, null, null, $ip, $request->userAgent(), [
                'reason' => 'ip_already_in_use',
                'current_user_id' => $user->id,
                'session_owner_id' => $sessionOwner,
            ]);

            return response()->json([
                'message' => 'Este IP já possui uma sessão ativa. Encerre a outra sessão antes de continuar.',
                'error' => 'ip_session_conflict',
            ], Response::HTTP_CONFLICT);
        }

        return $next($request);
    }
}
