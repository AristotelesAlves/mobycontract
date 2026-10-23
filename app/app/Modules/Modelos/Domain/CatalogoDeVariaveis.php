<?php

declare(strict_types=1);

namespace App\Modules\Modelos\Domain;

/**
 * Catálogo de variáveis aceitas nos documentos (R-8.2).
 */
final class CatalogoDeVariaveis
{
    /**
     * @return list<array{caminho: string, origem: string, exemplo: string}>
     */
    public static function conhecidos(): array
    {
        return [
            ['caminho' => 'imovel.titulo', 'origem' => 'Título do imóvel', 'exemplo' => 'Apartamento 302'],
            ['caminho' => 'imovel.endereco', 'origem' => 'Endereço formatado', 'exemplo' => 'Rua X, 100, Centro'],
            ['caminho' => 'imovel.cidade', 'origem' => 'Cidade', 'exemplo' => 'Juazeiro do Norte - CE'],
            ['caminho' => 'imovel.matricula', 'origem' => 'Matrícula', 'exemplo' => '48.221'],
            ['caminho' => 'imovel.valor', 'origem' => 'Valor de venda', 'exemplo' => 'R$ 320.000,00'],
            ['caminho' => 'imovel.valorAluguel', 'origem' => 'Valor do aluguel', 'exemplo' => 'R$ 2.400,00'],
            ['caminho' => 'imovel.area', 'origem' => 'Área', 'exemplo' => '70'],
            ['caminho' => 'imovel.quartos', 'origem' => 'Quartos', 'exemplo' => '2'],
            ['caminho' => 'imovel.banheiros', 'origem' => 'Banheiros', 'exemplo' => '1'],
            ['caminho' => 'imovel.vagasGaragem', 'origem' => 'Vagas', 'exemplo' => '1'],
            ['caminho' => 'cliente.nome', 'origem' => 'Nome do locatário', 'exemplo' => 'Maria Oliveira Santos'],
            ['caminho' => 'cliente.cpf', 'origem' => 'CPF do locatário', 'exemplo' => '123.456.789-00'],
            ['caminho' => 'cliente.email', 'origem' => 'E-mail do locatário', 'exemplo' => 'maria@exemplo.com'],
            ['caminho' => 'cliente.telefone', 'origem' => 'Telefone do locatário', 'exemplo' => '(88) 99999-0000'],
            ['caminho' => 'cliente.renda', 'origem' => 'Renda declarada', 'exemplo' => 'R$ 9.000,00'],
            ['caminho' => 'contrato.numero', 'origem' => 'Número do contrato', 'exemplo' => '2026/6005'],
            ['caminho' => 'contrato.dataInicio', 'origem' => 'Início formatado', 'exemplo' => '01/06/2026'],
            ['caminho' => 'contrato.dataFim', 'origem' => 'Término formatado', 'exemplo' => '31/05/2027'],
            ['caminho' => 'contrato.valorMensal', 'origem' => 'Aluguel formatado', 'exemplo' => 'R$ 3.500,00'],
            ['caminho' => 'contrato.multaPorAtraso', 'origem' => 'Multa por atraso', 'exemplo' => '2'],
            ['caminho' => 'contrato.multaRescisao', 'origem' => 'Multa de rescisão', 'exemplo' => '3'],
            ['caminho' => 'contrato.garantia', 'origem' => 'Tipo de garantia', 'exemplo' => 'Caução'],
            ['caminho' => 'contrato.valorCaucao', 'origem' => 'Valor da caução', 'exemplo' => 'R$ 7.000,00'],
            ['caminho' => 'contrato.indiceReajuste', 'origem' => 'Índice de reajuste', 'exemplo' => 'IPCA'],
            ['caminho' => 'aditivo.numero', 'origem' => 'Número do aditivo', 'exemplo' => 'ADT 001/2026'],
            ['caminho' => 'aditivo.title', 'origem' => 'Título do aditivo', 'exemplo' => 'Reajuste anual'],
            ['caminho' => 'aditivo.data', 'origem' => 'Data do aditivo', 'exemplo' => '01/03/2026'],
            ['caminho' => 'aditivo.descricao', 'origem' => 'Descrição do aditivo', 'exemplo' => 'Texto livre'],
        ];
    }

    /**
     * @return list<string>
     */
    public static function caminhos(): array
    {
        return array_map(
            static fn (array $variavel): string => $variavel['caminho'],
            self::conhecidos(),
        );
    }

    public static function conhecido(string $caminho): bool
    {
        return in_array($caminho, self::caminhos(), true);
    }
}
