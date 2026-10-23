<?php

declare(strict_types=1);

namespace App\Modules\Usuarios\Services;

use App\Modules\Usuarios\Models\User;
use App\Shared\Contracts\TokenIssuer;
use Psr\Log\LoggerInterface;

final readonly class RevocationService
{
    public function __construct(
        private RedisRefreshTokenStore $refreshTokenStore,
        private TokenIssuer $tokenIssuer,
        private LoggerInterface $logger,
    ) {}

    public function revokeAllUserTokens(User $user, string $reason = 'manual'): void
    {
        $revokedCount = $this->refreshTokenStore->revokeAllForUser($user->id, $reason);

        $this->tokenIssuer->revogar($user);

        $this->logger->warning('All user tokens revoked', [
            'user_id' => $user->id,
            'refresh_tokens_revoked' => $revokedCount,
            'reason' => $reason,
        ]);
    }

    public function revokeRefreshToken(string $jtiRefresh, string $reason = 'manual'): bool
    {
        return $this->refreshTokenStore->revoke($jtiRefresh, $reason);
    }

    public function revokeOnBindingViolation(User $user, string $ip, string $userAgent): void
    {
        $this->revokeAllUserTokens($user, 'binding_violation');

        $this->logger->critical('Token seizure detected - all tokens revoked', [
            'user_id' => $user->id,
            'ip' => $ip,
            'user_agent' => $userAgent,
            'action' => 'full_revocation',
        ]);
    }

    public function revokeOnConcurrentUse(string $jtiRefresh, int $userId): void
    {
        $this->refreshTokenStore->revoke($jtiRefresh, 'concurrent_use');

        $this->logger->critical('Concurrent refresh token use detected', [
            'jti_refresh' => $jtiRefresh,
            'user_id' => $userId,
            'action' => 'token_revoked',
        ]);
    }
}
