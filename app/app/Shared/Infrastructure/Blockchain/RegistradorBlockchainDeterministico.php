<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\Blockchain;

use App\Shared\Application\Exceptions\ServicoIndisponivelException;
use App\Shared\Contracts\RegistradorBlockchain;

/** Substituto determinístico restrito aos testes; não representa uma transação EVM. */
final readonly class RegistradorBlockchainDeterministico implements RegistradorBlockchain
{
    public function registrar(string $conteudo): string
    {
        if (! app()->environment('testing')) {
            throw new ServicoIndisponivelException('Configure um registrador blockchain real. O substituto determinístico só está disponível em testes.');
        }

        return '0x'.hash('sha256', $conteudo);
    }
}
