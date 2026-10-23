<?php

declare(strict_types=1);

namespace App\Shared\Providers;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

abstract class ModuloServiceProvider extends ServiceProvider
{
    /**
     * @return array<class-string, class-string>
     */
    abstract public function bindings(): array;

    /**
     * Prefixo completo das rotas do módulo, já com a versão da API.
     */
    abstract public function prefixoDoModulo(): string;

    /**
     * @return array<string, mixed>
     */
    public function configuracoes(): array
    {
        return [];
    }

    public function register(): void
    {
        foreach ($this->bindings() as $interface => $implementacao) {
            $this->app->bind($interface, $implementacao);
        }

        foreach ($this->configuracoes() as $chave => $valor) {
            Config::set($chave, $valor);
        }
    }

    public function boot(): void
    {
        if ($this->app->routesAreCached()) {
            return;
        }

        Route::middleware('api')
            ->prefix($this->prefixoDoModulo())
            ->group($this->rotaDoModulo());
    }

    abstract protected function rotaDoModulo(): string;
}
