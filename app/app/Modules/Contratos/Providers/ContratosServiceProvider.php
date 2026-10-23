<?php

declare(strict_types=1);

namespace App\Modules\Contratos\Providers;

use App\Modules\Contratos\Contracts\AditivoRepositoryInterface;
use App\Modules\Contratos\Contracts\AditivoServiceInterface;
use App\Modules\Contratos\Contracts\ContratoRepositoryInterface;
use App\Modules\Contratos\Contracts\ContratoServiceInterface;
use App\Modules\Contratos\Contracts\DividaRepositoryInterface;
use App\Modules\Contratos\Contracts\DividaServiceInterface;
use App\Modules\Contratos\Repositories\Eloquent\AditivoRepository;
use App\Modules\Contratos\Repositories\Eloquent\ContratoRepository;
use App\Modules\Contratos\Repositories\Eloquent\DividaRepository;
use App\Modules\Contratos\Services\AditivoService;
use App\Modules\Contratos\Services\ContratoService;
use App\Modules\Contratos\Services\DividaService;
use App\Modules\Contratos\Services\Fluxos\ContratoDadosDerivadosFactory;
use App\Modules\Contratos\Services\Fluxos\DerivadorDeDadosExtras;
use App\Modules\Contratos\Services\Fluxos\GeradorDeParcelas;
use App\Shared\Providers\ModuloServiceProvider;

final class ContratosServiceProvider extends ModuloServiceProvider
{
    /**
     * @return array<class-string, class-string>
     */
    public function bindings(): array
    {
        return [
            ContratoRepositoryInterface::class => ContratoRepository::class,
            ContratoServiceInterface::class => ContratoService::class,
            AditivoRepositoryInterface::class => AditivoRepository::class,
            AditivoServiceInterface::class => AditivoService::class,
            DividaRepositoryInterface::class => DividaRepository::class,
            DividaServiceInterface::class => DividaService::class,
            DerivadorDeDadosExtras::class => DerivadorDeDadosExtras::class,
            ContratoDadosDerivadosFactory::class => ContratoDadosDerivadosFactory::class,
            GeradorDeParcelas::class => GeradorDeParcelas::class,
        ];
    }

    public function prefixoDoModulo(): string
    {
        return 'api/v1/contratos';
    }

    protected function rotaDoModulo(): string
    {
        return __DIR__.'/../Routes/api.php';
    }
}
