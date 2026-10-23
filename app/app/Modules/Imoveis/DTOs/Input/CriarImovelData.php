<?php

declare(strict_types=1);

namespace App\Modules\Imoveis\DTOs\Input;

use App\Modules\Imoveis\Domain\CompositorDeEndereco;
use App\Modules\Imoveis\Domain\Enums\TipoImovel;
use App\Shared\Application\Support\DadosDeEntrada;

final readonly class CriarImovelData
{
    public function __construct(
        public string $titulo,
        public TipoImovel $tipo,
        public string $cep,
        public string $logradouro,
        public string $numero,
        public ?string $complemento,
        public ?string $bairro,
        public string $cidade,
        public ?string $estado,
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
        public ?string $nomeProprietario = null,
    ) {}

    /**
     * @param  array<string, mixed>  $validated
     */
    public static function fromValidated(array $validated): self
    {
        return new self(
            titulo: DadosDeEntrada::texto($validated, 'titulo'),
            tipo: TipoImovel::from(DadosDeEntrada::texto($validated, 'tipo')),
            cep: DadosDeEntrada::texto($validated, 'cep'),
            logradouro: DadosDeEntrada::texto($validated, 'logradouro'),
            numero: DadosDeEntrada::texto($validated, 'numero'),
            complemento: DadosDeEntrada::textoOpcional($validated, 'complemento'),
            bairro: DadosDeEntrada::textoOpcional($validated, 'bairro'),
            cidade: DadosDeEntrada::texto($validated, 'cidade'),
            estado: DadosDeEntrada::textoOpcional($validated, 'estado'),
            area: DadosDeEntrada::decimal($validated, 'area'),
            quartos: DadosDeEntrada::inteiro($validated, 'quartos'),
            banheiros: DadosDeEntrada::inteiro($validated, 'banheiros'),
            vagasGaragem: DadosDeEntrada::inteiro($validated, 'vagasGaragem'),
            valorAluguel: DadosDeEntrada::decimal($validated, 'valorAluguel'),
            valorIptu: DadosDeEntrada::decimalOpcional($validated, 'valorIptu'),
            valorCondominio: DadosDeEntrada::decimalOpcional($validated, 'valorCondominio'),
            aguaInclusa: DadosDeEntrada::booleano($validated, 'aguaInclusa'),
            energiaInclusa: DadosDeEntrada::booleano($validated, 'energiaInclusa'),
            internetInclusa: DadosDeEntrada::booleano($validated, 'internetInclusa'),
            gasIncluso: DadosDeEntrada::booleano($validated, 'gasIncluso'),
            matricula: DadosDeEntrada::textoOpcional($validated, 'matricula'),
            descricao: DadosDeEntrada::textoOpcional($validated, 'descricao'),
            nomeProprietario: DadosDeEntrada::textoOpcional($validated, 'nomeProprietario'),
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function paraAtributos(): array
    {
        return [
            'nome_proprietario' => $this->nomeProprietario,
            'titulo' => $this->titulo,
            'tipo' => $this->tipo->value,
            'cep' => $this->cep,
            'logradouro' => $this->logradouro,
            'numero' => $this->numero,
            'complemento' => $this->complemento,
            'bairro' => $this->bairro,
            'cidade' => $this->cidade,
            'estado' => $this->estado,
            'endereco' => CompositorDeEndereco::compor(
                $this->logradouro,
                $this->numero,
                $this->complemento,
                $this->bairro,
            ),
            'area' => $this->area,
            'quartos' => $this->quartos,
            'banheiros' => $this->banheiros,
            'vagas_garagem' => $this->vagasGaragem,
            'valor_aluguel' => $this->valorAluguel,
            'valor_iptu' => $this->valorIptu,
            'valor_condominio' => $this->valorCondominio,
            'agua_inclusa' => $this->aguaInclusa,
            'energia_inclusa' => $this->energiaInclusa,
            'internet_inclusa' => $this->internetInclusa,
            'gas_incluso' => $this->gasIncluso,
            'matricula' => $this->matricula,
            'descricao' => $this->descricao,
        ];
    }
}
