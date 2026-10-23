<?php

declare(strict_types=1);

use App\Modules\Modelos\Http\Controllers\ModeloController;
use Illuminate\Support\Facades\Route;

Route::middleware('autenticado')->group(function (): void {
    Route::get('/', [ModeloController::class, 'index'])->middleware('papel:listar')->name('modelos.listar');

    Route::post('/', [ModeloController::class, 'store'])->middleware('papel:criar')->name('modelos.criar');

    Route::post('/validar-conteudo', [ModeloController::class, 'validarConteudo'])
        ->middleware('papel:criar')
        ->name('modelos.validar_conteudo');

    Route::get('/{id}', [ModeloController::class, 'show'])
        ->whereNumber('id')
        ->middleware('papel:consultar')
        ->name('modelos.obter');

    Route::put('/{id}', [ModeloController::class, 'update'])
        ->whereNumber('id')
        ->middleware('papel:alterar')
        ->name('modelos.alterar');

    Route::post('/{id}/clonar', [ModeloController::class, 'clonar'])
        ->whereNumber('id')
        ->middleware('papel:criar')
        ->name('modelos.clonar');

    Route::put('/{id}/ativar', [ModeloController::class, 'ativar'])
        ->whereNumber('id')
        ->middleware('papel:alterar')
        ->name('modelos.ativar');

    Route::put('/{id}/inativar', [ModeloController::class, 'inativar'])
        ->whereNumber('id')
        ->middleware('papel:alterar')
        ->name('modelos.inativar');
});
