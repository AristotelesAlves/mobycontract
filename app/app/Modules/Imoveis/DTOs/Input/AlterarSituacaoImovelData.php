<?php

declare(strict_types=1);

namespace App\Modules\Imoveis\DTOs\Input;

use App\Modules\Imoveis\Domain\Enums\SituacaoImovel;
use App\Shared\Application\Support\DadosDeEntrada;

final readonly class AlterarSituacaoImovelData
{
    public function __construct(
        public int $id,
        public SituacaoImovel $situacao,
    ) {}

    /**
     * @param  array<string, mixed>  $validated
     */
    public static function fromValidated(array $validated): self
    {
        return new self(
            id: DadosDeEntrada::inteiro($validated, 'id'),
            situacao: SituacaoImovel::from(DadosDeEntrada::texto($validated, 'status')),
        );
    }
}
