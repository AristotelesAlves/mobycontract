<?php

declare(strict_types=1);

use App\Modules\Imoveis\Http\Controllers\AgendamentoController;
use App\Modules\Imoveis\Http\Controllers\AnotacaoController;
use App\Modules\Imoveis\Http\Controllers\EnderecoController;
use App\Modules\Imoveis\Http\Controllers\ImovelController;
use App\Modules\Imoveis\Http\Controllers\ImovelFotoController;
use App\Modules\Imoveis\Http\Controllers\InteressadoController;
use App\Modules\Imoveis\Http\Controllers\ManutencaoController;
use Illuminate\Support\Facades\Route;

Route::middleware('autenticado')->group(function (): void {
    Route::get('/{id}/fotos', [ImovelFotoController::class, 'index'])->whereNumber('id')->middleware('papel:consultar');
    Route::post('/{id}/fotos', [ImovelFotoController::class, 'store'])->whereNumber('id')->middleware('papel:alterar');
    Route::get('/{id}/fotos/{fotoId}', [ImovelFotoController::class, 'show'])->whereNumber(['id', 'fotoId'])->middleware('papel:consultar');
    Route::delete('/{id}/fotos/{fotoId}', [ImovelFotoController::class, 'destroy'])->whereNumber(['id', 'fotoId'])->middleware('papel:alterar');

    Route::get('/enderecos/{cep}', [EnderecoController::class, 'consultarPorCep'])
        ->middleware('papel:consultar')
        ->name('enderecos.consultar_cep');

    Route::get('/', [ImovelController::class, 'index'])
        ->middleware('papel:listar')
        ->name('imoveis.listar');

    Route::post('/', [ImovelController::class, 'store'])
        ->middleware('papel:criar')
        ->name('imoveis.criar');

    Route::get('/{id}/custo-mensal', [ImovelController::class, 'custoMensal'])
        ->whereNumber('id')
        ->middleware('papel:consultar')
        ->name('imoveis.custo_mensal');

    Route::get('/{id}/ocupacao', [ImovelController::class, 'ocupacao'])
        ->whereNumber('id')
        ->middleware('papel:consultar')
        ->name('imoveis.ocupacao');

    Route::get('/{id}', [ImovelController::class, 'show'])
        ->whereNumber('id')
        ->middleware('papel:consultar')
        ->name('imoveis.obter');

    Route::put('/{id}', [ImovelController::class, 'update'])
        ->whereNumber('id')
        ->middleware('papel:alterar')
        ->name('imoveis.alterar');

    Route::put('/{id}/situacao', [ImovelController::class, 'alterarSituacao'])
        ->whereNumber('id')
        ->middleware('papel:alterar')
        ->name('imoveis.alterar_situacao');

    Route::delete('/{id}', [ImovelController::class, 'destroy'])
        ->whereNumber('id')
        ->middleware('papel:excluir')
        ->name('imoveis.excluir');

    Route::get('/{imovelId}/anotacoes', [AnotacaoController::class, 'index'])
        ->whereNumber('imovelId')
        ->middleware('papel:consultar')
        ->name('imoveis.anotacoes.listar');

    Route::post('/{imovelId}/anotacoes', [AnotacaoController::class, 'store'])
        ->whereNumber('imovelId')
        ->middleware('papel:criar')
        ->name('imoveis.anotacoes.criar');

    Route::delete('/{imovelId}/anotacoes/{anotacaoId}', [AnotacaoController::class, 'destroy'])
        ->whereNumber('imovelId')
        ->whereNumber('anotacaoId')
        ->middleware('papel:excluir')
        ->name('imoveis.anotacoes.excluir');

    Route::get('/{imovelId}/agendamentos', [AgendamentoController::class, 'index'])
        ->whereNumber('imovelId')
        ->middleware('papel:consultar')
        ->name('imoveis.agendamentos.listar');

    Route::post('/{imovelId}/agendamentos', [AgendamentoController::class, 'store'])
        ->whereNumber('imovelId')
        ->middleware('papel:criar')
        ->name('imoveis.agendamentos.criar');

    Route::put('/{imovelId}/agendamentos/{agendamentoId}/status', [AgendamentoController::class, 'alterarSituacao'])
        ->whereNumber('imovelId')
        ->whereNumber('agendamentoId')
        ->middleware('papel:alterar')
        ->name('imoveis.agendamentos.alterar_situacao');

    Route::get('/{imovelId}/interessados', [InteressadoController::class, 'index'])
        ->whereNumber('imovelId')
        ->middleware('papel:consultar')
        ->name('imoveis.interessados.listar');

    Route::post('/{imovelId}/interessados', [InteressadoController::class, 'store'])
        ->whereNumber('imovelId')
        ->middleware('papel:criar')
        ->name('imoveis.interessados.criar');

    Route::put('/{imovelId}/interessados/{interessadoId}/status', [InteressadoController::class, 'alterarSituacao'])
        ->whereNumber('imovelId')
        ->whereNumber('interessadoId')
        ->middleware('papel:alterar')
        ->name('imoveis.interessados.alterar_situacao');

    Route::get('/{imovelId}/manutencoes', [ManutencaoController::class, 'index'])
        ->whereNumber('imovelId')
        ->middleware('papel:consultar')
        ->name('imoveis.manutencoes.listar');

    Route::post('/{imovelId}/manutencoes', [ManutencaoController::class, 'store'])
        ->whereNumber('imovelId')
        ->middleware('papel:criar')
        ->name('imoveis.manutencoes.criar');

    Route::put('/{imovelId}/manutencoes/{manutencaoId}', [ManutencaoController::class, 'update'])
        ->whereNumber('imovelId')
        ->whereNumber('manutencaoId')
        ->middleware('papel:alterar')
        ->name('imoveis.manutencoes.alterar');
});
