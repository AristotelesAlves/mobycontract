<?php

declare(strict_types=1);

namespace App\Modules\Vistorias\DTOs\Output;

use App\Modules\Vistorias\Domain\Enums\StatusVistoria;
use App\Modules\Vistorias\Models\Vistoria;
use DateTimeInterface;

final readonly class VistoriaResult
{
    /**
     * @param  list<VistoriaItemResult>  $itens
     * @param  list<VistoriaFotoResult>  $fotos
     */
    public function __construct(
        public int $id,
        public int $contratoId,
        public string $numero,
        public string $tipo,
        public string $data,
        public ?string $vistoriador,
        public string $status,
        public bool $podeAlterar,
        public bool $podeExcluir,
        public ?string $observacoes,
        public string $chaveAcesso,
        public array $itens,
        public array $fotos,
        public ?string $criadoEm,
        public ?string $atualizadoEm,
        public ?string $contratoNumero = null,
        public ?string $imovelTitulo = null,
    ) {}

    public static function fromModel(Vistoria $vistoria): self
    {
        $status = StatusVistoria::from((string) $vistoria->status);
        $contrato = $vistoria->contrato;
        $imovel = $contrato?->imovel;

        return new self(
            id: (int) $vistoria->id,
            contratoId: (int) $vistoria->contrato_id,
            numero: (string) $vistoria->numero,
            tipo: (string) $vistoria->tipo,
            data: self::dataIso($vistoria->data),
            vistoriador: self::textoOuNulo($vistoria->vistoriador),
            status: $status->value,
            podeAlterar: $status->permiteEdicao(),
            podeExcluir: $status->permiteExclusao(),
            observacoes: self::textoOuNulo($vistoria->observacoes),
            chaveAcesso: (string) $vistoria->chave_acesso,
            itens: array_values($vistoria->itens
                ->map(static fn ($item): VistoriaItemResult => new VistoriaItemResult(
                    id: (int) $item->id,
                    vistoriaId: (int) $item->vistoria_id,
                    ambiente: (string) $item->ambiente,
                    item: (string) $item->item,
                    estado: (string) $item->estado,
                    observacao: self::textoOuNulo($item->observacao),
                ))
                ->all()),
            fotos: array_values($vistoria->fotos
                ->map(static fn ($foto): VistoriaFotoResult => new VistoriaFotoResult(
                    id: (int) $foto->id,
                    vistoriaId: (int) $foto->vistoria_id,
                    descricao: self::textoOuNulo($foto->descricao),
                    nomeAnexo: (string) $foto->nome_anexo,
                    anexo: self::textoOuNulo($foto->anexo),
                ))
                ->all()),
            criadoEm: self::instante($vistoria->created_at),
            atualizadoEm: self::instante($vistoria->updated_at),
            contratoNumero: $contrato === null ? null : (string) $contrato->numero,
            imovelTitulo: $imovel === null ? null : (string) $imovel->titulo,
        );
    }

    private static function dataIso(mixed $data): string
    {
        return $data instanceof DateTimeInterface ? $data->format('Y-m-d') : '';
    }

    private static function instante(mixed $instante): ?string
    {
        return $instante instanceof DateTimeInterface
            ? $instante->format(DATE_ATOM)
            : null;
    }

    private static function textoOuNulo(mixed $valor): ?string
    {
        if (! is_scalar($valor)) {
            return null;
        }

        $texto = trim((string) $valor);

        return $texto === '' ? null : $texto;
    }
}
