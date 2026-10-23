<?php

declare(strict_types=1);

namespace App\Modules\Contratos\DTOs\Output;

use App\Modules\Contratos\Domain\Enums\StatusContrato;
use App\Modules\Contratos\Models\Contrato;
use App\Modules\Contratos\Models\ContratoEncargoFixo;
use App\Modules\Contratos\Models\ContratoUtilidade;
use DateTimeInterface;

final readonly class ContratoResult
{
    /**
     * @param  list<ContratoFiadorResult>  $fiadores
     * @param  list<ContratoAssinaturaResult>  $assinaturas
     * @param  list<array<string, mixed>>  $historico
     * @param  array<string, mixed>|null  $snapshot
     */
    public function __construct(
        public int $id,
        public string $numero,
        public string $tipoModelo,
        public string $status,
        public string $situacaoEfetiva,
        public bool $podeAlterar,
        public bool $podeExcluir,
        public int $imovelId,
        public string $imovelTitulo,
        public ?string $imovelEndereco,
        public ?string $imovelCidade,
        public ?string $imovelMatricula,
        public int $inquilinoId,
        public string $inquilinoNome,
        public int $criadoPor,
        public int $modeloId,
        public string $modeloTitulo,
        public int $modeloVersao,
        public float $valorMensal,
        public float $descontoMensal,
        public float $multaPorAtraso,
        public float $multaRescisao,
        public string $garantia,
        public ?float $valorCaucao,
        public array $fiadores,
        public string $destinacao,
        public string $prazoVigencia,
        public ?string $dataInicio,
        public ?string $dataFim,
        public string $indiceReajuste,
        public string $responsabilidadeIptu,
        public ?float $valorEstimadoIptu,
        public string $responsabilidadeCondominio,
        public ?float $valorEstimadoCondominio,
        public string $consumoAgua,
        public string $consumoLuz,
        public string $consumoGas,
        public string $consumoInternet,
        public ?string $anexoContratoNome,
        public ?string $observacoes,
        public ?string $motivoCancelamento,
        public bool $documentoFisicoConfirmado,
        public int $diaVencimentoParcela,
        public array $assinaturas,
        public ?string $hashBlockchain,
        public ?string $dataRegistroBlockchain,
        public string $statusBlockchain,
        public ?string $criadoEm,
        public ?string $atualizadoEm,
        public ?array $snapshot = null,
        public ?float $imovelArea = null,
        public ?int $imovelQuartos = null,
        public ?int $imovelBanheiros = null,
        public ?int $imovelVagas = null,
        public array $historico = [],
        public ?string $anexoContrato = null,
        public ?string $comprovanteGarantia = null,
    ) {}

    public static function fromModel(Contrato $contrato, string $hoje): self
    {
        $status = StatusContrato::from((string) $contrato->status);
        /** @var array{imovel: array{titulo: string, endereco?: ?string, cidade?: ?string, matricula?: ?string}, inquilino: array{nome: string}, modelo: array{titulo: string, versao: int}}|null $snapshot */
        $snapshot = is_array($contrato->snapshot) ? $contrato->snapshot : null;

        return new self(
            id: (int) $contrato->id,
            numero: (string) $contrato->numero,
            tipoModelo: (string) $contrato->tipo_modelo,
            status: $status->value,
            situacaoEfetiva: $status->situacaoEfetivaEm(self::dataIso($contrato->data_fim), $hoje)->value,
            podeAlterar: $status->permiteEdicao() && $contrato->hash_blockchain === null,
            podeExcluir: $status->permiteExclusao(),
            imovelId: (int) $contrato->imovel_id,
            imovelTitulo: $snapshot !== null ? ($snapshot['imovel']['titulo'] ?? '') : (string) ($contrato->imovel->titulo ?? ''),
            imovelEndereco: $snapshot !== null ? ($snapshot['imovel']['endereco'] ?? null) : self::textoOuNulo($contrato->imovel?->endereco),
            imovelCidade: $snapshot !== null ? ($snapshot['imovel']['cidade'] ?? null) : self::textoOuNulo($contrato->imovel?->cidade),
            imovelMatricula: $snapshot !== null ? ($snapshot['imovel']['matricula'] ?? null) : self::textoOuNulo($contrato->imovel?->matricula),
            inquilinoId: (int) $contrato->inquilino_id,
            inquilinoNome: $snapshot !== null ? ($snapshot['inquilino']['nome'] ?? '') : (string) ($contrato->inquilino->nome ?? ''),
            criadoPor: (int) $contrato->criado_por,
            modeloId: (int) $contrato->modelo_id,
            modeloTitulo: $snapshot !== null ? ($snapshot['modelo']['titulo'] ?? '') : (string) ($contrato->modelo->titulo ?? ''),
            modeloVersao: $snapshot !== null ? ($snapshot['modelo']['versao'] ?? 0) : (int) ($contrato->modelo->versao ?? 0),
            valorMensal: (float) $contrato->valor_mensal,
            descontoMensal: (float) $contrato->desconto_mensal,
            multaPorAtraso: (float) $contrato->multa_por_atraso,
            multaRescisao: (float) $contrato->multa_rescisao,
            garantia: (string) $contrato->garantia,
            valorCaucao: self::decimalOuNulo($contrato->valor_caucao),
            fiadores: self::fiadores($contrato),
            destinacao: (string) $contrato->destinacao,
            prazoVigencia: (string) $contrato->prazo_vigencia,
            dataInicio: self::dataIso($contrato->data_inicio),
            dataFim: self::dataIso($contrato->data_fim),
            indiceReajuste: (string) $contrato->indice_reajuste,
            responsabilidadeIptu: self::responsabilidadeIptu($contrato),
            valorEstimadoIptu: self::valorIptu($contrato),
            responsabilidadeCondominio: self::responsabilidadeCondominio($contrato),
            valorEstimadoCondominio: self::valorCondominio($contrato),
            consumoAgua: self::consumo($contrato, 'agua', 'agua_inclusa'),
            consumoLuz: self::consumo($contrato, 'luz', 'energia_inclusa'),
            consumoGas: self::consumo($contrato, 'gas', 'gas_incluso'),
            consumoInternet: self::consumo($contrato, 'internet', 'internet_inclusa'),
            anexoContratoNome: self::textoOuNulo($contrato->anexo_contrato_nome),
            anexoContrato: self::textoOuNulo($contrato->anexo_contrato),
            observacoes: self::textoOuNulo($contrato->observacoes),
            motivoCancelamento: self::textoOuNulo($contrato->motivo_cancelamento),
            documentoFisicoConfirmado: (bool) $contrato->documento_fisico_confirmado,
            diaVencimentoParcela: (int) $contrato->dia_vencimento_parcela,
            assinaturas: self::assinaturas($contrato),
            hashBlockchain: self::textoOuNulo($contrato->hash_blockchain),
            dataRegistroBlockchain: self::instante($contrato->data_registro_blockchain),
            statusBlockchain: (string) $contrato->status_blockchain,
            criadoEm: self::instante($contrato->created_at),
            atualizadoEm: self::instante($contrato->updated_at),
            snapshot: $snapshot,
            historico: $contrato->relationLoaded('historicos') ? array_values($contrato->historicos->map(static fn ($evento): array => [
                'id' => (string) $evento->id,
                'data' => self::instante($evento->data),
                'evento' => $evento->evento,
                'detalhe' => $evento->detalhe ?? '',
            ])->all()) : [],
            imovelArea: self::decimalOuNulo($snapshot !== null ? ($snapshot['imovel']['area'] ?? null) : $contrato->imovel?->area),
            imovelQuartos: self::inteiroOuNulo($snapshot !== null ? ($snapshot['imovel']['quartos'] ?? null) : $contrato->imovel?->quartos),
            imovelBanheiros: self::inteiroOuNulo($snapshot !== null ? ($snapshot['imovel']['banheiros'] ?? null) : $contrato->imovel?->banheiros),
            imovelVagas: self::inteiroOuNulo($snapshot !== null ? ($snapshot['imovel']['vagasGaragem'] ?? null) : $contrato->imovel?->vagas_garagem),
            comprovanteGarantia: self::textoOuNulo($contrato->comprovante_garantia),
        );
    }

    /**
     * @return list<ContratoFiadorResult>
     */
    private static function fiadores(Contrato $contrato): array
    {
        if (! $contrato->relationLoaded('fiadores')) {
            return [];
        }

        /** @var list<ContratoFiadorResult> $resultado */
        $resultado = [];

        foreach ($contrato->fiadores as $fiador) {
            $resultado[] = new ContratoFiadorResult(
                (int) $fiador->id,
                (string) $fiador->nome,
                (string) $fiador->cpf,
                (string) $fiador->telefone,
            );
        }

        return $resultado;
    }

    /**
     * @return list<ContratoAssinaturaResult>
     */
    private static function assinaturas(Contrato $contrato): array
    {
        if (! $contrato->relationLoaded('assinaturas')) {
            return [];
        }

        /** @var list<ContratoAssinaturaResult> $resultado */
        $resultado = [];

        foreach ($contrato->assinaturas as $assinatura) {
            $resultado[] = new ContratoAssinaturaResult(
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

    private static function inteiroOuNulo(mixed $valor): ?int
    {
        return is_numeric($valor) ? (int) $valor : null;
    }

    private static function decimalOuNulo(mixed $valor): ?float
    {
        return is_numeric($valor) ? (float) $valor : null;
    }

    /**
     * Encargo fixo do contrato pelo tipo ("iptu" ou "condominio"), com fallback
     * na derivação documentada (R-12.8.5.3) quando a tabela não estiver carregada.
     */
    private static function encargoDoTipo(Contrato $contrato, string $tipo): ?ContratoEncargoFixo
    {
        if (! $contrato->relationLoaded('encargosFixos')) {
            return null;
        }

        foreach ($contrato->encargosFixos as $encargo) {
            if ((string) $encargo->tipo === $tipo && (bool) $encargo->ativo) {
                return $encargo;
            }
        }

        return null;
    }

    private static function utilidadeDoTipo(Contrato $contrato, string $tipo): ?ContratoUtilidade
    {
        if (! $contrato->relationLoaded('utilidades')) {
            return null;
        }

        foreach ($contrato->utilidades as $utilidade) {
            if ((string) $utilidade->tipo === $tipo && (bool) $utilidade->ativo) {
                return $utilidade;
            }
        }

        return null;
    }

    private static function responsabilidadeIptu(Contrato $contrato): string
    {
        $encargo = self::encargoDoTipo($contrato, 'iptu');

        if ($encargo instanceof ContratoEncargoFixo) {
            return self::vocabularioDeDespesa((string) $encargo->responsabilidade);
        }

        $valorIptu = self::decimalOuNulo($contrato->imovel?->valor_iptu);

        return $valorIptu !== null && $valorIptu > 0 ? 'Inquilino' : 'Locador';
    }

    private static function responsabilidadeCondominio(Contrato $contrato): string
    {
        $encargo = self::encargoDoTipo($contrato, 'condominio');

        if ($encargo instanceof ContratoEncargoFixo) {
            return self::vocabularioDeDespesa((string) $encargo->responsabilidade);
        }

        $valorCondominio = self::decimalOuNulo($contrato->imovel?->valor_condominio);

        return $valorCondominio !== null && $valorCondominio > 0 ? 'Inquilino' : 'Locador';
    }

    private static function valorIptu(Contrato $contrato): ?float
    {
        $encargo = self::encargoDoTipo($contrato, 'iptu');

        if (($encargo instanceof ContratoEncargoFixo) && self::decimalOuNulo($encargo->valor_estimado) !== null) {
            return (float) $encargo->valor_estimado;
        }

        return self::decimalOuNulo($contrato->imovel?->valor_iptu);
    }

    private static function valorCondominio(Contrato $contrato): ?float
    {
        $encargo = self::encargoDoTipo($contrato, 'condominio');

        if (($encargo instanceof ContratoEncargoFixo) && self::decimalOuNulo($encargo->valor_estimado) !== null) {
            return (float) $encargo->valor_estimado;
        }

        return self::decimalOuNulo($contrato->imovel?->valor_condominio);
    }

    /**
     * Consumo de utilidade (aguas, luz, gas, internet) com fallback na regra
     * documentada (R-12.8.5.5 a R-12.8.5.8): inclusa no condominio quando a flag
     * do imovel esta ativa; caso contrario, responsabilidado do locatario.
     */
    private static function consumo(Contrato $contrato, string $tipo, string $chaveImovel): string
    {
        $utilidade = self::utilidadeDoTipo($contrato, $tipo);

        if ($utilidade instanceof ContratoUtilidade) {
            return self::vocabularioDeConsumo((string) $utilidade->responsabilidade);
        }

        $inclusa = (bool) $contrato->imovel?->{$chaveImovel};

        return $inclusa ? 'Inclusa no Condomínio' : 'Inquilino';
    }

    private static function vocabularioDeDespesa(string $responsabilidade): string
    {
        return match ($responsabilidade) {
            'Locador' => 'Locador',
            'N/A' => 'N/A',
            default => 'Inquilino',
        };
    }

    private static function vocabularioDeConsumo(string $responsabilidade): string
    {
        return match ($responsabilidade) {
            'Locador' => 'Locador',
            'Compartilhado', 'Inclusa no Condomínio' => 'Inclusa no Condomínio',
            default => 'Inquilino',
        };
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
