<?php

declare(strict_types=1);

namespace App\Modules\Vistorias\Services;

use App\Modules\Contratos\Contracts\ContratoRepositoryInterface;
use App\Modules\Contratos\Domain\Enums\StatusContrato;
use App\Modules\Contratos\DTOs\Output\ContratoResult;
use App\Modules\Vistorias\Contracts\VistoriaRepositoryInterface;
use App\Modules\Vistorias\Contracts\VistoriaServiceInterface;
use App\Modules\Vistorias\Domain\Enums\StatusVistoria;
use App\Modules\Vistorias\Domain\Enums\TipoVistoria;
use App\Modules\Vistorias\DTOs\Input\AlterarStatusVistoriaData;
use App\Modules\Vistorias\DTOs\Input\AlterarVistoriaData;
use App\Modules\Vistorias\DTOs\Input\CriarVistoriaData;
use App\Modules\Vistorias\DTOs\Input\ItemVistoriaData;
use App\Modules\Vistorias\DTOs\Input\ListarVistoriasFiltros;
use App\Modules\Vistorias\DTOs\Output\VistoriaFotoResult;
use App\Modules\Vistorias\DTOs\Output\VistoriaResult;
use App\Shared\Application\DTOs\PaginatedResult;
use App\Shared\Application\Exceptions\RecursoNaoEncontradoException;
use App\Shared\Application\Exceptions\RegraNegocioException;
use App\Shared\Application\Exceptions\TransicaoDeStatusInvalidaException;
use App\Shared\Contracts\ArmazenadorDeArquivo;
use App\Shared\Contracts\Relogio;
use App\Shared\Contracts\TransactionManager;
use Illuminate\Support\Str;

final readonly class VistoriaService implements VistoriaServiceInterface
{
    public function __construct(
        private VistoriaRepositoryInterface $vistorias,
        private ContratoRepositoryInterface $contratos,
        private TransactionManager $transacao,
        private Relogio $relogio,
        private ArmazenadorDeArquivo $arquivos,
    ) {}

    /**
     * @return PaginatedResult<VistoriaResult>
     */
    public function listar(ListarVistoriasFiltros $filtros): PaginatedResult
    {
        $this->obterContrato($filtros->contratoId);

        return $this->vistorias->paginar($filtros);
    }

    public function obter(int $vistoriaId): VistoriaResult
    {
        return $this->obterVistoria($vistoriaId);
    }

    public function obterPorChaveDeAcesso(string $chaveAcesso): VistoriaResult
    {
        $vistoria = $this->vistorias->buscarPorChaveAcesso($chaveAcesso);

        if (! $vistoria instanceof VistoriaResult) {
            throw new RegraNegocioException(
                'Vistoria não encontrada para a chave informada.',
                ['chave' => 'A chave de acesso não corresponde a nenhuma vistoria.'],
            );
        }

        return $vistoria;
    }

    public function armazenar(CriarVistoriaData $dados): VistoriaResult
    {
        $contrato = $this->obterContrato($dados->contratoId);

        $statusContrato = StatusContrato::tryFrom($contrato->status);

        if ($statusContrato === StatusContrato::Encerrado || $statusContrato === StatusContrato::Cancelado) {
            throw new RegraNegocioException(
                'Contratos encerrados ou cancelados não recebem novas vistorias.',
                ['contratoId' => 'Este contrato está encerrado/cancelado e não pode receber vistorias.'],
            );
        }

        return $this->transacao->executar(function () use ($dados, $contrato): VistoriaResult {
            $ano = substr($this->relogio->hoje(), 0, 4);

            $vistoria = $this->vistorias->criar([
                'contrato_id' => $contrato->id,
                'numero' => $this->vistorias->proximoNumero($contrato->id, $ano),
                'tipo' => $dados->tipo->value,
                'vistoriador' => $dados->vistoriador,
                'data' => $dados->data,
                'status' => StatusVistoria::Agendada->value,
                'observacoes' => $dados->observacoes,
                'chave_acesso' => (string) Str::uuid(),
            ]);

            if ($dados->itens !== []) {
                $this->vistorias->substituirItens(
                    $vistoria->id,
                    array_map(
                        static fn (ItemVistoriaData $item): array => $item->paraAtributos(),
                        $dados->itens,
                    ),
                );
            }

            return $this->obterVistoria($vistoria->id);
        });
    }

    public function alterar(int $vistoriaId, AlterarVistoriaData $dados): VistoriaResult
    {
        $vistoria = $this->obterVistoria($vistoriaId);

        if (! $vistoria->podeAlterar) {
            throw new RegraNegocioException(
                'A vistoria não pode ser alterada no status atual.',
                ['status' => 'Somente vistorias agendadas ou em andamento podem ser alteradas.'],
            );
        }

        return $this->transacao->executar(function () use ($vistoriaId, $dados): VistoriaResult {
            $atributos = $this->atributosDeAlteracao($dados);

            if ($atributos !== []) {
                $this->vistorias->atualizar($vistoriaId, $atributos);
            }

            if ($dados->itensInformados && $dados->itens !== null) {
                $this->vistorias->substituirItens(
                    $vistoriaId,
                    array_map(
                        static fn (ItemVistoriaData $item): array => $item->paraAtributos(),
                        $dados->itens,
                    ),
                );
            }

            return $this->obterVistoria($vistoriaId);
        });
    }

    public function alterarStatus(int $vistoriaId, AlterarStatusVistoriaData $dados): VistoriaResult
    {
        $vistoria = $this->obterVistoria($vistoriaId);
        $atual = StatusVistoria::from($vistoria->status);

        if (! $atual->podeTransicionarPara($dados->status)) {
            throw TransicaoDeStatusInvalidaException::entre($atual->value, $dados->status->value);
        }

        $motivo = $dados->motivo !== null ? trim($dados->motivo) : '';

        if ($motivo !== '' && mb_strlen($motivo) < 10) {
            throw new RegraNegocioException(
                'O motivo do cancelamento é muito curto.',
                ['motivo' => 'Se informado, o motivo deve ter ao menos 10 caracteres.'],
            );
        }

        $this->vistorias->atualizar($vistoriaId, [
            'status' => $dados->status->value,
        ]);

        return $this->obterVistoria($vistoriaId);
    }

    public function excluir(int $vistoriaId): void
    {
        $vistoria = $this->obterVistoria($vistoriaId);

        if (! $vistoria->podeExcluir) {
            throw new RegraNegocioException(
                'Somente vistorias agendadas podem ser excluídas.',
                ['status' => 'Vistorias concluídas ou canceladas permanecem no histórico.'],
            );
        }

        $this->vistorias->excluir($vistoriaId);
    }

    public function registrarFoto(
        int $vistoriaId,
        ?string $nomeAnexo,
        ?string $anexo,
        ?string $descricao,
    ): VistoriaFotoResult {
        $this->obterVistoria($vistoriaId);

        if (($nomeAnexo === null || $nomeAnexo === '') && $anexo === null) {
            throw new RegraNegocioException(
                'Informe o arquivo ou o nome da foto.',
                ['nomeAnexo' => 'Informe o nome do arquivo da foto.'],
            );
        }

        return $this->vistorias->criarFoto($vistoriaId, [
            'descricao' => $descricao,
            'nome_anexo' => $nomeAnexo ?? 'foto',
            'anexo' => $anexo,
        ]);
    }

    public function excluirFoto(int $vistoriaId, int $fotoId): void
    {
        $vistoria = $this->obterVistoria($vistoriaId);
        foreach ($vistoria->fotos as $foto) {
            if ($foto->id === $fotoId && $foto->anexo !== null) {
                $this->arquivos->excluir($foto->anexo);
            }
        }
        $this->vistorias->excluirFoto($vistoriaId, $fotoId);
    }

    /**
     * @return array<string, mixed>
     */
    private function atributosDeAlteracao(AlterarVistoriaData $dados): array
    {
        $atributos = [];

        if ($dados->tipo instanceof TipoVistoria) {
            $atributos['tipo'] = $dados->tipo->value;
        }

        if ($dados->data !== null) {
            $atributos['data'] = $dados->data;
        }

        if ($dados->vistoriador !== null) {
            $atributos['vistoriador'] = $dados->vistoriador;
        }

        if ($dados->observacoes !== null) {
            $atributos['observacoes'] = $dados->observacoes;
        }

        return $atributos;
    }

    private function obterVistoria(int $vistoriaId): VistoriaResult
    {
        $vistoria = $this->vistorias->buscarPorId($vistoriaId);

        if (! $vistoria instanceof VistoriaResult) {
            throw RecursoNaoEncontradoException::vistoria($vistoriaId);
        }

        return $vistoria;
    }

    private function obterContrato(int $contratoId): ContratoResult
    {
        $contrato = $this->contratos->buscarPorId($contratoId);

        if (! $contrato instanceof ContratoResult) {
            throw RecursoNaoEncontradoException::contrato($contratoId);
        }

        return $contrato;
    }
}
