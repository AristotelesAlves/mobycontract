<?php

declare(strict_types=1);

namespace App\Modules\Contratos\Services;

use App\Modules\Contratos\Models\Contrato;
use App\Shared\Application\Exceptions\ServicoIndisponivelException;
use App\Shared\Contracts\ExternalHttpClient;
use Illuminate\Support\Facades\Config;

final readonly class VerificadorDocumentoBlockchain
{
    public function __construct(private ExternalHttpClient $http) {}

    /** @return array<string, bool|int|string|null> */
    public function verificar(int $contratoId, string $hashArquivo): array
    {
        $contrato = Contrato::query()->findOrFail($contratoId);
        $base = [
            'contratoId' => $contrato->id,
            'numero' => $contrato->numero,
            'hashArquivo' => $hashArquivo,
            'hashTransacao' => $contrato->hash_blockchain,
            'verificadoEm' => now()->toIso8601String(),
        ];
        if (! $contrato->hash_blockchain) {
            return [...$base, 'resultado' => 'nao_registrado', 'mensagem' => 'Este contrato ainda não foi registrado na blockchain.'];
        }
        if (! $contrato->documento_hash_blockchain) {
            return [...$base, 'resultado' => 'registro_antigo', 'mensagem' => 'O registro deste contrato não inclui o hash do arquivo. Não é possível confirmar este documento pela blockchain.'];
        }

        $resposta = $this->http->post(
            rtrim(Config::string('mobycontract.blockchain.registry_url'), '/').'/contratos/'.$contrato->id.'/integridade',
            ['documentoHash' => $hashArquivo],
            ['x-api-key' => Config::string('mobycontract.blockchain.api_key')],
        );
        $erro = $resposta['body']['erro'] ?? null;
        $motivo = is_array($erro) && is_array($erro['detalhe'] ?? null)
            ? $erro['detalhe']['motivo'] ?? null
            : null;
        if ($resposta['status'] === 404 || ($resposta['status'] === 409 && $motivo === 'E_UNKNOWN_CONTRACT')) {
            return [...$base, 'resultado' => 'nao_registrado', 'mensagem' => 'O registro não foi encontrado na blockchain consultada.'];
        }
        $dados = $resposta['body'];
        $ancorado = $dados['hashAncorado'] ?? null;
        if ($resposta['status'] !== 200 || ! is_string($ancorado) || ! preg_match('/^0x[a-fA-F0-9]{64}$/D', $ancorado) || ! is_bool($dados['integro'] ?? null)) {
            throw new ServicoIndisponivelException('Não foi possível consultar a blockchain. Tente novamente.');
        }
        $corresponde = hash_equals(strtolower($ancorado), strtolower($hashArquivo));
        if ($corresponde !== $dados['integro']) {
            throw new ServicoIndisponivelException('A blockchain retornou uma resposta inconsistente. Tente novamente.');
        }

        return [
            ...$base,
            'resultado' => $corresponde ? 'correspondente' : 'diferente',
            'hashRegistrado' => $ancorado,
            'mensagem' => $corresponde
                ? 'O arquivo é idêntico ao documento registrado na blockchain.'
                : 'O arquivo é diferente do documento registrado na blockchain. Confira se selecionou o contrato e o arquivo assinado corretos.',
        ];
    }

    /** @return array<string, int|string|null> */
    public function consultarTransacao(string $hashTransacao): array
    {
        $resposta = $this->http->get(
            rtrim(Config::string('mobycontract.blockchain.registry_url'), '/').'/transacoes/'.$hashTransacao,
            ['x-api-key' => Config::string('mobycontract.blockchain.api_key')],
        );
        if ($resposta['status'] === 404) {
            return [
                'resultado' => 'nao_encontrada',
                'mensagem' => 'A transação não foi encontrada na blockchain atualmente conectada.',
                'hashTransacao' => $hashTransacao,
            ];
        }

        $dados = $resposta['body'];
        $hashDocumento = $dados['hashDocumento'] ?? null;
        $chave = $dados['chave'] ?? null;
        $numeroBloco = $dados['numeroBloco'] ?? null;
        $confirmacoes = $dados['confirmacoes'] ?? null;
        if ($resposta['status'] !== 200 || ! is_string($hashDocumento) || ! is_string($chave)
            || ! is_int($numeroBloco) || ! is_int($confirmacoes)) {
            throw new ServicoIndisponivelException('Não foi possível consultar a transação na blockchain. Tente novamente.');
        }

        $contrato = Contrato::query()->where('hash_blockchain', $hashTransacao)->first();

        return [
            'resultado' => 'confirmada',
            'mensagem' => 'A transação foi confirmada na blockchain.',
            'hashTransacao' => $hashTransacao,
            'hashDocumento' => $hashDocumento,
            'chaveBlockchain' => $chave,
            'numeroBloco' => $numeroBloco,
            'confirmacoes' => $confirmacoes,
            'contratoId' => $contrato?->id,
            'numero' => $contrato?->numero,
        ];
    }
}
