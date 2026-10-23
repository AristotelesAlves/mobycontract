<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\Blockchain;

use App\Shared\Contracts\ExternalHttpClient;
use App\Shared\Contracts\RegistradorBlockchain;
use RuntimeException;

/**
 * Registrador de producao: fala com o registry service da rede blockchain
 * (R-11.1.6). Recebe o conteudo determinístico, ancora no contrato inteligente
 * e devolve o hash da transacao retornado pela rede.
 */
final readonly class MobyLedgerClient implements RegistradorBlockchain
{
    public function __construct(
        private string $registryUrl,
        private string $apiKey,
        private ExternalHttpClient $httpClient,
    ) {}

    public function registrar(string $conteudo): string
    {
        $campos = explode('|', $conteudo);
        $contratoId = $campos[1] ?? hash('sha256', $conteudo);

        $hashDocumento = '0x'.hash('sha256', $conteudo);
        $hashArquivo = end($campos);
        $registroDeContrato = ($campos[0] ?? '') === 'contrato';

        if ($registroDeContrato && (! is_string($hashArquivo) || preg_match('/^0x[a-f0-9]{64}$/D', $hashArquivo) !== 1)) {
            throw new RuntimeException('O registro do contrato exige o hash SHA-256 do arquivo assinado.');
        }

        if ($registroDeContrato) {
            $hashDocumento = (string) $hashArquivo;
        }

        $payload = [
            'versaoEsquema' => 1,
            'contratoId' => $contratoId,
            'imovelId' => $campos[3] ?? hash('sha256', $conteudo.'|imovel'),
            'inquilinoId' => $campos[4] ?? hash('sha256', $conteudo.'|inquilino'),
            'dataInicio' => $this->normalizarData($campos[5] ?? ''),
            'dataFim' => $this->normalizarData($campos[6] ?? '', true),
            'valorMensalCentavos' => $this->paraCentavos($campos[7] ?? '0'),
            'garantia' => 'Sem garantia',
            'modeloId' => hash('sha256', $conteudo.'|modelo'),
            'partes' => ['0x0000000000000000000000000000000000000001'],
            'tipoHash' => 0,
            'prazoVigenciaDias' => 365,
            'indiceReajuste' => 0,
            'documentoHash' => $hashDocumento,
        ];

        $resposta = $this->httpClient->post(
            rtrim($this->registryUrl, '/').'/ancoras/contratos',
            $payload,
            ['x-api-key' => $this->apiKey],
        );

        if (! isset($resposta['body']['hashTransacao']) || ! is_string($resposta['body']['hashTransacao'])) {
            throw new RuntimeException(
                'Registro em blockchain falhou: o registry nao devolveu hashTransacao.',
            );
        }

        return $resposta['body']['hashTransacao'];
    }

    private function normalizarData(string $valor, bool $permitirVazio = false): ?string
    {
        if ($valor === '') {
            return $permitirVazio ? null : date('Y-m-d');
        }

        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $valor) && checkdate((int) substr($valor, 5, 2), (int) substr($valor, 8, 2), (int) substr($valor, 0, 4))) {
            return $valor;
        }

        return $permitirVazio ? null : date('Y-m-d');
    }

    private function paraCentavos(string $valor): string
    {
        $limpo = preg_replace('/[^0-9,.-]/', '', $valor) ?? '';
        $numero = (float) str_replace(',', '.', $limpo);

        return (string) (int) round(max(0, $numero) * 100);
    }
}
