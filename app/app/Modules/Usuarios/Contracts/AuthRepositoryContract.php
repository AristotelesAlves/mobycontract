<?php

declare(strict_types=1);

namespace App\Modules\Usuarios\Contracts;

use App\Modules\Usuarios\Models\User;

interface AuthRepositoryContract
{
    public function localizarPorEmail(string $email): ?User;
}
