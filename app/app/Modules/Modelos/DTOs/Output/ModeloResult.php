<?php

declare(strict_types=1);

namespace App\Modules\Modelos\DTOs\Output;

use App\Modules\Modelos\Domain\CatalogoDeVariaveis;
use App\Modules\Modelos\Models\Modelo;
use DateTimeInterface;

final readonly class ModeloResult
{
    /**
     * @param  array<string, mixed>  $margens
     * @param  array<string, mixed>  $tipografia
     * @param  list<VariavelConhecidaResult>  $caminhosConhecidos
     */
    public function __construct(
        public int $id,
        public int $codigo,
        public string $titulo,
        public string $tipo,
        public bool $ativo,
        public int $versao,
        public string $conteudoHtml,
        public ?string $garantia,
        public array $margens,
        public array $tipografia,
        public array $caminhosConhecidos,
        public ?string $criadoEm,
        public ?string $atualizadoEm,
        public bool $vinculadoAContrato = false,
    ) {}

    public static function fromModel(Modelo $modelo): self
    {
        return new self(
            id: (int) $modelo->id,
            codigo: (int) $modelo->codigo,
            titulo: (string) $modelo->titulo,
            tipo: (string) $modelo->tipo,
            ativo: (bool) $modelo->status,
            versao: (int) $modelo->versao,
            conteudoHtml: (string) $modelo->conteudo_html,
            garantia: self::textoOuNulo($modelo->garantia),
            margens: self::mapa($modelo->margens),
            tipografia: self::mapa($modelo->tipografia),
            caminhosConhecidos: self::caminhosConhecidos(),
            criadoEm: self::instante($modelo->created_at),
            atualizadoEm: self::instante($modelo->updated_at),
            vinculadoAContrato: (bool) $modelo->getAttribute('contratos_exists'),
        );
    }

    /**
     * @return list<VariavelConhecidaResult>
     */
    private static function caminhosConhecidos(): array
    {
        return array_map(
            static fn (array $variavel): VariavelConhecidaResult => new VariavelConhecidaResult(
                $variavel['caminho'],
                $variavel['origem'],
                $variavel['exemplo'],
            ),
            CatalogoDeVariaveis::conhecidos(),
        );
    }

    private static function textoOuNulo(mixed $valor): ?string
    {
        return is_scalar($valor) ? (string) $valor : null;
    }

    /**
     * @return array<string, mixed>
     */
    private static function mapa(mixed $valor): array
    {
        if (! is_array($valor)) {
            return [];
        }

        /** @var array<string, mixed> $valor */
        return $valor;
    }

    private static function instante(mixed $valor): ?string
    {
        return $valor instanceof DateTimeInterface ? $valor->format(DATE_ATOM) : null;
    }
}
