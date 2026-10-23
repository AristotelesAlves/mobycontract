<?php

declare(strict_types=1);

namespace App\Modules\Vistorias\DTOs\Input;

use App\Modules\Vistorias\Domain\Enums\TipoVistoria;
use App\Shared\Application\Support\DadosDeEntrada;

/**
 * Os campos são opcionais: somente o que vier informado é alterado.
 */
final readonly class AlterarVistoriaData
{
    /**
     * @param  list<ItemVistoriaData>|null  $itens
     */
    public function __construct(
        public ?TipoVistoria $tipo,
        public ?string $data,
        public ?string $vistoriador,
        public ?string $observacoes,
        public ?array $itens,
        public bool $itensInformados,
    ) {}

    /**
     * @param  array<string, mixed>  $validated
     */
    public static function fromValidated(array $validated): self
    {
        $tipo = DadosDeEntrada::textoOpcional($validated, 'tipo');
        $itensBrutos = $validated['itens'] ?? null;

        $itensConvertidos = [];
        foreach (is_array($itensBrutos) ? $itensBrutos : [] as $item) {
            if (! is_array($item)) {
                continue;
            }

            /** @var array<string, mixed> $item */
            $itensConvertidos[] = ItemVistoriaData::fromValidated($item);
        }
        $itens = is_array($itensBrutos) ? $itensConvertidos : null;

        return new self(
            tipo: $tipo === null ? null : TipoVistoria::from($tipo),
            data: DadosDeEntrada::textoOpcional($validated, 'data'),
            vistoriador: DadosDeEntrada::textoOpcional($validated, 'vistoriador'),
            observacoes: DadosDeEntrada::textoOpcional($validated, 'observacoes'),
            itens: $itens,
            itensInformados: array_key_exists('itens', $validated) && is_array($itensBrutos),
        );
    }
}
