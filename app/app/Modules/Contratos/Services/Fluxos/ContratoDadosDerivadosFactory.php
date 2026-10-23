<?php

declare(strict_types=1);

namespace App\Modules\Contratos\Services\Fluxos;

use App\Modules\Contratos\Domain\Enums\Destinacao;
use App\Modules\Contratos\Domain\Enums\PapelAssinatura;
use App\Modules\Contratos\DTOs\Input\CriarContratoData;
use App\Modules\Imoveis\DTOs\Output\ImovelResult;
use App\Shared\Domain\Enums\Garantia;

/**
 * Cria os dados de contrato que são derivados do imóvel e de suas partes.
 *
 * Centraliza esse conhecimento para que a criação do contrato não replique
 * regras de consumo, encargos e papéis de assinatura.
 */
final class ContratoDadosDerivadosFactory
{
    /** @return array{destinacao: string} */
    public function atributosDoImovel(ImovelResult $imovel): array
    {
        $destinacao = $imovel->tipo === 'Comercial' ? Destinacao::Comercial : Destinacao::Residencial;

        return ['destinacao' => $destinacao->value];
    }

    /** @return list<array{tipo: string, responsabilidade: string}> */
    public function utilidades(ImovelResult $imovel): array
    {
        return [
            ['tipo' => 'agua', 'responsabilidade' => $imovel->aguaInclusa ? 'Compartilhado' : 'Locatario'],
            ['tipo' => 'luz', 'responsabilidade' => $imovel->energiaInclusa ? 'Compartilhado' : 'Locatario'],
            ['tipo' => 'gas', 'responsabilidade' => $imovel->gasIncluso ? 'Compartilhado' : 'Locatario'],
            ['tipo' => 'internet', 'responsabilidade' => $imovel->internetInclusa ? 'Compartilhado' : 'Locatario'],
        ];
    }

    /** @return list<array{tipo: string, responsabilidade: string, valor_estimado: ?float}> */
    public function encargosFixos(ImovelResult $imovel): array
    {
        return [
            ['tipo' => 'iptu', 'responsabilidade' => $this->responsabilidadeDaDespesa($imovel->valorIptu), 'valor_estimado' => $imovel->valorIptu],
            ['tipo' => 'condominio', 'responsabilidade' => $this->responsabilidadeDaDespesa($imovel->valorCondominio), 'valor_estimado' => $imovel->valorCondominio],
        ];
    }

    /** @return list<array{papel: string, nome: string}> */
    public function assinaturas(CriarContratoData $dados, string $nomeInquilino, Garantia $garantia, ?string $nomeProprietario): array
    {
        $assinaturas = [
            ['papel' => PapelAssinatura::Imobiliaria->value, 'nome' => 'Imobiliária'],
            ['papel' => PapelAssinatura::Locador->value, 'nome' => $nomeProprietario ?: $dados->nomeLocador],
            ['papel' => PapelAssinatura::Locatario->value, 'nome' => $nomeInquilino],
        ];

        if ($garantia !== Garantia::Fiador) {
            return $assinaturas;
        }

        foreach ($dados->fiadores as $fiador) {
            $assinaturas[] = ['papel' => PapelAssinatura::Fiador->value, 'nome' => $fiador->nome];
        }

        return $assinaturas;
    }

    private function responsabilidadeDaDespesa(?float $valor): string
    {
        return $valor !== null && $valor > 0 ? 'Locatario' : 'Locador';
    }
}
