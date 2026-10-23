<?php

declare(strict_types=1);

namespace App\Shared\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Recurso de leitura com identidade de requisição (R-11.4).
 *
 * @template TResource of object
 *
 * @property-read int $usuarioId
 * @property-read string|null $requisicaoId
 */
abstract class BaseResource extends JsonResource
{
    public function usuarioId(): int
    {
        return (int) auth()->id();
    }

    public function requisicaoId(): ?string
    {
        $valor = request()->attributes->get('requisicao_id');

        return is_string($valor) ? $valor : null;
    }
}
