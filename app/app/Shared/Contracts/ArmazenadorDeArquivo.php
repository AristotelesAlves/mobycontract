<?php

declare(strict_types=1);

namespace App\Shared\Contracts;

/**
 * Armazena um arquivo enviado e devolve o caminho/identificador do objeto.
 *
 * A implementação de desenvolvimento grava no disco local; em produção,
 * troca-se a implementação (S3, etc.) em um único ponto de binding.
 */
interface ArmazenadorDeArquivo
{
    public function ler(string $caminho): string;

    public function excluir(string $caminho): void;

    public function guardar(string $pasta, string $nomeOriginal, string $conteudo): string;
}
