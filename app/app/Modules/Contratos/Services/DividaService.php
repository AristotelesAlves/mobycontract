<?php

declare(strict_types=1);

namespace App\Modules\Contratos\Services;

use App\Modules\Contratos\Contracts\ContratoRepositoryInterface;
use App\Modules\Contratos\Contracts\DividaRepositoryInterface;
use App\Modules\Contratos\Contracts\DividaServiceInterface;
use App\Modules\Contratos\Domain\Enums\StatusDivida;
use App\Modules\Contratos\DTOs\Output\ContratoResult;
use App\Modules\Contratos\DTOs\Output\DividaResult;
use App\Shared\Application\Exceptions\RecursoNaoEncontradoException;
use App\Shared\Application\Exceptions\RegraNegocioException;
use App\Shared\Contracts\Relogio;
use App\Shared\Contracts\TransactionManager;

final readonly class DividaService implements DividaServiceInterface
{
    public function __construct(
        private DividaRepositoryInterface $dividas,
        private ContratoRepositoryInterface $contratos,
        private TransactionManager $transacao,
        private Relogio $relogio,
    ) {}

    /**
     * @return list<DividaResult>
     */
    public function listar(int $contratoId): array
    {
        $this->obterContrato($contratoId);

        return $this->dividas->listarPorContrato($contratoId);
    }

    public function quitar(int $contratoId, int $dividaId, int $autorId): DividaResult
    {
        $this->obterContrato($contratoId);
        $divida = $this->dividas->buscarPorId($contratoId, $dividaId);

        if (! $divida instanceof DividaResult) {
            throw RecursoNaoEncontradoException::contrato($dividaId);
        }

        if ($divida->status === StatusDivida::Quitada->value) {
            throw new RegraNegocioException(
                'Esta dívida já foi quitada.',
                ['status' => 'Somente dívidas em aberto podem ser quitadas.'],
            );
        }

        return $this->transacao->executar(function () use ($contratoId, $dividaId, $autorId): DividaResult {
            $quitada = $this->dividas->quitar(
                $contratoId,
                $dividaId,
                substr($this->relogio->hoje(), 0, 10),
            );

            if (! $quitada instanceof DividaResult) {
                throw new RegraNegocioException('Dívida não encontrada.', []);
            }

            $this->contratos->registrarHistorico($contratoId, [
                'data' => (string) now(),
                'evento' => 'Dívida quitada',
                'detalhe' => $quitada->descricao,
                'usuario_id' => $autorId,
            ]);

            return $quitada;
        });
    }

    private function obterContrato(int $contratoId): ContratoResult
    {
        $contrato = $this->contratos->buscarPorId($contratoId);

        if ($contrato instanceof ContratoResult) {
            return $contrato;
        }

        throw RecursoNaoEncontradoException::contrato($contratoId);
    }
}
