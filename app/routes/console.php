<?php

declare(strict_types=1);

use App\Modules\Usuarios\Jobs\AnonimizarAuditLogsAntigos;
use Illuminate\Support\Facades\Schedule;

/*
|--------------------------------------------------------------------------
| Tarefas agendadas
|--------------------------------------------------------------------------
|
| O aquecimento de cache (`cache:aquecer`) entra junto do módulo que
| possui as leituras caras. Ver R-14.11 em INFRAESTRUTURA_DOCKER_E_CACHE.md.
*/

Schedule::job(new AnonimizarAuditLogsAntigos(180))->dailyAt('03:00')
    ->description('Anonimiza audit logs com mais de 180 dias (LGPD)')
    ->onOneServer()
    ->withoutOverlapping();
