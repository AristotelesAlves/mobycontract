<?php

declare(strict_types=1);

namespace App\Modules\Vistorias\Providers;

use App\Modules\Vistorias\Contracts\VistoriaRepositoryInterface;
use App\Modules\Vistorias\Contracts\VistoriaServiceInterface;
use App\Modules\Vistorias\Repositories\Eloquent\VistoriaRepository;
use App\Modules\Vistorias\Services\VistoriaService;
use App\Shared\Providers\ModuloServiceProvider;

final class VistoriasServiceProvider extends ModuloServiceProvider
{
    /**
     * @return array<class-string, class-string>
     */
    public function bindings(): array
    {
        return [
            VistoriaRepositoryInterface::class => VistoriaRepository::class,
            VistoriaServiceInterface::class => VistoriaService::class,
        ];
    }

    public function prefixoDoModulo(): string
    {
        return 'api/v1';
    }

    protected function rotaDoModulo(): string
    {
        return __DIR__.'/../Routes/api.php';
    }
}
