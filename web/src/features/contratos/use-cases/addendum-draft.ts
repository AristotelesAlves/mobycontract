import { DomainError } from "@/core/errors/domain-error";
import type {
  ContratoAditivo,
  MotivoAditivo,
} from "../data/contrato-types";

export interface AddendumExtraDatum {
  label: string;
  valor: string;
}

export interface AddendumDraft {
  titulo: string;
  data: string;
  descricao: string;
  motivos: MotivoAditivo[];
  modeloId: string;
  novoValor: string;
  novaDataFim: string;
  clausulaModificada: string;
  novaTaxaCondominio: string;
  novoIPTU: string;
  novoSeguroIncendio: string;
  novoAgua: string;
  novoEnergia: string;
}

export type AddendumFieldName = "titulo" | "modelo" | "motivos";

export type AddendumFieldErrors = Partial<Record<AddendumFieldName, string>>;

export const EMPTY_ADDENDUM_DRAFT: AddendumDraft = {
  titulo: "",
  data: "",
  descricao: "",
  motivos: [],
  modeloId: "",
  novoValor: "",
  novaDataFim: "",
  clausulaModificada: "",
  novaTaxaCondominio: "",
  novoIPTU: "",
  novoSeguroIncendio: "",
  novoAgua: "",
  novoEnergia: "",
};

export function toggleAddendumReason(
  reasons: MotivoAditivo[],
  reason: MotivoAditivo,
): MotivoAditivo[] {
  return reasons.includes(reason)
    ? reasons.filter((current) => current !== reason)
    : [...reasons, reason];
}

export function validateAddendumDraft(draft: AddendumDraft): void {
  const errors: AddendumFieldErrors = {};
  if (!draft.titulo.trim()) errors.titulo = "Informe o título do aditivo";
  if (!draft.modeloId) errors.modelo = "Selecione o modelo";
  if (draft.motivos.length === 0) {
    errors.motivos = "Selecione pelo menos um motivo";
  }

  if (Object.keys(errors).length > 0) {
    throw new DomainError(
      "Confira os campos obrigatórios do aditivo.",
      "INVALID_ADDENDUM_DRAFT",
      errors,
    );
  }
}

export function buildAddendumExtraData(draft: AddendumDraft): AddendumExtraDatum[] {
  const data: AddendumExtraDatum[] = [];
  const { motivos } = draft;

  if (motivos.includes("Valor") && draft.novoValor) {
    data.push({ label: "Novo Valor Mensal", valor: `R$ ${draft.novoValor}` });
  }
  if (motivos.includes("Vigência") && draft.novaDataFim) {
    data.push({ label: "Nova Data de Término", valor: draft.novaDataFim });
  }
  if (motivos.includes("Cláusula") && draft.clausulaModificada) {
    data.push({ label: "Cláusula Modificada", valor: draft.clausulaModificada });
  }
  if (motivos.includes("Taxa de Condomínio") && draft.novaTaxaCondominio) {
    data.push({
      label: "Nova Taxa de Condomínio",
      valor: `R$ ${draft.novaTaxaCondominio}`,
    });
  }
  if (motivos.includes("IPTU") && draft.novoIPTU) {
    data.push({ label: "Novo IPTU", valor: `R$ ${draft.novoIPTU}` });
  }
  if (motivos.includes("Seguro Incêndio") && draft.novoSeguroIncendio) {
    data.push({
      label: "Novo Seguro Incêndio",
      valor: `R$ ${draft.novoSeguroIncendio}`,
    });
  }
  if (motivos.includes("Água") && draft.novoAgua) {
    data.push({ label: "Novo Valor Água", valor: `R$ ${draft.novoAgua}` });
  }
  if (motivos.includes("Energia") && draft.novoEnergia) {
    data.push({ label: "Novo Valor Energia", valor: `R$ ${draft.novoEnergia}` });
  }

  return data;
}

export function addendumDraftFromRecord(
  addendum: ContratoAditivo,
): AddendumDraft {
  const draft: AddendumDraft = {
    ...EMPTY_ADDENDUM_DRAFT,
    titulo: addendum.titulo,
    data: addendum.data,
    descricao: addendum.descricao ?? "",
    motivos: addendum.motivos,
    modeloId: addendum.modeloId ?? "",
  };

  const currencyLabels = [
    "Novo Valor Mensal",
    "Nova Taxa de Condomínio",
    "Novo IPTU",
    "Novo Seguro Incêndio",
    "Novo Valor Água",
    "Novo Valor Energia",
  ];

  type CurrencyFree = Exclude<
    keyof AddendumDraft,
    "titulo" | "data" | "descricao" | "motivos" | "modeloId"
  >;

  const fields: Record<string, CurrencyFree> = {
    "Novo Valor Mensal": "novoValor",
    "Nova Data de Término": "novaDataFim",
    "Cláusula Modificada": "clausulaModificada",
    "Nova Taxa de Condomínio": "novaTaxaCondominio",
    "Novo IPTU": "novoIPTU",
    "Novo Seguro Incêndio": "novoSeguroIncendio",
    "Novo Valor Água": "novoAgua",
    "Novo Valor Energia": "novoEnergia",
  };

  const extrasArray = normalizeAddendumExtraData(addendum.dadosExtras);

  for (const datum of extrasArray) {
    const field = fields[datum.label];
    if (!field) continue;
    draft[field] = currencyLabels.includes(datum.label)
      ? datum.valor.replace("R$ ", "")
      : datum.valor;
  }

  return draft;
}
export function normalizeAddendumExtraData(
  extras: ContratoAditivo["dadosExtras"],
): AddendumExtraDatum[] {
  if (!extras) return [];
  if (!Array.isArray(extras)) {
    return Object.entries(extras).map(([label, valor]) => ({ label, valor: String(valor) }));
  }
  return extras.map((datum) => ({
    label: "rotulo" in datum ? String(datum.rotulo) : datum.label,
    valor: String(datum.valor),
  }));
}
