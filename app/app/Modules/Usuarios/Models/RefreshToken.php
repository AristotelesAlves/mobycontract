<?php

declare(strict_types=1);

namespace App\Modules\Usuarios\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;

final class RefreshToken extends Model
{
    protected $table = 'oauth_refresh_tokens';

    public $timestamps = false;

    protected $fillable = [
        'id',
        'access_token_id',
        'revoked',
        'expires_at',
    ];

    protected $casts = [
        'revoked' => 'boolean',
        'expires_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public $incrementing = false;

    protected $keyType = 'string';

    /** @return BelongsTo<AccessToken, $this> */
    public function accessToken(): BelongsTo
    {
        return $this->belongsTo(AccessToken::class, 'access_token_id');
    }

    /** @return HasOneThrough<User, AccessToken, $this> */
    public function user(): HasOneThrough
    {
        return $this->hasOneThrough(User::class, AccessToken::class, 'id', 'id', 'access_token_id', 'user_id');
    }

    public function isRevoked(): bool
    {
        return $this->revoked || ($this->expires_at?->isPast() ?? true);
    }
}
