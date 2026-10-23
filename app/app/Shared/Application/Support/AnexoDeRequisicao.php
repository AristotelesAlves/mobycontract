<?php

declare(strict_types=1);

namespace App\Shared\Application\Support;

use App\Shared\Application\Exceptions\RegraNegocioException;
use App\Shared\Contracts\ArmazenadorDeArquivo;
use Illuminate\Http\UploadedFile;

/**
 * Extrai um arquivo enviado da requisição e delega o armazenamento à
 * abstração {@see ArmazenadorDeArquivo}, mantendo o controller livre de
 * detalhes de armazenamento.
 */
final class AnexoDeRequisicao
{
    /**
     * @return array{anexo: string|null, nome: string|null}
     */
    public static function guardar(ArmazenadorDeArquivo $armazenador, string $pasta, mixed $arquivo): array
    {
        if (! $arquivo instanceof UploadedFile) {
            return ['anexo' => null, 'nome' => null];
        }

        $conteudo = $arquivo->get();

        if ($conteudo === false) {
            throw new RegraNegocioException('Não foi possível ler o arquivo enviado.');
        }

        $nome = $arquivo->getClientOriginalName();

        return [
            'anexo' => $armazenador->guardar($pasta, $nome, $conteudo),
            'nome' => $nome,
        ];
    }
}
