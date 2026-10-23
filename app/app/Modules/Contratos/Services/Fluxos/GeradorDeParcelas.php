<?php

declare(strict_types=1);

namespace App\Modules\Contratos\Services\Fluxos;

use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Config;

/**
 * Gera as parcelas de um contrato (R-6.5). Função determinística: recebe datas por parâmetro.
 */
final class GeradorDeParcelas
{
    /**
     * @var list<string>
     */
    private const array MESES = [
        'Jan', 'Fev', 'Mar', 'Abr', 'Mai', 'Jun',
        'Jul', 'Ago', 'Set', 'Out', 'Nov', 'Dez',
    ];

    /**
     * @return list<array<string, mixed>>
     */
    public function gerar(
        string $dataInicio,
        ?string $dataFim,
        bool $indeterminado,
        float $valorMensal,
        float $descontoMensal,
        int $diaVencimento,
    ): array {
        $inicio = CarbonImmutable::parse($dataInicio)->startOfMonth();
        $limite = $this->ultimoMes($inicio, $dataFim, $indeterminado);

        $parcelas = [];
        $primeira = true;

        for ($mes = $inicio; $mes->lessThanOrEqualTo($limite); $mes = $mes->addMonth()) {
            $parcelas[] = [
                'referencia' => self::MESES[$mes->month - 1].'/'.$mes->year,
                'vencimento' => $this->vencimento($mes, $diaVencimento),
                'valor' => $primeira ? max(0.0, $valorMensal - $descontoMensal) : $valorMensal,
                'juros' => 0,
                'taxas_adicionais' => 0,
                'valor_pago' => 0,
                'status' => 'Pendente',
            ];

            $primeira = false;
        }

        return $parcelas;
    }

    private function ultimoMes(CarbonImmutable $inicio, ?string $dataFim, bool $indeterminado): CarbonImmutable
    {
        if (! $indeterminado && $dataFim !== null) {
            return CarbonImmutable::parse($dataFim)->startOfMonth();
        }

        return $inicio->addMonths($this->horizonteMeses() - 1);
    }

    private function vencimento(CarbonImmutable $mes, int $dia): string
    {
        $ultimoDia = (int) $mes->endOfMonth()->format('j');
        $diaEfetivo = max(1, min($dia, $ultimoDia));

        return $mes->setDay($diaEfetivo)->format('Y-m-d');
    }

    private function horizonteMeses(): int
    {
        return Config::integer('mobycontract.contratos.prazo_vigencia_meses', 12);
    }
}
