<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\Cache;

use App\Shared\Contracts\CacheKeyFactory;
use App\Shared\Contracts\ReadCache;

final readonly class RedisCacheKeyFactory implements CacheKeyFactory
{
    public function __construct(
        private ReadCache $cache,
        private string $prefixoAplicacao,
    ) {}

    public function paraConsulta(
        string $modulo,
        string $recurso,
        string $operacao,
        array $criterios = [],
    ): string {
        ksort($criterios);

        $partes = [];

        foreach ($criterios as $nome => $valor) {
            $partes[] = $nome.'='.$this->normalizar($valor);
        }

        $assinatura = $partes === []
            ? 'sem-criterio'
            : substr(hash('sha256', implode('|', $partes)), 0, 16);

        return sprintf(
            '%s:%s:%s:v%d:%s:%s',
            $this->prefixoAplicacao,
            $modulo,
            $recurso,
            $this->cache->versionarPara($this->paraVersao($modulo, $recurso)),
            $operacao,
            $assinatura,
        );
    }

    public function paraVersao(string $modulo, string $recurso): string
    {
        return sprintf('%s:versao:%s:%s', $this->prefixoAplicacao, $modulo, $recurso);
    }

    public function prefixosDoRecurso(string $modulo, string $recurso): array
    {
        return [sprintf('%s:%s:%s:', $this->prefixoAplicacao, $modulo, $recurso)];
    }

    private function normalizar(int|float|bool|string|null $valor): string
    {
        return match (true) {
            $valor === null => 'nulo',
            is_bool($valor) => $valor ? 'sim' : 'nao',
            is_float($valor) => rtrim(rtrim(number_format($valor, 4, '.', ''), '0'), '.'),
            default => (string) $valor,
        };
    }
}
