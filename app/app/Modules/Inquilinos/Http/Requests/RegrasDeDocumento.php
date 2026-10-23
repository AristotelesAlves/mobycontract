<?php

declare(strict_types=1);

namespace App\Modules\Inquilinos\Http\Requests;

use App\Modules\Inquilinos\Domain\Enums\TipoPessoa;
use App\Modules\Inquilinos\Domain\ValidadorDeDocumento;
use Closure;

/**
 * Regra de documento (CPF/CNPJ) dependente de `tipoPessoa` (R-4.4.3, R-4.4.4, R-4.4.5, R-4.4.6).
 */
trait RegrasDeDocumento
{
    protected function regraDeDocumento(): Closure
    {
        return function (string $attribute, mixed $value, Closure $falhar): void {
            $tipo = $this->tipoPessoaInformada();
            $digitos = $this->somenteDigitos($value);
            $esperado = $tipo === TipoPessoa::Pj ? 14 : 11;

            if ($digitos === '') {
                return;
            }

            if (strlen($digitos) !== $esperado) {
                $falhar($tipo === TipoPessoa::Pj ? 'CNPJ deve ter 14 dígitos.' : 'CPF deve ter 11 dígitos.');

                return;
            }

            if (! ValidadorDeDocumento::eValido($digitos, $tipo)) {
                $falhar($tipo === TipoPessoa::Pj ? 'CNPJ inválido.' : 'CPF inválido.');
            }
        };
    }

    private function tipoPessoaInformada(): TipoPessoa
    {
        $valor = $this->input('tipoPessoa');
        $texto = is_scalar($valor) ? (string) $valor : '';

        return TipoPessoa::tryFrom($texto) ?? TipoPessoa::Pf;
    }

    private function somenteDigitos(mixed $valor): string
    {
        return ValidadorDeDocumento::somenteDigitos(is_scalar($valor) ? (string) $valor : '');
    }
}
