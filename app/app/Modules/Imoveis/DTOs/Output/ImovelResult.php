<?php

declare(strict_types=1);

namespace App\Modules\Imoveis\DTOs\Output;

use App\Modules\Imoveis\Models\Imovel;
use DateTimeInterface;

final readonly class ImovelResult
{
    public function __construct(
        public int $id,
        public string $titulo,
        public string $tipo,
        public string $situacao,
        public string $cep,
        public string $logradouro,
        public string $numero,
        public ?string $complemento,
        public ?string $bairro,
        public string $cidade,
        public ?string $estado,
        public string $endereco,
        public float $area,
        public int $quartos,
        public int $banheiros,
        public int $vagasGaragem,
        public float $valorAluguel,
        public ?float $valorIptu,
        public ?float $valorCondominio,
        public bool $aguaInclusa,
        public bool $energiaInclusa,
        public bool $internetInclusa,
        public bool $gasIncluso,
        public ?string $matricula,
        public ?string $descricao,
        public ?string $criadoEm,
        public ?string $atualizadoEm,
        public ?string $nomeProprietario = null,
    ) {}

    public static function fromModel(Imovel $imovel): self
    {
        return new self(
            id: (int) $imovel->id,
            titulo: (string) $imovel->titulo,
            tipo: (string) $imovel->tipo,
            situacao: (string) $imovel->status,
            cep: (string) $imovel->cep,
            logradouro: (string) $imovel->logradouro,
            numero: (string) $imovel->numero,
            complemento: self::textoOuNulo($imovel->complemento),
            bairro: self::textoOuNulo($imovel->bairro),
            cidade: (string) $imovel->cidade,
            estado: self::textoOuNulo($imovel->estado),
            endereco: (string) $imovel->endereco,
            area: (float) $imovel->area,
            quartos: (int) $imovel->quartos,
            banheiros: (int) $imovel->banheiros,
            vagasGaragem: (int) $imovel->vagas_garagem,
            valorAluguel: (float) $imovel->valor_aluguel,
            valorIptu: self::decimalOuNulo($imovel->valor_iptu),
            valorCondominio: self::decimalOuNulo($imovel->valor_condominio),
            aguaInclusa: (bool) $imovel->agua_inclusa,
            energiaInclusa: (bool) $imovel->energia_inclusa,
            internetInclusa: (bool) $imovel->internet_inclusa,
            gasIncluso: (bool) $imovel->gas_incluso,
            matricula: self::textoOuNulo($imovel->matricula),
            descricao: self::textoOuNulo($imovel->descricao),
            criadoEm: self::instante($imovel->created_at),
            atualizadoEm: self::instante($imovel->updated_at),
            nomeProprietario: self::textoOuNulo($imovel->nome_proprietario),
        );
    }

    private static function textoOuNulo(mixed $valor): ?string
    {
        return is_scalar($valor) ? (string) $valor : null;
    }

    private static function decimalOuNulo(mixed $valor): ?float
    {
        return is_numeric($valor) ? (float) $valor : null;
    }

    private static function instante(mixed $valor): ?string
    {
        return $valor instanceof DateTimeInterface ? $valor->format(DATE_ATOM) : null;
    }
}
