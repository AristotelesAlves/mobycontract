<?php

declare(strict_types=1);

/**
 * Falha o build quando encontra comentário que não é PHPDoc (P7 / R-2.1).
 *
 * Palavras reservadas de tipo e nomes de domínio explicam o código; comentário
 * inline esconde intenção. O tokenizador do PHP separa `//`, `#` e `/* *​/`
 * (T_COMMENT) de docblocks `/** *​/` (T_DOC_COMMENT), o que evita falso
 * positivo dentro de string, como URL com `//`.
 */
$diretorios = ['app', 'database', 'tests', 'bin'];
$raiz = dirname(__DIR__);
$infracoes = [];

$arquivos = [];

foreach ($diretorios as $diretorio) {
    $caminho = $raiz.DIRECTORY_SEPARATOR.$diretorio;

    if (! is_dir($caminho)) {
        continue;
    }

    $iterador = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($caminho, FilesystemIterator::SKIP_DOTS)
    );

    foreach ($iterador as $arquivo) {
        if ($arquivo instanceof SplFileInfo && $arquivo->getExtension() === 'php') {
            $arquivos[] = $arquivo->getPathname();
        }
    }
}

sort($arquivos);

foreach ($arquivos as $arquivo) {
    $conteudo = file_get_contents($arquivo);

    if ($conteudo === false) {
        continue;
    }

    foreach (PhpToken::tokenize($conteudo) as $token) {
        if ($token->id !== T_COMMENT) {
            continue;
        }

        if (str_starts_with($token->text, '/*') && str_starts_with($token->text, '/**')) {
            continue;
        }

        $relativo = str_replace($raiz.DIRECTORY_SEPARATOR, '', $arquivo);
        $infracoes[] = sprintf('%s:%d  %s', $relativo, $token->line, trim($token->text));
    }
}

if ($infracoes !== []) {
    fwrite(STDERR, "Comentários não permitidos (use apenas PHPDoc):\n");

    foreach ($infracoes as $infracao) {
        fwrite(STDERR, ' - '.$infracao.PHP_EOL);
    }

    exit(1);
}

fwrite(STDOUT, 'Nenhum comentário fora de PHPDoc.'.PHP_EOL);
exit(0);
