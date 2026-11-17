<?php

declare(strict_types=1);

use App\Modules\Usuarios\Jobs\AnonimizarAuditLogsAntigos;
use Illuminate\Support\Facades\Schedule;
use Illuminate\Support\Facades\Artisan;
use Laravel\Passport\ClientRepository;

Artisan::command('auth:preparar', function (ClientRepository $clientes): void {
    try {
        $cliente = $clientes->personalAccessClient();
    } catch (\RuntimeException) {
        $cliente = null;
    }

    if ($cliente === null || $cliente->revoked) {
        $clientes->createPersonalAccessClient(null, 'MobyContract', 'http://localhost');
        $this->info('Cliente OAuth pessoal criado.');
    } else {
        $this->info('Cliente OAuth pessoal pronto.');
    }
})->purpose('Prepara o cliente OAuth para emitir tokens de login sem duplicar clientes.');

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
