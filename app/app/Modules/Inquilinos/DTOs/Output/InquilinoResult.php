<?php

declare(strict_types=1);

namespace App\Modules\Inquilinos\DTOs\Output;

use App\Modules\Inquilinos\Models\Inquilino;
use DateTimeInterface;

final readonly class InquilinoResult
{
    /**
     * @param  array<string, mixed>|null  $contratoAtivo
     */
    public function __construct(
        public int $id,
        public string $nome,
        public string $cpf,
        public string $tipoPessoa,
        public ?string $rg,
        public string $email,
        public string $telefone,
        public string $dataNascimento,
        public string $estadoCivil,
        public ?string $profissao,
        public float $renda,
        public string $endereco,
        public ?int $clienteId,
        public ?string $dataCadastro,
        public int $idade,
        public ?array $contratoAtivo,
    ) {}

    public static function fromModel(Inquilino $inquilino): self
    {
        return new self(
            id: (int) $inquilino->id,
            nome: (string) $inquilino->nome,
            cpf: (string) $inquilino->cpf,
            tipoPessoa: (string) $inquilino->tipo_pessoa,
            rg: self::textoOuNulo($inquilino->rg),
            email: (string) $inquilino->email,
            telefone: (string) $inquilino->telefone,
            dataNascimento: self::data($inquilino->data_nascimento),
            estadoCivil: (string) $inquilino->estado_civil,
            profissao: self::textoOuNulo($inquilino->profissao),
            renda: (float) $inquilino->renda,
            endereco: (string) $inquilino->endereco,
            clienteId: is_numeric($inquilino->cliente_id) ? (int) $inquilino->cliente_id : null,
            dataCadastro: self::instante($inquilino->data_cadastro),
            idade: self::idade($inquilino->data_nascimento),
            contratoAtivo: null,
        );
    }

    private static function textoOuNulo(mixed $valor): ?string
    {
        return is_scalar($valor) ? (string) $valor : null;
    }

    private static function data(mixed $valor): string
    {
        return $valor instanceof DateTimeInterface ? $valor->format('Y-m-d') : '';
    }

    private static function instante(mixed $valor): ?string
    {
        return $valor instanceof DateTimeInterface ? $valor->format(DATE_ATOM) : null;
    }

    private static function idade(mixed $valor): int
    {
        if (! $valor instanceof DateTimeInterface) {
            return 0;
        }

        return (int) $valor->diff(now())->y;
    }
}
