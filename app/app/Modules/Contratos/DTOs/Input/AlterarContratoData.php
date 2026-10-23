<?php

declare(strict_types=1);

namespace App\Modules\Contratos\DTOs\Input;

use App\Shared\Application\Support\DadosDeEntrada;

final readonly class AlterarContratoData
{
    /**
     * @param  array<string, mixed>  $campos
     */
    private function __construct(
        public int $id,
        private array $campos,
    ) {}

    /**
     * @param  array<string, mixed>  $validated
     */
    public static function fromValidated(array $validated): self
    {
        $id = DadosDeEntrada::inteiro($validated, 'id');

        unset($validated['id']);

        return new self($id, $validated);
    }

    /**
     * @return array<string, mixed>
     */
    public function paraAtributos(): array
    {
        $atributos = [];

        if (DadosDeEntrada::existe($this->campos, 'descontoMensal')) {
            $atributos['desconto_mensal'] = DadosDeEntrada::decimal($this->campos, 'descontoMensal');
        }

        if (DadosDeEntrada::existe($this->campos, 'multaPorAtraso')) {
            $atributos['multa_por_atraso'] = DadosDeEntrada::decimal($this->campos, 'multaPorAtraso');
        }

        $multa = DadosDeEntrada::existe($this->campos, 'multaRescisao')
            ? DadosDeEntrada::decimalOpcional($this->campos, 'multaRescisao')
            : null;

        if ($multa !== null) {
            $atributos['multa_rescisao'] = $multa;
        }

        if (DadosDeEntrada::existe($this->campos, 'valorCaucao')) {
            $atributos['valor_caucao'] = DadosDeEntrada::decimalOpcional($this->campos, 'valorCaucao');
        }

        if (DadosDeEntrada::existe($this->campos, 'observacoes')) {
            $atributos['observacoes'] = DadosDeEntrada::textoOpcional($this->campos, 'observacoes');
        }

        return $atributos;
    }

    /**
     * @return list<FiadorData>
     */
    public function fiadores(): array
    {
        $fiadores = $this->campos['fiadores'] ?? null;

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

    public function fiadoresInformados(): bool
    {
        return DadosDeEntrada::existe($this->campos, 'fiadores');
    }
}
