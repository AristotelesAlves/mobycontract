<?php

declare(strict_types=1);

namespace App\Modules\Usuarios\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class AuditLog extends Model
{
    protected $table = 'audit_logs';

    protected $fillable = [
        'user_id',
        'evento',
        'jti_access',
        'jti_refresh',
        'ip',
        'user_agent',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
        'created_at' => 'datetime',
    ];

    public const UPDATED_AT = null;

    public $incrementing = true;

    protected $keyType = 'int';

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** @param array<string, mixed> $metadata */
    public static function log(
        string $evento,
        ?int $userId = null,
        ?string $jtiAccess = null,
        ?string $jtiRefresh = null,
        ?string $ip = null,
        ?string $userAgent = null,
        array $metadata = []
    ): self {
        return self::create([
            'user_id' => $userId,
            'evento' => $evento,
            'jti_access' => $jtiAccess,
            'jti_refresh' => $jtiRefresh,
            'ip' => $ip,
            'user_agent' => $userAgent,
            'metadata' => $metadata,
        ]);
    }
}
