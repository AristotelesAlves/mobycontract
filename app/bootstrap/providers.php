<?php

declare(strict_types=1);
use App\Modules\Contratos\Providers\ContratosServiceProvider;
use App\Modules\Imoveis\Providers\ImoveisServiceProvider;
use App\Modules\Inquilinos\Providers\InquilinosServiceProvider;
use App\Modules\Leads\Providers\LeadsServiceProvider;
use App\Modules\Modelos\Providers\ModelosServiceProvider;
use App\Modules\Regras\Providers\RegrasServiceProvider;
use App\Modules\Usuarios\Providers\UsuariosServiceProvider;
use App\Modules\Vistorias\Providers\VistoriasServiceProvider;
use App\Providers\AppServiceProvider;

return [
    AppServiceProvider::class,
    UsuariosServiceProvider::class,
    ContratosServiceProvider::class,
    ImoveisServiceProvider::class,
    InquilinosServiceProvider::class,
    LeadsServiceProvider::class,
    ModelosServiceProvider::class,
    RegrasServiceProvider::class,
    VistoriasServiceProvider::class,
];
