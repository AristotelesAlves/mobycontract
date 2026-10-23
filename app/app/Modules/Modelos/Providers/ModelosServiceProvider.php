<?php

declare(strict_types=1);

namespace App\Modules\Modelos\Providers;

use App\Modules\Modelos\Contracts\ModeloRepositoryInterface;
use App\Modules\Modelos\Contracts\ModeloServiceInterface;
use App\Modules\Modelos\Domain\AnalisadorDeVariaveis;
use App\Modules\Modelos\Repositories\Eloquent\ModeloRepository;
use App\Modules\Modelos\Services\ModeloService;
use App\Shared\Providers\ModuloServiceProvider;

final class ModelosServiceProvider extends ModuloServiceProvider
{
    /**
     * @return array<class-string, class-string>
     */
    public function bindings(): array
    {
        return [
            ModeloServiceInterface::class => ModeloService::class,
            ModeloRepositoryInterface::class => ModeloRepository::class,
            AnalisadorDeVariaveis::class => AnalisadorDeVariaveis::class,
        ];
    }

    public function prefixoDoModulo(): string
    {
        return 'api/v1/modelos';
    }

    protected function rotaDoModulo(): string
    {
        return __DIR__.'/../Routes/api.php';
    }
}
