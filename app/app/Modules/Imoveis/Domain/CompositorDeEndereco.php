<?php

declare(strict_types=1);

namespace App\Modules\Imoveis\Domain;

/**
 * Compõe o `endereco` do imóvel (R-3.1.1.2):
 * `logradouro, numero[ - complemento], bairro`.
 */
final class CompositorDeEndereco
{
    public static function compor(
        string $logradouro,
        string $numero,
        ?string $complemento,
        ?string $bairro,
    ): string {
        $endereco = $logradouro.', '.$numero;

        if ($complemento !== null && $complemento !== '') {
            $endereco .= ' - '.$complemento;
        }

        if ($bairro !== null && $bairro !== '') {
            $endereco .= ', '.$bairro;
        }

        return $endereco;
    }
}
