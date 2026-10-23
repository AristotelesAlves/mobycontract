<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\Http;

use App\Shared\Application\Exceptions\ServicoIndisponivelException;
use App\Shared\Contracts\ExternalHttpClient;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;

final class LaravelExternalHttpClient implements ExternalHttpClient
{
    public function get(string $url, array $headers = [], int $timeout = 5): array
    {
        return $this->enviar('GET', $url, null, $headers, $timeout);
    }

    public function post(string $url, array $payload, array $headers = [], int $timeout = 10): array
    {
        return $this->enviar('POST', $url, $payload, $headers, $timeout);
    }

    /**
     * @return array<string, mixed>
     */
    private function corpoComoArray(mixed $corpo): array
    {
        if (! is_array($corpo)) {
            return [];
        }

        $dicionario = [];

        foreach ($corpo as $chave => $valor) {
            if (is_string($chave)) {
                $dicionario[$chave] = $valor;
            }
        }

        return $dicionario;
    }

    /**
     * @param  array<string, mixed>|null  $payload
     * @param  array<string, string>  $headers
     * @return array{status: int, body: array<string, mixed>}
     */
    private function enviar(string $metodo, string $url, ?array $payload, array $headers, int $timeout): array
    {
        try {
            $requisicao = Http::timeout($timeout)
                ->withHeaders($headers)
                ->acceptJson();

            $resposta = $metodo === 'POST'
                ? $requisicao->post($url, $payload ?? [])
                : $requisicao->get($url);

            return [
                'status' => $resposta->status(),
                'body' => $this->corpoComoArray($resposta->json()),
            ];
        } catch (ConnectionException $semConexao) {
            throw new ServicoIndisponivelException(
                'Serviço externo indisponível. Tente novamente.',
                ['url' => $url],
                $semConexao,
            );
        }
    }
}
