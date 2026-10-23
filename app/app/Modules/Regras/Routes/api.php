<?php

declare(strict_types=1);

use App\Modules\Regras\Http\Controllers\RegrasController;
use Illuminate\Support\Facades\Route;

Route::middleware('autenticado')->group(function (): void {
    Route::get('/', [RegrasController::class, 'show'])->middleware('papel:consultar')->name('regras.obter');

    Route::put('/', [RegrasController::class, 'update'])->middleware('papel:alterar')->name('regras.alterar');
});
