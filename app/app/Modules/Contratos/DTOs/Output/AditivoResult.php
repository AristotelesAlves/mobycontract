<?php

declare(strict_types=1);

namespace App\Modules\Contratos\DTOs\Output;

use App\Modules\Contratos\Domain\Enums\MotivoAditivo;
use App\Modules\Contratos\Domain\Enums\StatusAditivo;
use App\Modules\Contratos\Models\ContratoAditivo;
use DateTimeInterface;

final readonly class AditivoResult
{
    /**
     * @param  list<string>  $motivos
     * @param  list<array{rotulo: string, valor: string}>  $dadosExtras
     * @param  list<AditivoAssinaturaResult>  $assinaturas
     * @param  array<string, mixed>|null  $snapshot
     */
    public function __construct(
        public int $id,
        public int $contratoId,
        public string $numero,
        public string $titulo,
        public ?string $data,
        public ?string $descricao,
        public int $criadoPor,
        public string $status,
        public bool $podeAlterar,
        public bool $podeExcluir,
        public array $motivos,
        public int $modeloId,
        public string $modeloTitulo,
        public array $dadosExtras,
        public bool $uploadConfirmado,
        public ?string $anexoNome,
        public ?string $hashBlockchain,
        public ?string $dataRegistroBlockchain,
        public string $statusBlockchain,
        public array $assinaturas,
        public ?string $criadoEm,
        public ?string $atualizadoEm,
        public ?array $snapshot = null,
    ) {}

    public static function fromModel(ContratoAditivo $aditivo): self
    {
        $status = StatusAditivo::from((string) $aditivo->status);

        /** @var array{modelo: array{titulo: string}}|null $snapshot */
        $snapshot = is_array($aditivo->snapshot) ? $aditivo->snapshot : null;

        return new self(
            id: (int) $aditivo->id,
            contratoId: (int) $aditivo->contrato_id,
            numero: (string) $aditivo->numero,
            titulo: (string) $aditivo->titulo,
            data: self::dataIso($aditivo->data),
            descricao: self::textoOuNulo($aditivo->descricao),
            criadoPor: (int) $aditivo->criado_por,
            status: $status->value,
            podeAlterar: $status->permiteEdicao() && $aditivo->hash_blockchain === null,
            podeExcluir: $status->permiteExclusao(),
            motivos: self::motivos($aditivo),
            modeloId: (int) $aditivo->modelo_id,
            modeloTitulo: $snapshot !== null ? $snapshot['modelo']['titulo'] : (string) ($aditivo->modelo->titulo ?? ''),
            dadosExtras: self::dadosExtras($aditivo),
            uploadConfirmado: (bool) $aditivo->upload_confirmado,
            anexoNome: self::textoOuNulo($aditivo->anexo_nome),
            hashBlockchain: self::textoOuNulo($aditivo->hash_blockchain),
            dataRegistroBlockchain: self::instante($aditivo->data_registro_blockchain),
            statusBlockchain: (string) $aditivo->status_blockchain,
            assinaturas: self::assinaturas($aditivo),
            criadoEm: self::instante($aditivo->created_at),
            atualizadoEm: self::instante($aditivo->updated_at),
            snapshot: $snapshot,
        );
    }

    /**
     * @return list<string>
     */
    private static function motivos(ContratoAditivo $aditivo): array
    {
        $motivos = $aditivo->motivos;

        if (! is_array($motivos)) {
            return [];
        }

        $resultado = [];

        foreach ($motivos as $motivo) {
            if (is_string($motivo) && MotivoAditivo::tryFrom($motivo) instanceof MotivoAditivo) {
                $resultado[] = $motivo;
            }
        }

        return $resultado;
    }

    /**
     * @return list<array{rotulo: string, valor: string}>
     */
    private static function dadosExtras(ContratoAditivo $aditivo): array
    {
        $extras = $aditivo->dados_extras;

        if (! is_array($extras)) {
            return [];
        }

        $resultado = [];

        foreach ($extras as $extra) {
            if (! is_array($extra)) {
                continue;
            }

            $rotulo = $extra['rotulo'] ?? null;
            $valor = $extra['valor'] ?? null;

            if (is_string($rotulo) && is_scalar($valor)) {
                $resultado[] = ['rotulo' => $rotulo, 'valor' => (string) $valor];
            }
        }

        return $resultado;
    }

    /**
     * @return list<AditivoAssinaturaResult>
     */
    private static function assinaturas(ContratoAditivo $aditivo): array
    {
        if (! $aditivo->relationLoaded('assinaturas')) {
            return [];
        }

        /** @var list<AditivoAssinaturaResult> $resultado */
        $resultado = [];

        foreach ($aditivo->assinaturas as $assinatura) {
            $resultado[] = new AditivoAssinaturaResult(
                (int) $assinatura->id,
                (string) $assinatura->papel,
                (string) $assinatura->nome,
                (bool) $assinatura->assinado,
                self::instante($assinatura->data_assinatura),
            );
        }

        return $resultado;
    }

    private static function textoOuNulo(mixed $valor): ?string
    {
        return is_scalar($valor) ? (string) $valor : null;
    }

    private static function dataIso(mixed $valor): ?string
    {
        return $valor instanceof DateTimeInterface ? $valor->format('Y-m-d') : null;
    }

    private static function instante(mixed $valor): ?string
    {
        return $valor instanceof DateTimeInterface ? $valor->format(DATE_ATOM) : null;
    }
}
