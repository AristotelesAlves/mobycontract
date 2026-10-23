<?php

declare(strict_types=1);

namespace App\Modules\Contratos\Services\Fluxos;

use App\Modules\Contratos\Domain\Enums\MotivoAditivo;

/**
 * Deriva `dadosExtras` do aditivo a partir dos motivos escolhidos e dos
 * campos informados (R-3.6.2). Campo vazio ou motivo não escolhido é ignorado.
 */
final readonly class DerivadorDeDadosExtras
{
    /**
     * @param  list<MotivoAditivo>  $motivos
     * @param  array<string, mixed>  $campos
     * @return list<array{rotulo: string, valor: string}>
     */
    public function derivar(array $motivos, array $campos): array
    {
        $resultado = [];

        foreach ($motivos as $motivo) {
            $valor = $campos[$motivo->campo()] ?? null;

            if ($valor === null || $valor === '') {
                continue;
            }

            $resultado[] = [
                'rotulo' => $motivo->rotulo(),
                'valor' => $this->formatar($motivo, $valor),
            ];
        }

        return $resultado;
    }

    private function formatar(MotivoAditivo $motivo, mixed $valor): string
    {
        if ($motivo->ehMonetario() && is_numeric($valor)) {
            return number_format((float) $valor, 2, ',', '.');
        }

        return is_scalar($valor) ? (string) $valor : '';
    }
}
