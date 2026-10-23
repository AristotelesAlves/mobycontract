<?php

declare(strict_types=1);

namespace App\Modules\Regras\Providers;

use App\Modules\Regras\Contracts\RegraImobiliariaRepositoryInterface;
use App\Modules\Regras\Contracts\RegraImobiliariaServiceInterface;
use App\Modules\Regras\Repositories\Eloquent\RegraImobiliariaRepository;
use App\Modules\Regras\Services\RegraImobiliariaService;
use App\Shared\Providers\ModuloServiceProvider;

final class RegrasServiceProvider extends ModuloServiceProvider
{
    /**
     * @return array<class-string, class-string>
     */
    public function bindings(): array
    {
        return [
            RegraImobiliariaRepositoryInterface::class => RegraImobiliariaRepository::class,
            RegraImobiliariaServiceInterface::class => RegraImobiliariaService::class,
        ];
    }

    public function prefixoDoModulo(): string
    {
        return 'api/v1/regras-imobiliarias';
    }

    protected function rotaDoModulo(): string
    {
        return __DIR__.'/../Routes/api.php';
    }
}
