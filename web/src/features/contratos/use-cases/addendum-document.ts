import { normalizeAddendumExtraData } from "./addendum-draft";
import { escapeDocumentTextForHtml } from "@/lib/tiptap/document-variables";
import { formatCurrency, formatDate } from "@/lib/formatters";
import type { IModeloClonar } from "@/features/modelos/data/modelo-types";
import type { Contrato, ContratoAditivo } from "../data/contrato-types";

export const ADDENDUM_FALLBACK_HTML = `
<h1>Termo aditivo ao contrato de locação</h1>
<p>Fica aditado o contrato <strong>{{contrato.numero}}</strong>, firmado entre {{cliente.nome}} e a imobiliária, referente ao imóvel {{imovel.titulo}}, situado em {{imovel.endereco}}, {{imovel.cidade}}.</p>
<h2>Do objeto do aditivo</h2>
<p>{{aditivo.descricao}}</p>
<h2>Dos demais termos</h2>
<p>Aditivo número <strong>{{aditivo.numero}}</strong>, datado de {{aditivo.data}}. Mantidas as demais cláusulas do contrato em tudo que não contrariar o presente aditivo.</p>
`;

export type AddendumTemplate = {
  uuid: string;
  titulo: string;
  conteudo: string;
  tipo: string;
  versao: number;
  garantia?: string;
};

export function resolveAddendumTemplate(
  modeloId?: string,
  templates: IModeloClonar[] = [],
): AddendumTemplate {
  const byId = modeloId
    ? templates.find((model) => model.uuid === modeloId)
    : undefined;

  const template = byId ?? templates.find((model) => model.tipo === "Aditivo");
  const base = template ?? templates[0];

  return {
    uuid: base?.uuid ?? "",
    titulo: base?.titulo ?? "",
    conteudo: base?.conteudoHtml ?? ADDENDUM_FALLBACK_HTML,
    tipo: base?.tipo ?? "",
    versao: base?.versao ?? 1,
    garantia: base?.garantia,
  };
}

export function buildAddendumDocumentValues(
  contract: Contrato,
  addendum: ContratoAditivo,
) {
  const tenantName = contract.inquilinoNome ?? "";

  return {
    aditivo: {
      numero: addendum.numero,
      title: addendum.titulo,
      data: formatDate(addendum.data),
      descricao: addendum.descricao,
    },
    contrato: {
      numero: contract.numero,
      dataInicio: formatDate(contract.dataInicio),
      dataFim: formatDate(contract.dataFim),
      valorMensal: formatCurrency(contract.valorAluguel),
    },
    imovel: {
      titulo: contract.imovelTitulo,
      title: contract.imovelTitulo,
      endereco: contract.imovelEndereco,
      cidade: contract.imovelCidade,
      matricula: contract.imovelMatricula,
    },
    cliente: contract.snapshot?.inquilino ?? { nome: tenantName },
  };
}

export function addendumChangesHtml(addendum: ContratoAditivo): string {
  const extras = normalizeAddendumExtraData(addendum.dadosExtras);
  if (!extras.length) return "";
  return `<h2>Alterações previstas neste aditivo</h2><dl>${extras.map(({ label, valor }) =>
    `<dt><strong>${escapeDocumentTextForHtml(label)}</strong></dt><dd>${escapeDocumentTextForHtml(label === "Nova Data de Término" ? formatDate(valor) : valor)}</dd>`,
  ).join("")}</dl>`;
}
