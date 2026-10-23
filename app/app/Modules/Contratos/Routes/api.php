<?php

declare(strict_types=1);

use App\Modules\Contratos\Http\Controllers\AditivoController;
use App\Modules\Contratos\Http\Controllers\ConsultarTransacaoBlockchainController;
use App\Modules\Contratos\Http\Controllers\ContratoController;
use App\Modules\Contratos\Http\Controllers\DividaController;
use App\Modules\Contratos\Http\Controllers\DownloadDocumentoController;
use App\Modules\Contratos\Http\Controllers\ValidacaoPublicaController;
use App\Modules\Contratos\Http\Controllers\VerificarDocumentoController;
use Illuminate\Support\Facades\Route;

Route::post('/validar', ValidacaoPublicaController::class)->middleware('throttle:validacao-publica');

Route::get('/{id}/exportacao-pdf', [DownloadDocumentoController::class, 'verificarExportacao'])
    ->whereNumber('id')->middleware('signed:relative')->name('contratos.exportacao-pdf');

Route::middleware('autenticado')->group(function (): void {
    Route::post('/verificar-transacao', ConsultarTransacaoBlockchainController::class)
        ->middleware('papel:consultar');
    Route::post('/{id}/verificar-documento', VerificarDocumentoController::class)
        ->whereNumber('id')->middleware(['papel:consultar', 'throttle:verificacao-documento']);
    Route::post('/{id}/exportacao-pdf', [DownloadDocumentoController::class, 'autorizarExportacao'])
        ->whereNumber('id')->middleware('papel:consultar');
    Route::get('/{id}/comprovante-garantia', [DownloadDocumentoController::class, 'garantia'])->whereNumber('id')->middleware('papel:consultar');

    Route::get('/{id}/documento', [DownloadDocumentoController::class, 'contrato'])->whereNumber('id')->middleware('papel:consultar');
    Route::get('/{contratoId}/aditivos/{aditivoId}/documento', [DownloadDocumentoController::class, 'aditivo'])->whereNumber(['contratoId', 'aditivoId'])->middleware('papel:consultar');

    Route::get('/', [ContratoController::class, 'index'])->middleware('papel:listar')->name('contratos.listar');

    Route::post('/', [ContratoController::class, 'store'])->middleware('papel:criar')->name('contratos.criar');

    Route::get('/{id}', [ContratoController::class, 'show'])
        ->whereNumber('id')
        ->middleware('papel:consultar')
        ->name('contratos.obter');

    Route::put('/{id}', [ContratoController::class, 'update'])
        ->whereNumber('id')
        ->middleware('papel:alterar')
        ->name('contratos.alterar');

    Route::put('/{id}/status', [ContratoController::class, 'alterarStatus'])
        ->whereNumber('id')
        ->middleware('papel:alterar')
        ->name('contratos.alterar_status');

    Route::delete('/{id}', [ContratoController::class, 'destroy'])
        ->whereNumber('id')
        ->middleware('papel:excluir')
        ->name('contratos.excluir');

    Route::post('/{id}/assinaturas/{assinaturaId}/assinar', [ContratoController::class, 'assinar'])
        ->whereNumber('id')
        ->whereNumber('assinaturaId')
        ->middleware('papel:alterar')
        ->name('contratos.assinar');

    Route::get('/{id}/etapas', [ContratoController::class, 'etapas'])
        ->whereNumber('id')
        ->middleware('papel:consultar')
        ->name('contratos.etapas');

    Route::post('/{id}/documento-fisico', [ContratoController::class, 'confirmarDocumento'])
        ->whereNumber('id')
        ->middleware('papel:alterar')
        ->name('contratos.confirmar_documento');

    Route::post('/{id}/blockchain', [ContratoController::class, 'registrarBlockchain'])
        ->whereNumber('id')
        ->middleware('papel:validar')
        ->name('contratos.registrar_blockchain');

    Route::get('/{id}/parcelas', [ContratoController::class, 'parcelas'])
        ->whereNumber('id')
        ->middleware('papel:consultar')
        ->name('contratos.parcelas');

    Route::get('/{id}/dividas', [DividaController::class, 'index'])
        ->whereNumber('id')
        ->middleware('papel:consultar')
        ->name('dividas.listar');

    Route::post('/{id}/dividas/{dividaId}/quitar', [DividaController::class, 'quitar'])
        ->whereNumber('id')
        ->whereNumber('dividaId')
        ->middleware('papel:alterar')
        ->name('dividas.quitar');

    Route::post('/{id}/parcelas/{parcelaId}/pagamento', [ContratoController::class, 'registrarPagamento'])
        ->whereNumber('id')
        ->whereNumber('parcelaId')
        ->middleware('papel:alterar')
        ->name('contratos.registrar_pagamento');

    Route::post('/{id}/parcelas/{parcelaId}/encargos', [ContratoController::class, 'lancarEncargo'])
        ->whereNumber('id')
        ->whereNumber('parcelaId')
        ->middleware('papel:alterar')
        ->name('contratos.lancar_encargo');

    Route::prefix('{contratoId}/aditivos')->whereNumber('contratoId')->group(function (): void {
        Route::get('/', [AditivoController::class, 'index'])->middleware('papel:listar')->name('aditivos.listar');
        Route::post('/', [AditivoController::class, 'store'])->middleware('papel:criar')->name('aditivos.criar');

        Route::get('/{aditivoId}', [AditivoController::class, 'show'])
            ->whereNumber('aditivoId')
            ->middleware('papel:consultar')
            ->name('aditivos.obter');

        Route::put('/{aditivoId}', [AditivoController::class, 'update'])
            ->whereNumber('aditivoId')
            ->middleware('papel:alterar')
            ->name('aditivos.alterar');

        Route::delete('/{aditivoId}', [AditivoController::class, 'destroy'])
            ->whereNumber('aditivoId')
            ->middleware('papel:excluir')
            ->name('aditivos.excluir');

        Route::put('/{aditivoId}/status', [AditivoController::class, 'alterarStatus'])
            ->whereNumber('aditivoId')
            ->middleware('papel:alterar')
            ->name('aditivos.alterar_status');

        Route::post('/{aditivoId}/assinaturas/{assinaturaId}/assinar', [AditivoController::class, 'assinar'])
            ->whereNumber('aditivoId')
            ->whereNumber('assinaturaId')
            ->middleware('papel:alterar')
            ->name('aditivos.assinar');

        Route::post('/{aditivoId}/documento-fisico', [AditivoController::class, 'confirmarDocumento'])
            ->whereNumber('aditivoId')
            ->middleware('papel:alterar')
            ->name('aditivos.confirmar_documento');

        Route::post('/{aditivoId}/blockchain', [AditivoController::class, 'registrarBlockchain'])
            ->whereNumber('aditivoId')
            ->middleware('papel:validar')
            ->name('aditivos.registrar_blockchain');
    });
});
