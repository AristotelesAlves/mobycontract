<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\Armazenamento;

use App\Shared\Application\Exceptions\RegraNegocioException;
use App\Shared\Contracts\ArmazenadorDeArquivo;
use Illuminate\Support\Str;

/**
 * Implementação de desenvolvimento: grava o arquivo no disco local.
 *
 * Substituível pela implementação de produção (S3, object storage) sem
 * alterar as camadas que chamam {@see ArmazenadorDeArquivo}.
 */
final readonly class ArmazenadorLocal implements ArmazenadorDeArquivo
{
    public function __construct(
        private string $diretorioBase,
    ) {}

    public function guardar(string $pasta, string $nomeOriginal, string $conteudo): string
    {
        $this->validarCaminho($pasta);
        $extensao = strtolower(pathinfo($nomeOriginal, PATHINFO_EXTENSION));
        $extensao = preg_match('/^[a-z0-9]{1,10}$/', $extensao) === 1 ? '.'.$extensao : '';
        $relativo = $pasta.'/'.Str::uuid()->toString().$extensao;
        $absoluto = rtrim($this->diretorioBase, '/').'/'.$relativo;

        $diretorio = dirname($absoluto);

        if (! is_dir($diretorio) && ! mkdir($diretorio, 0o700, true) && ! is_dir($diretorio)) {
            throw new RegraNegocioException('Não foi possível armazenar o arquivo enviado.');
        }

        $baseReal = realpath($this->diretorioBase);
        $diretorioReal = realpath($diretorio);
        if ($baseReal === false || $diretorioReal === false || ! str_starts_with($diretorioReal.'/', $baseReal.'/')) {
            throw new RegraNegocioException('Diretório de armazenamento inválido.');
        }

        if (file_put_contents($absoluto, $conteudo, LOCK_EX) === false) {
            throw new RegraNegocioException('Não foi possível armazenar o arquivo enviado.');
        }

        chmod($absoluto, 0o600);

        return $relativo;
    }

    public function ler(string $caminho): string
    {
        $absoluto = $this->caminhoExistente($caminho);
        $conteudo = file_get_contents($absoluto);
        if ($conteudo === false) {
            throw new RegraNegocioException('Não foi possível ler o arquivo.');
        }

        return $conteudo;
    }

    public function excluir(string $caminho): void
    {
        $absoluto = $this->caminhoExistente($caminho);
        if (! unlink($absoluto)) {
            throw new RegraNegocioException('Não foi possível excluir o arquivo.');
        }
    }

    private function validarCaminho(string $caminho): void
    {
        if (preg_match('~^[a-zA-Z0-9_-]+(?:/[a-zA-Z0-9_.-]+)*$~D', $caminho) !== 1
            || in_array('..', explode('/', $caminho), true)
            || in_array('.', explode('/', $caminho), true)) {
            throw new RegraNegocioException('Identificador de arquivo inválido.');
        }
    }

    private function caminhoExistente(string $caminho): string
    {
        $this->validarCaminho($caminho);
        $base = realpath($this->diretorioBase);
        $absoluto = realpath($this->diretorioBase.'/'.$caminho);
        if ($base === false || $absoluto === false || ! is_file($absoluto)
            || ! str_starts_with($absoluto, $base.DIRECTORY_SEPARATOR)) {
            abort(404, 'Arquivo não encontrado.');
        }

        return $absoluto;
    }
}
