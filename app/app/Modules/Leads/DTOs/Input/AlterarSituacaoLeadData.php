<?php

declare(strict_types=1);

namespace App\Modules\Leads\DTOs\Input;

use App\Modules\Leads\Domain\Enums\SituacaoLead;
use App\Shared\Application\Support\DadosDeEntrada;

final readonly class AlterarSituacaoLeadData
{
    public function __construct(
        public int $id,
        public SituacaoLead $situacao,
    ) {}

    /**
     * @param  array<string, mixed>  $validated
     */
    public static function fromValidated(array $validated): self
    {
        return new self(
            id: DadosDeEntrada::inteiro($validated, 'id'),
            situacao: SituacaoLead::from(DadosDeEntrada::texto($validated, 'status')),
        );
    }
}
