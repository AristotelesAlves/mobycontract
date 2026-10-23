<?php

declare(strict_types=1);

namespace App\Modules\Contratos\DTOs\Input;

use App\Modules\Contratos\Domain\Enums\MotivoAditivo;
use App\Shared\Application\Support\DadosDeEntrada;

final readonly class AlterarAditivoData
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

        if (DadosDeEntrada::existe($this->campos, 'titulo')) {
            $atributos['titulo'] = DadosDeEntrada::texto($this->campos, 'titulo');
        }

        if (DadosDeEntrada::existe($this->campos, 'descricao')) {
            $atributos['descricao'] = DadosDeEntrada::textoOpcional($this->campos, 'descricao');
        }

        return $atributos;
    }

    public function motivosInformados(): bool
    {
        return DadosDeEntrada::existe($this->campos, 'motivos');
    }

    /**
     * @return list<MotivoAditivo>
     */
    public function motivos(): array
    {
        $informados = $this->campos['motivos'] ?? null;

        if (! is_array($informados)) {
            return [];
        }

        $resultado = [];

        foreach ($informados as $motivo) {
            if (is_string($motivo) && MotivoAditivo::tryFrom($motivo) instanceof MotivoAditivo) {
                $resultado[] = MotivoAditivo::from($motivo);
            }
        }

        return $resultado;
    }

    /**
     * @return array<string, mixed>
     */
    public function camposDosMotivos(): array
    {
        return $this->campos;
    }
}
