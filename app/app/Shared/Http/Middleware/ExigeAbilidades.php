<?php

declare(strict_types=1);

namespace App\Shared\Http\Middleware;

use App\Modules\Usuarios\Models\User;
use App\Shared\Domain\Enums\Papel;
use Closure;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

final class ExigeAbilidades
{
    /**
     * @param  Closure(Request): Response  $proximo
     */
    public function handle(Request $request, Closure $proximo, string ...$habilidades): Response
    {
        $usuario = $request->user();

        if (! $usuario instanceof User) {
            throw new AuthenticationException;
        }

        $papel = $this->papelDoUsuario($usuario);

        foreach ($habilidades as $habilidade) {
            if (! $papel->permite($habilidade)) {
                throw new AuthorizationException;
            }
        }

        return $proximo($request);
    }

    private function papelDoUsuario(User $usuario): Papel
    {
        try {
            return Papel::from((string) $usuario->papel);
        } catch (Throwable) {
            throw new AuthorizationException;
        }
    }
}
