<?php

declare(strict_types=1);

use App\Modules\Inquilinos\Http\Controllers\InquilinoController;
use App\Modules\Inquilinos\Http\Controllers\ProfissaoController;
use Illuminate\Support\Facades\Route;

Route::middleware('autenticado')->group(function (): void {
    Route::get('/profissoes', ProfissaoController::class)->middleware('papel:consultar');
    Route::get('/', [InquilinoController::class, 'index'])->middleware('papel:listar')->name('inquilinos.listar');

    Route::post('/', [InquilinoController::class, 'store'])->middleware('papel:criar')->name('inquilinos.criar');

    Route::get('/{id}', [InquilinoController::class, 'show'])
        ->whereNumber('id')
        ->middleware('papel:consultar')
        ->name('inquilinos.obter');

    Route::put('/{id}', [InquilinoController::class, 'update'])
        ->whereNumber('id')
        ->middleware('papel:alterar')
        ->name('inquilinos.alterar');

    Route::delete('/{id}', [InquilinoController::class, 'destroy'])
        ->whereNumber('id')
        ->middleware('papel:excluir')
        ->name('inquilinos.excluir');

    Route::get('/{id}/indicadores', [InquilinoController::class, 'indicadores'])
        ->whereNumber('id')
        ->middleware('papel:consultar')
        ->name('inquilinos.indicadores');
});
