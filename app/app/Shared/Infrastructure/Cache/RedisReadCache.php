<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\Cache;

use App\Shared\Contracts\ReadCache;
use Closure;
use Illuminate\Contracts\Cache\Repository as RepositorioCache;
use Illuminate\Contracts\Redis\Factory as RedisFactory;
use Psr\Log\LoggerInterface;
use Throwable;

final readonly class RedisReadCache implements ReadCache
{
    public function __construct(
        private RepositorioCache $cache,
        private RedisFactory $redis,
        private LoggerInterface $log,
    ) {}

    public function lembrar(string $chave, int $segundosParaViver, Closure $origem): mixed
    {
        $consulta = $this->buscarNoCache($chave);

        if ($consulta['tem']) {
            return $consulta['valor'];
        }

        $valor = $origem();

        $this->guardarNoCache($chave, $valor, $segundosParaViver);

        return $valor;
    }

    public function versionarPara(string $chaveDeVersao): int
    {
        try {
            $versao = $this->cache->get($chaveDeVersao);

            return is_numeric($versao) ? (int) $versao : 1;
        } catch (Throwable) {
            return 1;
        }
    }

    public function avancarVersao(string $chaveDeVersao): int
    {
        try {
            return (int) $this->cache->increment($chaveDeVersao);
        } catch (Throwable) {
            $this->log->warning('cache.versao_nao_avancou', ['chave' => $chaveDeVersao]);

            return $this->versionarPara($chaveDeVersao);
        }
    }

    public function disponivel(): bool
    {
        try {
            return $this->redis->connection('cache')->command('ping') !== false;
        } catch (Throwable) {
            return false;
        }
    }

    /**
     * @return array{tem: bool, valor: mixed}
     */
    private function buscarNoCache(string $chave): array
    {
        try {
            if (! $this->cache->has($chave)) {
                return ['tem' => false, 'valor' => null];
            }

            return ['tem' => true, 'valor' => $this->cache->get($chave)];
        } catch (Throwable) {
            $this->log->warning('cache.leitura_indisponivel', [
                'chave' => $chave,
                'requisicao_id' => request()->header('X-Request-Id'),
            ]);

            return ['tem' => false, 'valor' => null];
        }
    }

    private function guardarNoCache(string $chave, mixed $valor, int $segundosParaViver): void
    {
        try {
            $this->cache->put($chave, $valor, $segundosParaViver);
        } catch (Throwable) {
            $this->log->warning('cache.gravacao_indisponivel', [
                'chave' => $chave,
                'requisicao_id' => request()->header('X-Request-Id'),
            ]);
        }
    }
}
