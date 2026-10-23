<?php

declare(strict_types=1);

namespace App\Shared\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

final class IdentificadorDeRequisicao
{
    /**
     * @param  Closure(Request): Response  $proximo
     */
    public function handle(Request $request, Closure $proximo): Response
    {
        $identificador = $request->header('X-Request-Id');

        if (! is_string($identificador) || $identificador === '') {
            $identificador = Str::uuid()->toString();
        }

        $request->headers->set('X-Request-Id', $identificador);

        $resposta = $proximo($request);
        $resposta->headers->set('X-Request-Id', $identificador);

        return $resposta;
    }
}
