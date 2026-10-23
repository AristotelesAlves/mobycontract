<?php

declare(strict_types=1);

namespace App\Modules\Inquilinos\Providers;

use App\Modules\Inquilinos\Contracts\InquilinoRepositoryInterface;
use App\Modules\Inquilinos\Contracts\InquilinoServiceInterface;
use App\Modules\Inquilinos\Contracts\ProfissaoRepositoryInterface;
use App\Modules\Inquilinos\Repositories\Eloquent\InquilinoRepository;
use App\Modules\Inquilinos\Repositories\Eloquent\ProfissaoRepository;
use App\Modules\Inquilinos\Services\InquilinoService;
use App\Shared\Providers\ModuloServiceProvider;

final class InquilinosServiceProvider extends ModuloServiceProvider
{
    /**
     * @return array<class-string, class-string>
     */
    public function bindings(): array
    {
        return [
            InquilinoServiceInterface::class => InquilinoService::class,
            InquilinoRepositoryInterface::class => InquilinoRepository::class,
            ProfissaoRepositoryInterface::class => ProfissaoRepository::class,
        ];
    }

    public function prefixoDoModulo(): string
    {
        return 'api/v1/inquilinos';
    }

    protected function rotaDoModulo(): string
    {
        return __DIR__.'/../Routes/api.php';
    }
}
