<?php

declare(strict_types=1);

namespace App\Modules\Modelos\DTOs\Input;

use App\Modules\Modelos\Domain\Enums\TipoModelo;
use App\Modules\Modelos\Domain\GeometriaDaPagina;
use App\Modules\Modelos\Domain\Tipografia;
use App\Modules\Modelos\DTOs\Output\ModeloResult;
use App\Shared\Application\Support\DadosDeEntrada;
use App\Shared\Domain\Enums\Garantia;

final readonly class AlterarModeloData
{
    /**
     * @param  array<string, mixed>  $campos
     */
    private function __construct(
        public int $id,
        private ?TipoModelo $tipo,
        private array $campos,
    ) {}

    /**
     * @param  array<string, mixed>  $validated
     */
    public static function fromValidated(array $validated): self
    {
        $id = DadosDeEntrada::inteiro($validated, 'id');
        $tipo = DadosDeEntrada::textoOpcional($validated, 'tipo');

        unset($validated['id']);

        return new self(
            $id,
            $tipo === null ? null : TipoModelo::from($tipo),
            $validated,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function paraAtributosSobre(ModeloResult $atual): array
    {
        $tipo = $this->tipoResultante($atual);

        return [
            'titulo' => $this->informado('titulo') ? DadosDeEntrada::texto($this->campos, 'titulo') : $atual->titulo,
            'tipo' => $tipo->value,
            'versao' => $this->informado('versao') ? DadosDeEntrada::inteiro($this->campos, 'versao', $atual->versao) : $atual->versao,
            'conteudo_html' => $this->conteudoResultante($atual),
            'garantia' => $this->garantiaAplicavel($atual, $tipo)?->value,
            'margens' => $this->informado('margens') ? GeometriaDaPagina::normalizar($this->mapa('margens')) : $atual->margens,
            'tipografia' => $this->informado('tipografia') ? Tipografia::normalizar($this->mapa('tipografia')) : $atual->tipografia,
        ];
    }

    public function tipoResultante(ModeloResult $atual): TipoModelo
    {
        return $this->tipo ?? TipoModelo::from($atual->tipo);
    }

    public function conteudoResultante(ModeloResult $atual): string
    {
        return $this->informado('conteudoHtml')
            ? DadosDeEntrada::texto($this->campos, 'conteudoHtml')
            : $atual->conteudoHtml;
    }

    private function garantiaAplicavel(ModeloResult $atual, TipoModelo $tipo): ?Garantia
    {
        if (! $tipo->aceitaGarantia()) {
            return null;
        }

        if ($this->informado('garantia')) {
            $valor = DadosDeEntrada::textoOpcional($this->campos, 'garantia');

            return $valor === null ? null : Garantia::from($valor);
        }

        return $atual->garantia === null ? null : Garantia::tryFrom($atual->garantia);
    }

    private function informado(string $chave): bool
    {
        return DadosDeEntrada::existe($this->campos, $chave);
    }

    /**
     * @return array<string, mixed>
     */
    private function mapa(string $chave): array
    {
        $valor = $this->campos[$chave] ?? null;

        if (! is_array($valor)) {
            return [];
        }

        /** @var array<string, mixed> $valor */
        return $valor;
    }
}
