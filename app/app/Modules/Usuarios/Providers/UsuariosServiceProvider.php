<?php

declare(strict_types=1);

namespace App\Modules\Usuarios\Providers;

use App\Modules\Usuarios\Contracts\AuthRepositoryContract;
use App\Modules\Usuarios\Contracts\UsuarioRepositoryInterface;
use App\Modules\Usuarios\Contracts\UsuarioServiceInterface;
use App\Modules\Usuarios\Listeners\PassportTokenEventListener;
use App\Modules\Usuarios\Repositories\Eloquent\AuthRepository;
use App\Modules\Usuarios\Repositories\Eloquent\UsuarioRepository;
use App\Modules\Usuarios\Services\AuthService;
use App\Modules\Usuarios\Services\RedisRefreshTokenStore;
use App\Modules\Usuarios\Services\RevocationService;
use App\Modules\Usuarios\Services\UsuarioService;
use App\Shared\Providers\ModuloServiceProvider;
use Illuminate\Support\Facades\Event;
use Laravel\Passport\Events\AccessTokenCreated;
use Laravel\Passport\Events\RefreshTokenCreated;

final class UsuariosServiceProvider extends ModuloServiceProvider
{
    /**
     * @return array<class-string, class-string>
     */
    public function bindings(): array
    {
        return [
            AuthService::class => AuthService::class,
            AuthRepositoryContract::class => AuthRepository::class,
            UsuarioServiceInterface::class => UsuarioService::class,
            UsuarioRepositoryInterface::class => UsuarioRepository::class,
            RedisRefreshTokenStore::class => RedisRefreshTokenStore::class,
            RevocationService::class => RevocationService::class,
        ];
    }

    public function prefixoDoModulo(): string
    {
        return 'api/v1/usuarios';
    }

    protected function rotaDoModulo(): string
    {
        return __DIR__.'/../Routes/api.php';
    }

    public function boot(): void
    {
        parent::boot();

        Event::listen(AccessTokenCreated::class, [PassportTokenEventListener::class, 'handleAccessTokenCreated']);
        Event::listen(RefreshTokenCreated::class, [PassportTokenEventListener::class, 'handleRefreshTokenCreated']);
    }
}
