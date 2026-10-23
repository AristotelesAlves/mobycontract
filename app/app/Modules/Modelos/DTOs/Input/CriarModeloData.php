<?php

declare(strict_types=1);

namespace App\Modules\Modelos\DTOs\Input;

use App\Modules\Modelos\Domain\Enums\TipoModelo;
use App\Modules\Modelos\Domain\GeometriaDaPagina;
use App\Modules\Modelos\Domain\Tipografia;
use App\Shared\Application\Support\DadosDeEntrada;
use App\Shared\Domain\Enums\Garantia;

final readonly class CriarModeloData
{
    /**
     * @param  array<string, mixed>  $margens
     * @param  array<string, mixed>  $tipografia
     */
    public function __construct(
        public string $titulo,
        public TipoModelo $tipo,
        public int $versao,
        public string $conteudoHtml,
        public ?Garantia $garantia,
        public array $margens,
        public array $tipografia,
    ) {}

    /**
     * @param  array<string, mixed>  $validated
     */
    public static function fromValidated(array $validated): self
    {
        $tipo = TipoModelo::from(DadosDeEntrada::texto($validated, 'tipo'));
        $garantia = DadosDeEntrada::textoOpcional($validated, 'garantia');

        return new self(
            titulo: DadosDeEntrada::texto($validated, 'titulo'),
            tipo: $tipo,
            versao: DadosDeEntrada::inteiro($validated, 'versao', 1),
            conteudoHtml: DadosDeEntrada::texto($validated, 'conteudoHtml'),
            garantia: $tipo->aceitaGarantia() && $garantia !== null ? Garantia::from($garantia) : null,
            margens: GeometriaDaPagina::normalizar(self::mapa($validated, 'margens')),
            tipografia: Tipografia::normalizar(self::mapa($validated, 'tipografia')),
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function paraAtributos(int $codigo): array
    {
        return [
            'codigo' => $codigo,
            'titulo' => $this->titulo,
            'tipo' => $this->tipo->value,
            'status' => true,
            'versao' => $this->versao,
            'conteudo_html' => $this->conteudoHtml,
            'garantia' => $this->garantia?->value,
            'margens' => $this->margens,
            'tipografia' => $this->tipografia,
        ];
    }

    /**
     * @param  array<string, mixed>  $validated
     * @return array<string, mixed>
     */
    private static function mapa(array $validated, string $chave): array
    {
        $valor = $validated[$chave] ?? null;

        if (! is_array($valor)) {
            return [];
        }

        /** @var array<string, mixed> $valor */
        return $valor;
    }
}
