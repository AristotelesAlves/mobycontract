<?php

declare(strict_types=1);

namespace App\Modules\Imoveis\DTOs\Output;

use App\Modules\Imoveis\Models\Agendamento;

final readonly class AgendamentoResult
{
    public function __construct(
        public int $id,
        public int $imovelId,
        public string $data,
        public string $hora,
        public string $interessado,
        public ?string $telefone,
        public string $status,
        public ?string $criadoEm,
    ) {}

    public static function fromModel(Agendamento $agendamento): self
    {
        return new self(
            id: (int) $agendamento->id,
            imovelId: (int) $agendamento->imovel_id,
            data: is_string($agendamento->data) ? $agendamento->data : ($agendamento->data instanceof \DateTimeInterface ? $agendamento->data->format('Y-m-d') : ''),
            hora: (string) $agendamento->hora,
            interessado: (string) ($agendamento->interessado->nome ?? ''),
            telefone: $agendamento->telefone_contato !== null ? (string) $agendamento->telefone_contato : null,
            status: (string) $agendamento->status,
            criadoEm: $agendamento->created_at?->toISOString(),
        );
    }
}
