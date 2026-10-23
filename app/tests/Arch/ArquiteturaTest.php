<?php

declare(strict_types=1);

use App\Modules\Usuarios\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

arch('camadas nao vazam framework para o dominio')
    ->expect('App\Modules\Contratos\Domain')
    ->not->toUse(['Illuminate', 'Laravel']);

arch('servicos nao dependem de http, cache ou eloquent')
    ->expect('App\Modules\*\Services')
    ->not->toUse([
        Cache::class,
        Http::class,
        DB::class,
        Model::class,
        Request::class,
    ]);

arch('repositorios nao retornam modelo eloquent para fora do modulo')
    ->expect('App\Modules\*\Repositories')
    ->not->toUse([Request::class, JsonResponse::class]);

arch('controllers nao acessam eloquent diretamente')
    ->expect('App\Modules\*\Http\Controllers')
    ->not->toUse([DB::class, Builder::class]);

arch('shared nao depende de camadas de aplicacao de modulos')
    ->expect('App\Shared')
    ->not->toUse([
        'App\Modules\*\Services',
        'App\Modules\*\Repositories',
        'App\Modules\*\Http',
    ]);

arch('modulos nao acessam repositories de outro modulo')
    ->expect('App\Modules\Usuarios')
    ->not->toUse(['App\Modules\Contratos', 'App\Modules\Financeiro']);

arch('enums de dominio nao usam framework')
    ->expect('App\Shared\Domain')
    ->not->toUse(['Illuminate', 'Laravel']);

arch('modelo de usuario expoe apenas o que a sessao devolve')
    ->expect(User::class)
    ->toHaveMethods(['enumPapel']);
