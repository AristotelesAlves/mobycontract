<?php

declare(strict_types=1);

namespace App\Modules\Contratos\Domain\Enums;

/**
 * Máquina de estados do contrato (R-5.1).
 */
enum StatusContrato: string
{
    case Rascunho = 'Rascunho';
    case Minuta = 'Minuta';
    case Pendente = 'Pendente';
    case Validado = 'Validado';
    case Vigente = 'Vigente';
    case Encerrado = 'Encerrado';
    case Cancelado = 'Cancelado';

    /**
     * @return list<self>
     */
    public function transicoesPermitidas(): array
    {
        return match ($this) {
            self::Rascunho => [self::Minuta, self::Pendente, self::Cancelado],
            self::Minuta => [self::Pendente, self::Rascunho, self::Cancelado],
            self::Pendente => [self::Minuta, self::Validado, self::Cancelado],
            self::Validado => [self::Vigente, self::Encerrado, self::Cancelado],
            self::Vigente => [self::Encerrado, self::Cancelado],
            self::Encerrado, self::Cancelado => [],
        };
    }

    public function podeTransicionarPara(self $destino): bool
    {
        return in_array($destino, $this->transicoesPermitidas(), true);
    }

    public function permiteEdicao(): bool
    {
        return $this === self::Rascunho;
    }

    public function permiteExclusao(): bool
    {
        return $this === self::Rascunho;
    }

    public function estaAtivo(): bool
    {
        return $this === self::Validado || $this === self::Vigente;
    }

    public function estaFechado(): bool
    {
        return $this === self::Encerrado || $this === self::Cancelado;
    }

    public function situacaoEfetivaEm(?string $dataFim, string $dataReferencia): self
    {
        return $this === self::Vigente && $dataFim !== null && $dataFim < $dataReferencia
            ? self::Encerrado
            : $this;
    }
}
