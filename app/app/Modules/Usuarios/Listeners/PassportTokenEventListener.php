<?php

declare(strict_types=1);

namespace App\Modules\Usuarios\Listeners;

use App\Modules\Usuarios\Models\AuditLog;
use App\Modules\Usuarios\Models\User;
use App\Modules\Usuarios\Services\RedisRefreshTokenStore;
use Laravel\Passport\Events\AccessTokenCreated;
use Laravel\Passport\Events\RefreshTokenCreated;
use Laravel\Passport\Token;
use Psr\Log\LoggerInterface;
use Ramsey\Uuid\Uuid;

final readonly class PassportTokenEventListener
{
    public function __construct(
        private RedisRefreshTokenStore $refreshTokenStore,
        private LoggerInterface $logger,
    ) {}

    public function handleAccessTokenCreated(AccessTokenCreated $event): void
    {
        $token = Token::find($event->tokenId);

        if (! $token instanceof Token) {
            return;
        }

        $user = $token->user;

        if (! $user instanceof User) {
            return;
        }

        $request = request();
        $ip = (string) $request->ip();
        $userAgent = (string) $request->userAgent();

        $jtiAccess = (string) $event->tokenId;
        $jtiRefresh = (string) Uuid::uuid4();

        $this->refreshTokenStore->store(
            $jtiRefresh,
            (int) $user->id,
            $jtiAccess,
            $ip,
            $userAgent,
            ['client_id' => $token->client_id]
        );

        AuditLog::log('token_issued', $user->id, $jtiAccess, $jtiRefresh, $ip, $userAgent, [
            'client_id' => $token->client_id,
            'grant_type' => $request->input('grant_type', 'personal_access'),
        ]);

        $this->logger->info('Access token created', [
            'jti_access' => $jtiAccess,
            'jti_refresh' => $jtiRefresh,
            'user_id' => $user->id,
            'ip' => $ip,
        ]);
    }

    public function handleRefreshTokenCreated(RefreshTokenCreated $event): void
    {
        $this->logger->info('Refresh token created', [
            'jti_refresh' => $event->refreshTokenId,
            'access_token_id' => $event->accessTokenId,
        ]);
    }
}
