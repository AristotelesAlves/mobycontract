<?php

declare(strict_types=1);

namespace App\Modules\Usuarios\Repositories\Eloquent;

use App\Modules\Usuarios\Contracts\AuthRepositoryContract;
use App\Modules\Usuarios\Models\User;

final class AuthRepository implements AuthRepositoryContract
{
    public function localizarPorEmail(string $email): ?User
    {
        return User::query()
            ->where('email', $email)
            ->first();
    }
}
