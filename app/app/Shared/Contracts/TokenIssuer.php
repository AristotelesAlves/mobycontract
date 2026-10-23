<?php

declare(strict_types=1);

namespace App\Shared\Contracts;

use App\Modules\Usuarios\Models\User;

interface TokenIssuer
{
    /**
     * @return array{token: string, expiraEm: string|null}
     */
    public function emitir(User $usuario): array;

    public function revogar(User $usuario): void;
}
