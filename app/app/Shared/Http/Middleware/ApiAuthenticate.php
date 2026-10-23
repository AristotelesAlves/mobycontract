<?php

declare(strict_types=1);

namespace App\Shared\Http\Middleware;

use App\Modules\Usuarios\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class ApiAuthenticate
{
    /** @param Closure(Request): Response $proximo */
    public function handle(Request $request, Closure $proximo): Response
    {
        $usuario = $request->user();
        if (! $usuario instanceof User || ! $usuario->ativo) {
            return response()->json([
                'codigo' => 'UNAUTHENTICATED',
                'mensagem' => 'Autenticação necessária.',
                'campos' => [],
            ], Response::HTTP_UNAUTHORIZED);
        }

        return $proximo($request);
    }
}
