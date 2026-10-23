<?php

declare(strict_types=1);

namespace App\Modules\Usuarios\DTOs\Input;

use App\Shared\Application\Support\DadosDeEntrada;
use App\Shared\Domain\Enums\Papel;

final readonly class CriarUsuarioData
{
    public function __construct(
        public string $nome,
        public string $email,
        public string $senha,
        public Papel $papel,
    ) {}

    /**
     * @param  array<string, mixed>  $validated
     */
    public static function fromValidated(array $validated): self
    {
        return new self(
            nome: DadosDeEntrada::texto($validated, 'nome'),
            email: DadosDeEntrada::texto($validated, 'email'),
            senha: DadosDeEntrada::texto($validated, 'senha'),
            papel: Papel::from(DadosDeEntrada::texto($validated, 'papel')),
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function paraAtributos(): array
    {
        return [
            'nome' => $this->nome,
            'email' => $this->email,
            'password' => $this->senha,
            'papel' => $this->papel->value,
        ];
    }
}
