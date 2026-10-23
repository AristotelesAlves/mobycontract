<?php

declare(strict_types=1);

namespace App\Modules\Usuarios\Http\Resources;

use App\Modules\Usuarios\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin User
 */
final class UsuarioResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => (int) $this->id,
            'nome' => (string) $this->nome,
            'email' => (string) $this->email,
            'papel' => (string) $this->papel,
            'ativo' => (bool) $this->ativo,
        ];
    }
}
