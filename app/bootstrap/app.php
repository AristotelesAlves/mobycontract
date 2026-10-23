<?php

declare(strict_types=1);

use App\Modules\Usuarios\Http\Middleware\AutenticacaoPorCookieMiddleware;
use App\Modules\Usuarios\Http\Middleware\SingleSessionPerIpMiddleware;
use App\Modules\Usuarios\Http\Middleware\TokenBindingMiddleware;
use App\Shared\Application\Exceptions\ApiException;
use App\Shared\Http\Middleware\ApiAuthenticate;
use App\Shared\Http\Middleware\ExigeAbilidades;
use App\Shared\Http\Middleware\IdentificadorDeRequisicao;
use App\Shared\Infrastructure\Http\FormatadorDeErroDeFramework;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        api: __DIR__.'/../routes/api.php',
        apiPrefix: 'api/v1',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->api(prepend: [
            AutenticacaoPorCookieMiddleware::class,
            IdentificadorDeRequisicao::class,
        ]);

        $middleware->api(append: [
            TokenBindingMiddleware::class,
            SingleSessionPerIpMiddleware::class,
        ]);

        $middleware->alias([
            'autenticado' => ApiAuthenticate::class,
            'papel' => ExigeAbilidades::class,
            'token.binding' => TokenBindingMiddleware::class,
            'session.single_ip' => SingleSessionPerIpMiddleware::class,
        ]);

        $middleware->throttleApi('120,1');
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->dontReport([
            ApiException::class,
        ]);

        $exceptions->render(static function (Throwable $erro, Request $request): ?JsonResponse {
            if (! $request->is('api/*')) {
                return null;
            }

            return app(FormatadorDeErroDeFramework::class)->render($request, $erro);
        });

        $exceptions->shouldRenderJsonWhen(
            static fn (Request $request): bool => $request->is('api/*') || $request->expectsJson()
        );
    })
    ->create();
