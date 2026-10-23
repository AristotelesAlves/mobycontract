<?php

declare(strict_types=1);

namespace App\Providers;

use App\Shared\Contracts\ArmazenadorDeArquivo;
use App\Shared\Contracts\CacheKeyFactory;
use App\Shared\Contracts\DomainEventDispatcher;
use App\Shared\Contracts\ExternalHttpClient;
use App\Shared\Contracts\ReadCache;
use App\Shared\Contracts\RegistradorBlockchain;
use App\Shared\Contracts\RegistradorDeErrosApi;
use App\Shared\Contracts\Relogio;
use App\Shared\Contracts\SequenciaNumericaInterface;
use App\Shared\Contracts\TokenIssuer;
use App\Shared\Contracts\TransactionManager;
use App\Shared\Contracts\VerificadorDeSenha;
use App\Shared\Database\EloquentTransactionManager;
use App\Shared\Database\SequenciaNumerica;
use App\Shared\Infrastructure\Armazenamento\ArmazenadorLocal;
use App\Shared\Infrastructure\Auth\PassportTokenIssuer;
use App\Shared\Infrastructure\Blockchain\MobyLedgerClient;
use App\Shared\Infrastructure\Blockchain\RegistradorBlockchainDeterministico;
use App\Shared\Infrastructure\Cache\RedisCacheKeyFactory;
use App\Shared\Infrastructure\Cache\RedisReadCache;
use App\Shared\Infrastructure\Events\EventoDeDominioDispatcher;
use App\Shared\Infrastructure\Http\LaravelExternalHttpClient;
use App\Shared\Infrastructure\Logging\RegistradorDeErrosComLog;
use App\Shared\Infrastructure\Security\BcryptSenhaHasher;
use App\Shared\Infrastructure\Tempo\RelogioSistema;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Contracts\Cache\Repository as RepositorioCache;
use Illuminate\Contracts\Container\Container;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Redis\Factory as RedisFactory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Laravel\Passport\Passport;
use Psr\Log\LoggerInterface;

final class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(TransactionManager::class, EloquentTransactionManager::class);
        $this->app->singleton(SequenciaNumericaInterface::class, SequenciaNumerica::class);
        $this->app->singleton(Relogio::class, RelogioSistema::class);
        $this->app->singleton(TokenIssuer::class, PassportTokenIssuer::class);
        $this->app->singleton(ExternalHttpClient::class, LaravelExternalHttpClient::class);
        $this->app->singleton(RegistradorBlockchain::class, static function (Container $aplicacao): RegistradorBlockchain {
            $registrador = Config::string(
                'mobycontract.blockchain.registrador',
                RegistradorBlockchainDeterministico::class,
            );

            if ($registrador === MobyLedgerClient::class) {
                return $aplicacao->make(MobyLedgerClient::class, [
                    'registryUrl' => Config::string('mobycontract.blockchain.registry_url', 'http://localhost:4000'),
                    'apiKey' => Config::string('mobycontract.blockchain.api_key', ''),
                ]);
            }

            /** @var RegistradorBlockchain $instancia */
            $instancia = $aplicacao->make($registrador);

            return $instancia;
        });
        $this->app->singleton(ArmazenadorDeArquivo::class, static fn (): ArmazenadorLocal => new ArmazenadorLocal(
            Config::string('mobycontract.arquivos.diretorio_base', storage_path('app/uploads')),
        ));
        $this->app->singleton(VerificadorDeSenha::class, BcryptSenhaHasher::class);
        $this->app->singleton(RegistradorDeErrosApi::class, RegistradorDeErrosComLog::class);
        $this->app->singleton(DomainEventDispatcher::class, EventoDeDominioDispatcher::class);

        $this->app->singleton(ReadCache::class, static fn (Application $aplicacao): RedisReadCache => new RedisReadCache(
            $aplicacao->make(RepositorioCache::class),
            $aplicacao->make(RedisFactory::class),
            $aplicacao->make(LoggerInterface::class),
        ));

        $this->app->singleton(CacheKeyFactory::class, static fn (Container $aplicacao): RedisCacheKeyFactory => new RedisCacheKeyFactory(
            $aplicacao->make(ReadCache::class),
            Config::string('cache.prefix', 'mobycontract'),
        ));
    }

    public function boot(): void
    {
        RateLimiter::for('validacao-publica', static fn (Request $request): Limit => Limit::perMinute(5)->by($request->ip()));

        RateLimiter::for('verificacao-documento', static function (Request $request): Limit {
            $identificador = $request->user()?->getAuthIdentifier();
            $chave = is_string($identificador) || is_int($identificador)
                ? (string) $identificador
                : $request->ip();

            return Limit::perMinute(20)->by($chave);
        });

        RateLimiter::for('login', static fn (Request $request): Limit => Limit::perMinute(5)
            ->by(mb_strtolower($request->string('email')->toString()).'|'.$request->ip()));

        /**
         * O Passport valida os escopos no momento da emissão do token
         * (usuário->createToken(abilities)). Sem este registro, qualquer
         * usuário que não seja administrador recebia 500 no login
         * ("The requested scope is invalid, unknown, or malformed").
         */
        Passport::tokensCan([
            '*' => 'Acesso total',
            'listar' => 'Listar registros',
            'criar' => 'Criar registros',
            'alterar' => 'Alterar registros',
            'consultar' => 'Consultar registros',
        ]);
    }
}
