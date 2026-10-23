<?php

declare(strict_types=1);

namespace App\Modules\Leads\Providers;

use App\Modules\Leads\Contracts\LeadRepositoryInterface;
use App\Modules\Leads\Contracts\LeadServiceInterface;
use App\Modules\Leads\Repositories\Eloquent\LeadRepository;
use App\Modules\Leads\Services\LeadService;
use App\Shared\Providers\ModuloServiceProvider;

final class LeadsServiceProvider extends ModuloServiceProvider
{
    /**
     * @return array<class-string, class-string>
     */
    public function bindings(): array
    {
        return [
            LeadServiceInterface::class => LeadService::class,
            LeadRepositoryInterface::class => LeadRepository::class,
        ];
    }

    public function prefixoDoModulo(): string
    {
        return 'api/v1/leads';
    }

    protected function rotaDoModulo(): string
    {
        return __DIR__.'/../Routes/api.php';
    }
}
