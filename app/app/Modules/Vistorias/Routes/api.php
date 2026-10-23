<?php

declare(strict_types=1);

use App\Modules\Vistorias\Http\Controllers\VistoriaArquivoController;
use App\Modules\Vistorias\Http\Controllers\VistoriaController;
use Illuminate\Support\Facades\Route;

/**
 * Vistorias pertencem a um contrato (`/contratos/{contratoId}/vistorias`).
 * O único caminho fora do contrato é o acesso externo pela chave.
 */
Route::middleware('autenticado')->group(function (): void {
    Route::get('/contratos/{contratoId}/vistorias/{vistoriaId}/fotos/{fotoId}', VistoriaArquivoController::class)->whereNumber(['contratoId', 'vistoriaId', 'fotoId'])->middleware('papel:consultar');

    Route::get('/contratos/{contratoId}/vistorias', [VistoriaController::class, 'index'])
        ->whereNumber('contratoId')
        ->middleware('papel:listar')
        ->name('vistorias.listar');

    Route::post('/contratos/{contratoId}/vistorias', [VistoriaController::class, 'store'])
        ->whereNumber('contratoId')
        ->middleware('papel:criar')
        ->name('vistorias.criar');

    Route::get('/contratos/{contratoId}/vistorias/{vistoriaId}', [VistoriaController::class, 'show'])
        ->whereNumber('contratoId')
        ->whereNumber('vistoriaId')
        ->middleware('papel:consultar')
        ->name('vistorias.obter');

    Route::put('/contratos/{contratoId}/vistorias/{vistoriaId}', [VistoriaController::class, 'update'])
        ->whereNumber('contratoId')
        ->whereNumber('vistoriaId')
        ->middleware('papel:alterar')
        ->name('vistorias.alterar');

    Route::put('/contratos/{contratoId}/vistorias/{vistoriaId}/status', [VistoriaController::class, 'alterarStatus'])
        ->whereNumber('contratoId')
        ->whereNumber('vistoriaId')
        ->middleware('papel:alterar')
        ->name('vistorias.alterar_status');

    Route::delete('/contratos/{contratoId}/vistorias/{vistoriaId}', [VistoriaController::class, 'destroy'])
        ->whereNumber('contratoId')
        ->whereNumber('vistoriaId')
        ->middleware('papel:excluir')
        ->name('vistorias.excluir');

    Route::post('/contratos/{contratoId}/vistorias/{vistoriaId}/fotos', [VistoriaController::class, 'registrarFoto'])
        ->whereNumber('contratoId')
        ->whereNumber('vistoriaId')
        ->middleware('papel:alterar')
        ->name('vistorias.registrar_foto');

    Route::delete('/contratos/{contratoId}/vistorias/{vistoriaId}/fotos/{fotoId}', [VistoriaController::class, 'excluirFoto'])
        ->whereNumber('contratoId')
        ->whereNumber('vistoriaId')
        ->whereNumber('fotoId')
        ->middleware('papel:excluir')
        ->name('vistorias.excluir_foto');
});

/**
 * Acesso externo (vistoriador sem conta): a chave única em UUID atua como
 * token de acesso à vistoria, de forma somente-leitura.
 */
Route::get('/vistorias/acesso/{chave}', [VistoriaController::class, 'porChaveDeAcesso'])
    ->whereUuid('chave')
    ->name('vistorias.acesso_publica');
