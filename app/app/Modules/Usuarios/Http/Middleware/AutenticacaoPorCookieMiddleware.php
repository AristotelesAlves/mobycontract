<?php

declare(strict_types=1);

namespace App\Modules\Usuarios\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class AutenticacaoPorCookieMiddleware
{
    public const string NOME_COOKIE = 'mobycontract_access';

    /** @param Closure(Request): Response $proximo */
    public function handle(Request $request, Closure $proximo): Response
    {
        $token = $request->bearerToken() === null
            ? $request->cookie(self::NOME_COOKIE)
            : null;

        if (is_string($token) && preg_match('/^[A-Za-z0-9._-]+$/D', $token) === 1) {
            $request->headers->set('Authorization', 'Bearer '.$token);
        }

        return $proximo($request);
    }
}
