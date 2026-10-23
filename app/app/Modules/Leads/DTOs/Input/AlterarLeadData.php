<?php

declare(strict_types=1);

namespace App\Modules\Leads\DTOs\Input;

use App\Modules\Leads\Domain\Enums\OrigemLead;
use App\Modules\Leads\DTOs\Output\LeadResult;
use App\Shared\Application\Support\DadosDeEntrada;

final readonly class AlterarLeadData
{
    /**
     * @param  array<string, mixed>  $campos
     */
    private function __construct(
        public int $id,
        private array $campos,
    ) {}

    /**
     * @param  array<string, mixed>  $validated
     */
    public static function fromValidated(array $validated): self
    {
        $id = DadosDeEntrada::inteiro($validated, 'id');

        unset($validated['id'], $validated['status']);

        return new self($id, $validated);
    }

    /**
     * R-12.6.3: `status` não é alterado por esta operação.
     *
     * @return array<string, mixed>
     */
    public function paraAtributosSobre(LeadResult $atual): array
    {
        return [
            'nome' => $this->texto('nome') ?? $atual->nome,
            'telefone' => $this->texto('telefone') ?? $atual->telefone,
            'email' => $this->informado('email') ? $this->textoOpcional('email') : $atual->email,
            'imovel_de_interesse' => $this->informado('imovelDeInteresse')
                ? $this->textoOpcional('imovelDeInteresse')
                : $atual->imovelDeInteresse,
            'origem' => $this->origem()->value ?? $atual->origem,
            'observacoes' => $this->informado('observacoes')
                ? $this->textoOpcional('observacoes')
                : $atual->observacoes,
        ];
    }

    private function informado(string $chave): bool
    {
        return DadosDeEntrada::existe($this->campos, $chave);
    }

    private function texto(string $chave): ?string
    {
        return $this->informado($chave) ? DadosDeEntrada::textoOpcional($this->campos, $chave) : null;
    }

    private function textoOpcional(string $chave): ?string
    {
        return DadosDeEntrada::textoOpcional($this->campos, $chave);
    }

    private function origem(): ?OrigemLead
    {
        $valor = $this->texto('origem');

        return $valor === null ? null : OrigemLead::from($valor);
    }
}
