<?php

declare(strict_types=1);

namespace App\Shared\Database;

use App\Shared\Application\Exceptions\RegraNegocioException;
use App\Shared\Contracts\SequenciaNumericaInterface;
use Illuminate\Database\DatabaseManager;
use Illuminate\Database\QueryException;
use Throwable;

final readonly class SequenciaNumerica implements SequenciaNumericaInterface
{
    public function __construct(
        private DatabaseManager $banco,
    ) {}

    /**
     * Exige transação ativa: o UPDATE trava a linha até o commit (R-14.5).
     */
    public function proximo(string $nome): int
    {
        try {
            $this->avancarValor($nome);

            $valor = $this->banco->table('sequences')
                ->where('nome', $nome)
                ->value('valor');

            if (! is_numeric($valor)) {
                throw new RegraNegocioException(
                    'Não foi possível gerar a numeração do documento.',
                    [],
                    ['sequencia' => $nome],
                );
            }

            return (int) $valor;
        } catch (QueryException $falhaDeSequencia) {
            throw new RegraNegocioException(
                'Não foi possível gerar a numeração do documento.',
                [],
                ['sequencia' => $nome],
                $falhaDeSequencia,
            );
        } catch (Throwable $falhaInesperada) {
            throw new RegraNegocioException(
                'Não foi possível gerar a numeração do documento.',
                [],
                ['sequencia' => $nome],
                $falhaInesperada,
            );
        }
    }

    private function avancarValor(string $nome): void
    {
        $this->banco->update(
            'UPDATE sequences SET valor = valor + 1, atualizado_em = now() WHERE nome = ?',
            [$nome],
        );
    }
}
