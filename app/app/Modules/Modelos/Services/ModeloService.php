<?php

declare(strict_types=1);

namespace App\Modules\Modelos\Services;

use App\Modules\Modelos\Contracts\ModeloRepositoryInterface;
use App\Modules\Modelos\Contracts\ModeloServiceInterface;
use App\Modules\Modelos\Domain\AnalisadorDeVariaveis;
use App\Modules\Modelos\Domain\AnaliseDeVariaveis;
use App\Modules\Modelos\Domain\Enums\TipoModelo;
use App\Modules\Modelos\DTOs\Input\AlterarModeloData;
use App\Modules\Modelos\DTOs\Input\CriarModeloData;
use App\Modules\Modelos\DTOs\Input\ListarModelosFiltros;
use App\Modules\Modelos\DTOs\Input\ValidarConteudoData;
use App\Modules\Modelos\DTOs\Output\ModeloResult;
use App\Shared\Application\DTOs\PaginatedResult;
use App\Shared\Application\Exceptions\RecursoNaoEncontradoException;
use App\Shared\Contracts\DomainEventDispatcher;
use App\Shared\Contracts\TransactionManager;
use App\Shared\Domain\Enums\Garantia;
use App\Shared\Infrastructure\Security\SanitizadorHtmlDeModelo;
use Illuminate\Validation\ValidationException;

final readonly class ModeloService implements ModeloServiceInterface
{
    public function __construct(
        private ModeloRepositoryInterface $modelos,
        private AnalisadorDeVariaveis $analisador,
        private TransactionManager $transacao,
        private DomainEventDispatcher $eventos,
        private SanitizadorHtmlDeModelo $sanitizadorHtml,
    ) {}

    /**
     * @return PaginatedResult<ModeloResult>
     */
    public function listar(ListarModelosFiltros $filtros): PaginatedResult
    {
        return $this->modelos->paginar($filtros);
    }

    public function obter(int $modeloId): ModeloResult
    {
        $modelo = $this->modelos->buscarPorId($modeloId);

        if (! $modelo instanceof ModeloResult) {
            throw RecursoNaoEncontradoException::modelo($modeloId);
        }

        return $modelo;
    }

    public function armazenar(CriarModeloData $dados, int $autorId): ModeloResult
    {
        $conteudoHtml = $this->sanitizadorHtml->sanitizar($dados->conteudoHtml);
        $this->validarConteudoDeModelo($dados->tipo, $conteudoHtml);

        if ($dados->tipo->exigeGarantia() && ! $dados->garantia instanceof Garantia) {
            throw ValidationException::withMessages(['garantia' => 'Selecione a garantia vinculada.']);
        }

        return $this->transacao->executar(function () use ($dados, $autorId, $conteudoHtml): ModeloResult {
            $codigo = $this->modelos->proximoCodigo();
            $atributos = $dados->paraAtributos($codigo);
            $atributos['conteudo_html'] = $conteudoHtml;
            $modelo = $this->modelos->criar($atributos);

            $this->eventos->publicar('modelo.criado', [
                'modeloId' => $modelo->id,
                'autorId' => $autorId,
            ]);

            return $modelo;
        });
    }

    public function alterar(AlterarModeloData $dados, int $autorId): ModeloResult
    {
        $atual = $this->obter($dados->id);
        $tipo = $dados->tipoResultante($atual);

        $conteudoHtml = $this->sanitizadorHtml->sanitizar($dados->conteudoResultante($atual));
        $this->validarConteudoDeModelo($tipo, $conteudoHtml);

        $atributos = $dados->paraAtributosSobre($atual);
        $atributos['conteudo_html'] = $conteudoHtml;

        if ($tipo->exigeGarantia() && ($atributos['garantia'] ?? null) === null) {
            throw ValidationException::withMessages(['garantia' => 'Selecione a garantia vinculada.']);
        }

        return $this->transacao->executar(function () use ($dados, $atributos): ModeloResult {
            $modelo = $this->modelos->atualizar($dados->id, $atributos);

            $this->eventos->publicar('modelo.alterado', ['modeloId' => $modelo->id]);

            return $modelo;
        });
    }

    public function clonar(int $modeloId, int $autorId): ModeloResult
    {
        $atual = $this->obter($modeloId);
        $codigo = $this->modelos->proximoCodigo();

        return $this->transacao->executar(function () use ($atual, $codigo, $autorId): ModeloResult {
            $this->modelos->definirAtivo($atual->id, false);

            $novo = $this->modelos->criar([
                'codigo' => $codigo,
                'titulo' => $atual->titulo,
                'tipo' => $atual->tipo,
                'status' => true,
                'versao' => $atual->versao + 1,
                'conteudo_html' => $atual->conteudoHtml,
                'garantia' => $atual->garantia,
                'margens' => $atual->margens,
                'tipografia' => $atual->tipografia,
            ]);

            $this->eventos->publicar('modelo.clonado', [
                'modeloId' => $novo->id,
                'origemId' => $atual->id,
                'autorId' => $autorId,
            ]);

            return $novo;
        });
    }

    public function ativar(int $modeloId, int $autorId): ModeloResult
    {
        $this->obter($modeloId);

        return $this->transacao->executar(function () use ($modeloId, $autorId): ModeloResult {
            $modelo = $this->modelos->definirAtivo($modeloId, true);

            $this->eventos->publicar('modelo.ativado', ['modeloId' => $modeloId, 'autorId' => $autorId]);

            return $modelo;
        });
    }

    public function inativar(int $modeloId, int $autorId): ModeloResult
    {
        $this->obter($modeloId);

        return $this->transacao->executar(function () use ($modeloId, $autorId): ModeloResult {
            $modelo = $this->modelos->definirAtivo($modeloId, false);

            $this->eventos->publicar('modelo.inativado', ['modeloId' => $modeloId, 'autorId' => $autorId]);

            return $modelo;
        });
    }

    public function validarConteudo(ValidarConteudoData $dados): AnaliseDeVariaveis
    {
        return $this->analisador->analisar($this->sanitizadorHtml->sanitizar($dados->conteudoHtml));
    }

    private function validarConteudoDeModelo(TipoModelo $tipo, string $conteudoHtml): void
    {
        $analise = $this->analisador->analisar($conteudoHtml);

        if ($analise->possuiInvalida()) {
            throw ValidationException::withMessages([
                'conteudoHtml' => 'Variável inválida no documento.',
            ]);
        }

        if (! $tipo->aceitaVariaveisDeAditivo() && $analise->caminhosDeAditivo() !== []) {
            throw ValidationException::withMessages([
                'conteudoHtml' => 'Variáveis de aditivo só são aceitas em modelos do tipo Aditivo.',
            ]);
        }
    }
}
