<?php

declare(strict_types=1);

namespace App\Modules\Contratos\DTOs\Input;

use App\Modules\Inquilinos\Domain\ValidadorDeDocumento;
use App\Shared\Application\Support\DadosDeEntrada;

final readonly class FiadorData
{
    public function __construct(
        public string $nome,
        public string $cpf,
        public string $telefone,
    ) {}

    /**
     * @param  array<string, mixed>  $dados
     */
    public static function fromArray(array $dados): self
    {
        return new self(
            nome: DadosDeEntrada::texto($dados, 'nome'),
            cpf: ValidadorDeDocumento::somenteDigitos(DadosDeEntrada::texto($dados, 'cpf')),
            telefone: DadosDeEntrada::texto($dados, 'telefone'),
        );
    }
}
