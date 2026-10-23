<?php

declare(strict_types=1);

use App\Modules\Usuarios\Http\Controllers\SessaoController;
use App\Modules\Usuarios\Http\Controllers\UsuarioController;
use Illuminate\Support\Facades\Route;

Route::post('/sessao/entrar', [SessaoController::class, 'entrar'])
    ->middleware('throttle:login')
    ->name('usuarios.sessao.entrar');

Route::post('/sessao/renovar', [SessaoController::class, 'renovar'])
    ->middleware(['autenticado', 'throttle:login'])
    ->name('usuarios.sessao.renovar');

Route::middleware('autenticado')->group(function (): void {
    Route::get('/sessao', [SessaoController::class, 'consultar'])
        ->name('usuarios.sessao.consultar');

    Route::delete('/sessao', [SessaoController::class, 'sair'])
        ->name('usuarios.sessao.sair');

    Route::delete('/sessao/todas', [SessaoController::class, 'sairTodas'])
        ->name('usuarios.sessao.sair_todas');

    Route::get('/', [UsuarioController::class, 'index'])
        ->middleware('papel:gerenciarUsuarios')
        ->name('usuarios.listar');

    Route::post('/', [UsuarioController::class, 'store'])
        ->middleware('papel:gerenciarUsuarios')
        ->name('usuarios.criar');

    Route::get('/operadores', [UsuarioController::class, 'operadores'])
        ->name('usuarios.operadores');

    Route::get('/{id}', [UsuarioController::class, 'show'])
        ->whereNumber('id')
        ->name('usuarios.obter');

    Route::put('/{id}', [UsuarioController::class, 'update'])
        ->whereNumber('id')
        ->middleware('papel:gerenciarUsuarios')
        ->name('usuarios.alterar');

    Route::put('/{id}/senha', [UsuarioController::class, 'alterarSenha'])
        ->whereNumber('id')
        ->middleware('papel:gerenciarUsuarios')
        ->name('usuarios.alterar_senha');
});
