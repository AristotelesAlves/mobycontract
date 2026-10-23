<?php

declare(strict_types=1);

namespace App\Modules\Usuarios\DTOs\Output;

use App\Modules\Usuarios\Models\User;

final readonly class UsuarioResult
{
    public function __construct(
        public int $id,
        public string $nome,
        public string $email,
        public string $papel,
        public bool $ativo,
    ) {}

    public static function fromModel(User $usuario): self
    {
        return new self(
            id: (int) $usuario->id,
            nome: (string) $usuario->nome,
            email: (string) $usuario->email,
            papel: (string) $usuario->papel,
            ativo: (bool) $usuario->ativo,
        );
    }
}
