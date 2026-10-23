<?php

declare(strict_types=1);

namespace App\Modules\Imoveis\Providers;

use App\Modules\Imoveis\Contracts\ConsultaEnderecoServiceInterface;
use App\Modules\Imoveis\Contracts\EnderecoCepRepositoryInterface;
use App\Modules\Imoveis\Contracts\ImovelRepositoryInterface;
use App\Modules\Imoveis\Contracts\ImovelServiceInterface;
use App\Modules\Imoveis\Contracts\ImovelSubRecursoRepositoryInterface;
use App\Modules\Imoveis\Contracts\ImovelSubRecursoServiceInterface;
use App\Modules\Imoveis\Repositories\Eloquent\EnderecoCepRepository;
use App\Modules\Imoveis\Repositories\Eloquent\ImovelRepository;
use App\Modules\Imoveis\Repositories\Eloquent\ImovelSubRecursoRepository;
use App\Modules\Imoveis\Services\ConsultaEnderecoService;
use App\Modules\Imoveis\Services\ImovelService;
use App\Modules\Imoveis\Services\ImovelSubRecursoService;
use App\Shared\Providers\ModuloServiceProvider;

final class ImoveisServiceProvider extends ModuloServiceProvider
{
    /**
     * @return array<class-string, class-string>
     */
    public function bindings(): array
    {
        return [
            ImovelServiceInterface::class => ImovelService::class,
            ImovelRepositoryInterface::class => ImovelRepository::class,
            ImovelSubRecursoServiceInterface::class => ImovelSubRecursoService::class,
            ImovelSubRecursoRepositoryInterface::class => ImovelSubRecursoRepository::class,
            ConsultaEnderecoServiceInterface::class => ConsultaEnderecoService::class,
            EnderecoCepRepositoryInterface::class => EnderecoCepRepository::class,
        ];
    }

    public function prefixoDoModulo(): string
    {
        return 'api/v1/imoveis';
    }

    protected function rotaDoModulo(): string
    {
        return __DIR__.'/../Routes/api.php';
    }
}
