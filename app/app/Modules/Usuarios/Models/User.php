<?php

declare(strict_types=1);

namespace App\Modules\Usuarios\Models;

use App\Shared\Domain\Enums\Papel;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;

/**
 * @use HasFactory<UserFactory>
 */
final class User extends Authenticatable
{
    use HasApiTokens;

    /** @use HasFactory<UserFactory> */
    use HasFactory;

    use Notifiable;

    protected $table = 'users';

    protected $fillable = [
        'nome',
        'email',
        'password',
        'papel',
        'ativo',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'ativo' => 'boolean',
            'email_verificado_em' => 'datetime',
        ];
    }

    protected static function newFactory(): UserFactory
    {
        return UserFactory::new();
    }

    public function enumPapel(): Papel
    {
        return Papel::from((string) $this->papel);
    }

    /**
     * @return list<string>
     */
    public function abilities(): array
    {
        return $this->enumPapel()->abilities();
    }

    /** @return HasManyThrough<RefreshToken, AccessToken, $this> */
    public function refreshTokens(): HasManyThrough
    {
        return $this->hasManyThrough(RefreshToken::class, AccessToken::class, 'user_id', 'access_token_id', 'id', 'id');
    }
}
