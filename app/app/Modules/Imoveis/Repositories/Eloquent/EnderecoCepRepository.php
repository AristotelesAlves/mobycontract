<?php

declare(strict_types=1);

namespace App\Modules\Imoveis\Repositories\Eloquent;

use App\Modules\Imoveis\Contracts\EnderecoCepRepositoryInterface;
use App\Modules\Imoveis\DTOs\Output\EnderecoCepResult;
use App\Shared\Application\Exceptions\ServicoIndisponivelException;
use App\Shared\Contracts\ExternalHttpClient;
use App\Shared\Contracts\ReadCache;
use Throwable;

final readonly class EnderecoCepRepository implements EnderecoCepRepositoryInterface
{
    public function __construct(
        private ExternalHttpClient $http,
        private ReadCache $cache,
    ) {}

    public function consultar(string $cep): EnderecoCepResult
    {
        $chave = sprintf('mobycontract:externo:endereco:por-cep:%s', $cep);

        /** @var EnderecoCepResult $resultado */
        $resultado = $this->cache->lembrar($chave, 86400, function () use ($cep): EnderecoCepResult {
            try {
                $resposta = $this->http->get(sprintf('https://viacep.com.br/ws/%s/json/', $cep), [], 5);
            } catch (Throwable $falha) {
                throw new ServicoIndisponivelException('Serviço de consulta de endereço indisponível.', ['cep' => $cep], $falha);
            }

            if ($resposta['status'] >= 500) {
                throw new ServicoIndisponivelException('Serviço de consulta de endereço retornou instabilidade.', ['status' => $resposta['status']]);
            }

            $dados = $resposta['body'];

            if ($resposta['status'] === 404 || ($dados['erro'] ?? false) === true || ($dados['erro'] ?? '') === 'true') {
                return new EnderecoCepResult(
                    logradouro: null,
                    bairro: null,
                    cidade: null,
                    estado: null,
                    encontrado: false,
                );
            }

            return new EnderecoCepResult(
                logradouro: isset($dados['logradouro']) && is_string($dados['logradouro']) && $dados['logradouro'] !== '' ? $dados['logradouro'] : null,
                bairro: isset($dados['bairro']) && is_string($dados['bairro']) && $dados['bairro'] !== '' ? $dados['bairro'] : null,
                cidade: isset($dados['localidade']) && is_string($dados['localidade']) ? $dados['localidade'] : (isset($dados['cidade']) && is_string($dados['cidade']) ? $dados['cidade'] : null),
                estado: isset($dados['uf']) && is_string($dados['uf']) ? $dados['uf'] : (isset($dados['estado']) && is_string($dados['estado']) ? $dados['estado'] : null),
                encontrado: true,
            );
        });

        return $resultado;
    }
}
