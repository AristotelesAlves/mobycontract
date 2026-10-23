<?php

declare(strict_types=1);

namespace App\Modules\Imoveis\DTOs\Input;

use App\Modules\Imoveis\Domain\CompositorDeEndereco;
use App\Modules\Imoveis\Domain\Enums\TipoImovel;
use App\Modules\Imoveis\DTOs\Output\ImovelResult;
use App\Shared\Application\Support\DadosDeEntrada;

final readonly class AlterarImovelData
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
     * R-12.4.2: `status` é ignorado na alteração. R-3.1.1.2: `endereco` é recomposto.
     *
     * @return array<string, mixed>
     */
    public function paraAtributosSobre(ImovelResult $atual): array
    {
        $logradouro = $this->texto('logradouro') ?? $atual->logradouro;
        $numero = $this->texto('numero') ?? $atual->numero;
        $complemento = $this->informado('complemento') ? $this->textoOpcional('complemento') : $atual->complemento;
        $bairro = $this->informado('bairro') ? $this->textoOpcional('bairro') : $atual->bairro;

        return [
            'titulo' => $this->texto('titulo') ?? $atual->titulo,
            'nome_proprietario' => $this->informado('nomeProprietario') ? $this->textoOpcional('nomeProprietario') : $atual->nomeProprietario,
            'tipo' => $this->tipo()->value ?? $atual->tipo,
            'cep' => $this->texto('cep') ?? $atual->cep,
            'logradouro' => $logradouro,
            'numero' => $numero,
            'complemento' => $complemento,
            'bairro' => $bairro,
            'cidade' => $this->texto('cidade') ?? $atual->cidade,
            'estado' => $this->informado('estado') ? $this->textoOpcional('estado') : $atual->estado,
            'endereco' => CompositorDeEndereco::compor($logradouro, $numero, $complemento, $bairro),
            'area' => $this->decimal('area') ?? $atual->area,
            'quartos' => $this->inteiro('quartos') ?? $atual->quartos,
            'banheiros' => $this->inteiro('banheiros') ?? $atual->banheiros,
            'vagas_garagem' => $this->inteiro('vagasGaragem') ?? $atual->vagasGaragem,
            'valor_aluguel' => $this->decimal('valorAluguel') ?? $atual->valorAluguel,
            'valor_iptu' => $this->informado('valorIptu') ? $this->decimal('valorIptu') : $atual->valorIptu,
            'valor_condominio' => $this->informado('valorCondominio') ? $this->decimal('valorCondominio') : $atual->valorCondominio,
            'agua_inclusa' => $this->booleano('aguaInclusa') ?? $atual->aguaInclusa,
            'energia_inclusa' => $this->booleano('energiaInclusa') ?? $atual->energiaInclusa,
            'internet_inclusa' => $this->booleano('internetInclusa') ?? $atual->internetInclusa,
            'gas_incluso' => $this->booleano('gasIncluso') ?? $atual->gasIncluso,
            'matricula' => $this->informado('matricula') ? $this->textoOpcional('matricula') : $atual->matricula,
            'descricao' => $this->informado('descricao') ? $this->textoOpcional('descricao') : $atual->descricao,
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

    private function decimal(string $chave): ?float
    {
        return $this->informado($chave) ? DadosDeEntrada::decimalOpcional($this->campos, $chave) : null;
    }

    private function inteiro(string $chave): ?int
    {
        return $this->informado($chave) ? DadosDeEntrada::inteiro($this->campos, $chave) : null;
    }

    private function booleano(string $chave): ?bool
    {
        return $this->informado($chave) ? DadosDeEntrada::booleano($this->campos, $chave) : null;
    }

    private function tipo(): ?TipoImovel
    {
        $valor = $this->texto('tipo');

        return $valor === null ? null : TipoImovel::from($valor);
    }
}
