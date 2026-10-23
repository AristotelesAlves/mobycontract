<?php

declare(strict_types=1);

namespace App\Modules\Vistorias\DTOs\Input;

use App\Modules\Vistorias\Domain\Enums\TipoVistoria;
use App\Shared\Application\Support\DadosDeEntrada;

final readonly class CriarVistoriaData
{
    /**
     * @param  list<ItemVistoriaData>  $itens
     */
    public function __construct(
        public int $contratoId,
        public TipoVistoria $tipo,
        public string $data,
        public ?string $vistoriador,
        public ?string $observacoes,
        public array $itens,
    ) {}

    /**
     * @param  array<string, mixed>  $validated
     */
    public static function fromValidated(int $contratoId, array $validated): self
    {
        return new self(
            contratoId: $contratoId,
            tipo: TipoVistoria::from(DadosDeEntrada::texto($validated, 'tipo')),
            data: DadosDeEntrada::texto($validated, 'data'),
            vistoriador: DadosDeEntrada::textoOpcional($validated, 'vistoriador'),
            observacoes: DadosDeEntrada::textoOpcional($validated, 'observacoes'),
            itens: self::itens($validated),
        );
    }

    /**
     * @param  array<string, mixed>  $validated
     * @return list<ItemVistoriaData>
     */
    private static function itens(array $validated): array
    {
        $brutos = $validated['itens'] ?? [];

        if (! is_array($brutos)) {
            return [];
        }

        $itens = [];

        foreach ($brutos as $item) {
            if (is_array($item)) {
                /** @var array<string, mixed> $item */
                $itens[] = ItemVistoriaData::fromValidated($item);
            }
        }

        return $itens;
    }
}
