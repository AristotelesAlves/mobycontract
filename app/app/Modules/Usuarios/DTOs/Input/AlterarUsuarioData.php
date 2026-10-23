<?php

declare(strict_types=1);

namespace App\Modules\Usuarios\DTOs\Input;

use App\Shared\Application\Support\DadosDeEntrada;
use App\Shared\Domain\Enums\Papel;

final readonly class AlterarUsuarioData
{
    public function __construct(
        public int $id,
        public ?string $nome = null,
        public ?string $email = null,
        public ?Papel $papel = null,
        public ?bool $ativo = null,
    ) {}

    /**
     * @param  array<string, mixed>  $validated
     */
    public static function fromValidated(array $validated): self
    {
        return new self(
            id: DadosDeEntrada::inteiro($validated, 'id'),
            nome: DadosDeEntrada::existe($validated, 'nome') ? DadosDeEntrada::texto($validated, 'nome') : null,
            email: DadosDeEntrada::existe($validated, 'email') ? DadosDeEntrada::texto($validated, 'email') : null,
            papel: DadosDeEntrada::existe($validated, 'papel')
                ? Papel::from(DadosDeEntrada::texto($validated, 'papel'))
                : null,
            ativo: DadosDeEntrada::booleanoOpcional($validated, 'ativo'),
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function paraAtributos(): array
    {
        $atributos = [];

        if ($this->nome !== null) {
            $atributos['nome'] = $this->nome;
        }

        if ($this->email !== null) {
            $atributos['email'] = $this->email;
        }

        if ($this->papel instanceof Papel) {
            $atributos['papel'] = $this->papel->value;
        }

        if ($this->ativo !== null) {
            $atributos['ativo'] = $this->ativo;
        }

        return $atributos;
    }
}
