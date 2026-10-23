<?php

declare(strict_types=1);

namespace App\Modules\Contratos\DTOs\Input;

use App\Modules\Contratos\Domain\Enums\IndiceReajuste;
use App\Modules\Contratos\Domain\Enums\PrazoVigencia;
use App\Modules\Inquilinos\DTOs\Input\CriarInquilinoData;
use App\Shared\Application\Support\DadosDeEntrada;

final readonly class CriarContratoData
{
    /**
     * @param  list<FiadorData>  $fiadores
     */
    public function __construct(
        public int $imovelId,
        public int $modeloId,
        public PrazoVigencia $prazoVigencia,
        public string $dataInicio,
        public ?string $dataFim,
        public IndiceReajuste $indiceReajuste,
        public string $modoInquilino,
        public ?int $inquilinoId,
        public ?CriarInquilinoData $inquilino,
        public ?float $valorCaucao,
        public array $fiadores,
        public string $nomeLocador,
        public float $descontoMensal,
        public float $multaPorAtraso,
        public ?float $multaRescisao,
        public ?string $observacoes,
        public int $diaVencimentoParcela,
        public ?string $comprovanteGarantia = null,
        public ?string $comprovanteGarantiaNome = null,
    ) {}

    /**
     * @param  array<string, mixed>  $validated
     */
    public static function fromValidated(array $validated): self
    {
        $inquilino = $validated['inquilino'] ?? null;
        $inquilinoData = null;

        if (is_array($inquilino)) {
            /** @var array<string, mixed> $inquilino */
            $inquilinoData = CriarInquilinoData::fromValidated($inquilino);
        }

        return new self(
            imovelId: DadosDeEntrada::inteiro($validated, 'imovelId'),
            modeloId: DadosDeEntrada::inteiro($validated, 'modeloId'),
            prazoVigencia: PrazoVigencia::from(DadosDeEntrada::texto($validated, 'prazoVigencia')),
            dataInicio: DadosDeEntrada::texto($validated, 'dataInicio'),
            dataFim: DadosDeEntrada::textoOpcional($validated, 'dataFim'),
            indiceReajuste: IndiceReajuste::from(DadosDeEntrada::texto($validated, 'indiceReajuste')),
            modoInquilino: DadosDeEntrada::texto($validated, 'modoInquilino'),
            inquilinoId: DadosDeEntrada::existe($validated, 'inquilinoId')
                ? DadosDeEntrada::inteiro($validated, 'inquilinoId')
                : null,
            inquilino: $inquilinoData,
            valorCaucao: DadosDeEntrada::decimalOpcional($validated, 'valorCaucao'),
            fiadores: self::fiadores($validated),
            nomeLocador: DadosDeEntrada::texto($validated, 'nomeLocador'),
            descontoMensal: DadosDeEntrada::decimal($validated, 'descontoMensal'),
            multaPorAtraso: DadosDeEntrada::decimal($validated, 'multaPorAtraso'),
            multaRescisao: DadosDeEntrada::decimalOpcional($validated, 'multaRescisao'),
            observacoes: DadosDeEntrada::textoOpcional($validated, 'observacoes'),
            diaVencimentoParcela: DadosDeEntrada::inteiro($validated, 'diaVencimentoParcela', 5),
            comprovanteGarantia: DadosDeEntrada::textoOpcional($validated, 'comprovanteGarantiaCaminho'),
            comprovanteGarantiaNome: DadosDeEntrada::textoOpcional($validated, 'comprovanteGarantiaNome'),
        );
    }

    /**
     * @param  array<string, mixed>  $validated
     * @return list<FiadorData>
     */
    private static function fiadores(array $validated): array
    {
        $fiadores = $validated['fiadores'] ?? null;

        if (! is_array($fiadores)) {
            return [];
        }

        $resultado = [];

        foreach ($fiadores as $fiador) {
            if (is_array($fiador)) {
                /** @var array<string, mixed> $fiador */
                $resultado[] = FiadorData::fromArray($fiador);
            }
        }

        return $resultado;
    }
}
