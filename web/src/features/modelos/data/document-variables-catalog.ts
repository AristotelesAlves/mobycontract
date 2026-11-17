import type { DocumentVariableCatalog, DocumentVariableValues } from "@/lib/tiptap/document-variables";
// Vocabulário de App\Modules\Modelos\Domain\CatalogoDeVariaveis.
export const DOCUMENTO_VARIAVEIS_CATALOGO: DocumentVariableCatalog = [
    {
        "group": "Imóvel",
        "items": [
            {
                "path": "imovel.titulo",
                "label": "Título do imóvel"
            },
            {
                "path": "imovel.endereco",
                "label": "Endereço formatado"
            },
            {
                "path": "imovel.cidade",
                "label": "Cidade"
            },
            {
                "path": "imovel.matricula",
                "label": "Matrícula"
            },
            {
                "path": "imovel.valor",
                "label": "Valor de venda"
            },
            {
                "path": "imovel.valorAluguel",
                "label": "Valor do aluguel"
            },
            {
                "path": "imovel.area",
                "label": "Área"
            },
            {
                "path": "imovel.quartos",
                "label": "Quartos"
            },
            {
                "path": "imovel.banheiros",
                "label": "Banheiros"
            },
            {
                "path": "imovel.vagasGaragem",
                "label": "Vagas"
            }
        ]
    },
    {
        "group": "Inquilino",
        "items": [
            {
                "path": "cliente.nome",
                "label": "Nome do locatário"
            },
            {
                "path": "cliente.cpf",
                "label": "CPF do locatário"
            },
            {
                "path": "cliente.email",
                "label": "E-mail do locatário"
            },
            {
                "path": "cliente.telefone",
                "label": "Telefone do locatário"
            },
            {
                "path": "cliente.renda",
                "label": "Renda declarada"
            }
        ]
    },
    {
        "group": "Contrato",
        "items": [
            {
                "path": "contrato.numero",
                "label": "Número do contrato"
            },
            {
                "path": "contrato.dataInicio",
                "label": "Início formatado"
            },
            {
                "path": "contrato.dataFim",
                "label": "Término formatado"
            },
            {
                "path": "contrato.valorMensal",
                "label": "Aluguel formatado"
            },
            {
                "path": "contrato.multaPorAtraso",
                "label": "Multa por atraso"
            },
            {
                "path": "contrato.multaRescisao",
                "label": "Multa de rescisão"
            },
            {
                "path": "contrato.garantia",
                "label": "Tipo de garantia"
            },
            {
                "path": "contrato.valorCaucao",
                "label": "Valor da caução"
            },
            {
                "path": "contrato.indiceReajuste",
                "label": "Índice de reajuste"
            }
        ]
    },
    {
        "group": "Aditivo",
        "items": [
            {
                "path": "aditivo.numero",
                "label": "Número do aditivo"
            },
            {
                "path": "aditivo.title",
                "label": "Título do aditivo"
            },
            {
                "path": "aditivo.data",
                "label": "Data do aditivo"
            },
            {
                "path": "aditivo.descricao",
                "label": "Descrição do aditivo"
            }
        ]
    }
];
// Valores ilustrativos usados somente na prévia do editor de modelos.
export const DOCUMENTO_VARIAVEIS_DE_EXEMPLO: DocumentVariableValues = {
    "imovel": {
        "titulo": "Apartamento 302",
        "endereco": "Rua X, 100, Centro",
        "cidade": "Juazeiro do Norte - CE",
        "matricula": "48.221",
        "valor": "R$ 320.000,00",
        "valorAluguel": "R$ 2.400,00",
        "area": "70",
        "quartos": "2",
        "banheiros": "1",
        "vagasGaragem": "1"
    },
    "cliente": {
        "nome": "Maria Oliveira Santos",
        "cpf": "123.456.789-00",
        "email": "maria@exemplo.com",
        "telefone": "(88) 99999-0000",
        "renda": "R$ 9.000,00"
    },
    "contrato": {
        "numero": "2026/6005",
        "dataInicio": "01/06/2026",
        "dataFim": "31/05/2027",
        "valorMensal": "R$ 3.500,00",
        "multaPorAtraso": "2",
        "multaRescisao": "3",
        "garantia": "Caução",
        "valorCaucao": "R$ 7.000,00",
        "indiceReajuste": "IPCA"
    },
    "aditivo": {
        "numero": "ADT 001/2026",
        "title": "Reajuste anual",
        "data": "01/03/2026",
        "descricao": "Texto livre"
    }
};
