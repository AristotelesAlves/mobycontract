<?php

declare(strict_types=1);

namespace App\Modules\Usuarios\Services;

use Illuminate\Contracts\Redis\Factory as RedisFactory;
use Psr\Log\LoggerInterface;

final readonly class RedisRefreshTokenStore
{
    private const string PREFIX = 'refresh_token:';

    private const string BLACKLIST_PREFIX = 'blacklist:';

    private const string SESSION_PREFIX = 'session:ip:';

    private const TTL_SECONDS = 30 * 86400;

    public function __construct(
        private RedisFactory $redis,
        private LoggerInterface $logger,
    ) {}

    /** @param array<string, mixed> $metadata */
    public function store(
        string $jtiRefresh,
        int $userId,
        string $jtiAccess,
        string $ip,
        string $userAgent,
        array $metadata = []
    ): void {
        $bind = hash('sha256', $ip.'|'.$userAgent);
        $now = now()->toIso8601String();

        $data = [
            'user_id' => (string) $userId,
            'jti_access' => $jtiAccess,
            'ip' => $ip,
            'user_agent' => $userAgent,
            'bind' => $bind,
            'created_at' => $now,
            'last_used_at' => $now,
            'revoked_at' => null,
            'metadata' => json_encode($metadata, JSON_THROW_ON_ERROR),
        ];

        $this->redis->connection()->setex(
            self::PREFIX.$jtiRefresh,
            self::TTL_SECONDS,
            json_encode($data, JSON_THROW_ON_ERROR)
        );

        $this->redis->connection()->setex('access_refresh:'.$jtiAccess, self::TTL_SECONDS, $jtiRefresh);
        $this->redis->connection()->sadd('user_refresh:'.$userId, $jtiRefresh);
        $this->redis->connection()->expire('user_refresh:'.$userId, self::TTL_SECONDS);

        $this->logger->info('Refresh token stored', [
            'jti_refresh' => $jtiRefresh,
            'user_id' => $userId,
            'ip' => $ip,
        ]);
    }

    /** @return array{user_id: string, jti_access: string, ip: string, user_agent: string, bind: string, created_at: string, last_used_at: string, revoked_at: ?string, metadata: string, revoke_reason?: string}|null */
    public function get(string $jtiRefresh): ?array
    {
        $raw = $this->redis->connection()->get(self::PREFIX.$jtiRefresh);

        if (! is_string($raw) || $raw === '') {
            return null;
        }

        $data = json_decode($raw, true);

        if (! is_array($data)) {
            return null;
        }
        foreach (['user_id', 'jti_access', 'ip', 'user_agent', 'bind', 'created_at', 'last_used_at', 'metadata'] as $campo) {
            if (! isset($data[$campo]) || ! is_string($data[$campo])) {
                return null;
            }
        }
        if (! array_key_exists('revoked_at', $data) || ($data['revoked_at'] !== null && ! is_string($data['revoked_at']))) {
            return null;
        }

        /** @var array{user_id: string, jti_access: string, ip: string, user_agent: string, bind: string, created_at: string, last_used_at: string, revoked_at: ?string, metadata: string, revoke_reason?: string} $data */
        return $data;
    }

    public function refreshIdPorAccessToken(string $jtiAccess): ?string
    {
        $valor = $this->redis->connection()->get('access_refresh:'.$jtiAccess);

        return is_string($valor) && $valor !== '' ? $valor : null;
    }

    public function updateLastUsed(string $jtiRefresh): void
    {
        $data = $this->get($jtiRefresh);

        if ($data) {
            $data['last_used_at'] = now()->toIso8601String();
            $this->redis->connection()->setex(
                self::PREFIX.$jtiRefresh,
                self::TTL_SECONDS,
                json_encode($data, JSON_THROW_ON_ERROR)
            );
        }
    }

    public function revoke(string $jtiRefresh, string $reason = 'manual'): bool
    {
        $data = $this->get($jtiRefresh);

        if (! $data) {
            return false;
        }

        $data['revoked_at'] = now()->toIso8601String();
        $data['revoke_reason'] = $reason;
        $this->redis->connection()->setex(
            self::PREFIX.$jtiRefresh,
            self::TTL_SECONDS,
            json_encode($data, JSON_THROW_ON_ERROR)
        );

        $this->blacklistAccessToken($data['jti_access'], $reason);

        $this->logger->warning('Refresh token revoked', [
            'jti_refresh' => $jtiRefresh,
            'reason' => $reason,
            'user_id' => $data['user_id'],
        ]);

        return true;
    }

    public function revokeAllForUser(int $userId, string $reason = 'cascade'): int
    {
        $tokens = $this->redis->connection()->smembers('user_refresh:'.$userId);
        $count = 0;
        foreach (is_array($tokens) ? $tokens : [] as $jtiRefresh) {
            if (! is_string($jtiRefresh)) {
                continue;
            }
            $data = $this->get($jtiRefresh);
            if ($data && (int) $data['user_id'] === $userId && ! $data['revoked_at']) {
                $this->revoke($jtiRefresh, $reason);
                $count++;
            }
        }

        return $count;
    }

    public function blacklistAccessToken(string $jtiAccess, string $reason = 'revoked'): void
    {
        $ttl = 15 * 60;
        $this->redis->connection()->setex(self::BLACKLIST_PREFIX.$jtiAccess, $ttl, json_encode([
            'reason' => $reason,
            'revoked_at' => now()->toIso8601String(),
        ]));

        $this->logger->warning('Access token blacklisted', [
            'jti_access' => $jtiAccess,
            'reason' => $reason,
        ]);
    }

    public function isAccessTokenBlacklisted(string $jtiAccess): bool
    {
        return (bool) $this->redis->connection()->exists(self::BLACKLIST_PREFIX.$jtiAccess);
    }

    public function claimSession(string $ip, int $userId): bool
    {
        $key = self::SESSION_PREFIX.$ip;
        $redis = $this->redis->connection();

        $claimed = $redis->command('set', [$key, (string) $userId, ['EX' => 86400, 'NX']]);

        if ($claimed) {
            $this->logger->info('Session claimed', ['ip' => $ip, 'user_id' => $userId]);
        }

        return (bool) $claimed;
    }

    public function releaseSession(string $ip, int $userId): void
    {
        $key = self::SESSION_PREFIX.$ip;
        $redis = $this->redis->connection();

        $script = <<<'LUA'
            if redis.call("GET", KEYS[1]) == ARGV[1] then
                return redis.call("DEL", KEYS[1])
            end
            return 0
        LUA;

        $redis->command('eval', [$script, [$key, (string) $userId], 1]);

        $this->logger->info('Session released', ['ip' => $ip, 'user_id' => $userId]);
    }

    public function getSessionOwner(string $ip): ?int
    {
        $key = self::SESSION_PREFIX.$ip;
        $owner = $this->redis->connection()->get($key);

        return is_numeric($owner) ? (int) $owner : null;
    }

    public function validateBinding(string $jtiRefresh, string $ip, string $userAgent): bool
    {
        $data = $this->get($jtiRefresh);

        if (! $data) {
            return false;
        }

        $currentBind = hash('sha256', $ip.'|'.$userAgent);

        return hash_equals($data['bind'], $currentBind);
    }
}
