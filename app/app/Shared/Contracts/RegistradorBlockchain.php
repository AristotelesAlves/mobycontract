<?php

declare(strict_types=1);

namespace App\Shared\Contracts;

/**
 * Registra um conteúdo determinístico em rede blockchain e devolve o hash
 * retornado pela rede (R-11.1.6). A implementação real fala com a rede;
 * a de desenvolvimento devolve um digest determinístico do conteúdo.
 */
interface RegistradorBlockchain
{
    public function registrar(string $conteudo): string;
}
