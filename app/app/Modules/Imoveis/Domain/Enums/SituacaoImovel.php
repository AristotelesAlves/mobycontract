<?php

declare(strict_types=1);

namespace App\Modules\Imoveis\Domain\Enums;

/**
 * Máquina de estados da situação do imóvel (R-5.6).
 *
 * `Disponível` e `Alugado` são derivados dos contratos e nunca digitados
 * (R-5.6.3). Apenas `Em negociação` e `Vendido` são controlados pelo usuário.
 */
enum SituacaoImovel: string
{
    case Disponivel = 'Disponível';
    case Alugado = 'Alugado';
    case Vendido = 'Vendido';
    case EmNegociacao = 'Em negociação';

    /**
     * @return list<self>
     */
    public function transicoesPermitidas(): array
    {
        return match ($this) {
            self::Disponivel => [self::EmNegociacao, self::Alugado, self::Vendido],
            self::EmNegociacao => [self::Disponivel, self::Alugado, self::Vendido],
            self::Alugado => [self::Disponivel, self::Vendido],
            self::Vendido => [],
        };
    }

    /**
     * R-5.6.1: `Vendido` é irreversível.
     */
    public function podeTransicionarPara(self $destino): bool
    {
        return in_array($destino, $this->transicoesPermitidas(), true);
    }

    /**
     * R-5.6.3: só `Em negociação` e `Vendido` são controlados pelo usuário.
     */
    public function controladaPeloUsuario(): bool
    {
        return $this === self::Vendido || $this === self::EmNegociacao;
    }

    public function estaAlugado(): bool
    {
        return $this === self::Alugado;
    }
}
