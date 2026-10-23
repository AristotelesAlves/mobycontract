<?php

declare(strict_types=1);

namespace App\Modules\Vistorias\DTOs\Input;

use App\Modules\Vistorias\Domain\Enums\EstadoVistoriaItem;
use App\Shared\Application\Support\DadosDeEntrada;

final readonly class ItemVistoriaData
{
    public function __construct(
        public string $ambiente,
        public string $item,
        public EstadoVistoriaItem $estado,
        public ?string $observacao,
    ) {}

    /**
     * @param  array<string, mixed>  $item
     */
    public static function fromValidated(array $item): self
    {
        return new self(
            ambiente: DadosDeEntrada::texto($item, 'ambiente'),
            item: DadosDeEntrada::texto($item, 'item'),
            estado: EstadoVistoriaItem::from(DadosDeEntrada::texto($item, 'estado')),
            observacao: DadosDeEntrada::textoOpcional($item, 'observacao'),
        );
    }

    /**
     * @return array{ambiente: string, item: string, estado: string, observacao: string|null}
     */
    public function paraAtributos(): array
    {
        return [
            'ambiente' => $this->ambiente,
            'item' => $this->item,
            'estado' => $this->estado->value,
            'observacao' => $this->observacao,
        ];
    }
}
