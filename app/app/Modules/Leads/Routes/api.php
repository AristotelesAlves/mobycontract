<?php

declare(strict_types=1);

use App\Modules\Leads\Http\Controllers\LeadController;
use App\Modules\Leads\Http\Controllers\LeadConversaoController;
use Illuminate\Support\Facades\Route;

Route::middleware('autenticado')->group(function (): void {
    Route::get('/', [LeadController::class, 'index'])->middleware('papel:listar')->name('leads.listar');

    Route::post('/', [LeadController::class, 'store'])->middleware('papel:criar')->name('leads.criar');

    Route::get('/{id}', [LeadController::class, 'show'])
        ->whereNumber('id')
        ->middleware('papel:consultar')
        ->name('leads.obter');

    Route::put('/{id}', [LeadController::class, 'update'])
        ->whereNumber('id')
        ->middleware('papel:alterar')
        ->name('leads.alterar');

    Route::put('/{id}/situacao', [LeadController::class, 'alterarSituacao'])
        ->whereNumber('id')
        ->middleware('papel:alterar')
        ->name('leads.alterar_situacao');

    Route::post('/{id}/converter', [LeadConversaoController::class, 'converter'])
        ->whereNumber('id')
        ->middleware('papel:alterar')
        ->name('leads.converter');

    Route::delete('/{id}', [LeadController::class, 'destroy'])
        ->whereNumber('id')
        ->middleware('papel:excluir')
        ->name('leads.excluir');
});
