<?php

declare(strict_types=1);

namespace App\Modules\Contratos\DTOs\Output;

use App\Modules\Contratos\Domain\Enums\StatusDivida;
use App\Modules\Contratos\Models\Divida;
use DateTimeInterface;

final readonly class DividaResult
{
    public function __construct(
        public int $id,
        public int $contratoId,
        public string $descricao,
        public float $valor,
        public string $status,
        public ?string $dataQuitacao,
        public ?string $criadoEm,
    ) {}

    public static function fromModel(Divida $divida): self
    {
        $status = StatusDivida::from((string) $divida->status);

        return new self(
            id: (int) $divida->id,
            contratoId: (int) $divida->contrato_id,
            descricao: (string) $divida->descricao,
            valor: (float) $divida->valor,
            status: $status->value,
            dataQuitacao: self::dataIso($divida->data_quitacao),
            criadoEm: self::instante($divida->created_at),
        );
    }

    private static function dataIso(mixed $data): ?string
    {
        return $data instanceof DateTimeInterface ? $data->format('Y-m-d') : null;
    }

    private static function instante(mixed $instante): ?string
    {
        return $instante instanceof DateTimeInterface
            ? $instante->format(DATE_ATOM)
            : null;
    }
}
