import { cpfValido, dataIsoValida } from "@/lib/documentos";
import { DomainError } from "@/core/errors/domain-error";
import { parseValorPtBr } from "@/lib/numeros";
import type { FiadorContrato } from "../data/contrato-types";

export type ContractPartyMode = "existente" | "novo";

export interface NewTenantDraft {
  nome: string;
  documento: string;
  rg: string;
  email: string;
  telefone: string;
  dataNascimento: string;
  estadoCivil: string;
  profissao?: string;
  renda: string;
  cep: string;
  logradouro: string;
  numero: string;
  bairro: string;
  cidade: string;
  estado: string;
}

export interface ContractDraft {
  partyMode: ContractPartyMode;
  existingTenantId: string;
  newTenant: NewTenantDraft;
  guaranteeType: string;
  depositAmount?: string;
  guarantors: FiadorContrato[];
  attachmentStatus: "idle" | "uploading" | "done";
  termType: string;
  startDate: string;
  endDate: string;
}

export type ContractFieldName =
  | "imovel"
  | "modelo"
  | "locador"
  | "inquilino"
  | "startDate"
  | "endDate"
  | "vigencia"
  | "desconto"
  | "reajuste"
  | "multaAtraso"
  | "multaRescisao"
  | "observacoes"
  | "novoInquilino"
  | "guarantees"
  | "upload";

export type ContractFieldErrors = Partial<Record<ContractFieldName, string>>;

function emailValido(valor: string): boolean {
  return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(valor.trim());
}

export function validateContractDraft(
  draft: ContractDraft,
  resolved: {
    hasProperty: boolean;
    hasTemplate: boolean;
    hasLocador: boolean;
  },
): void {
  const errors: ContractFieldErrors = {};

  if (!resolved.hasProperty) errors.imovel = "Selecione um imóvel";
  if (!resolved.hasTemplate) errors.modelo = "Selecione o modelo do contrato";
  if (!resolved.hasLocador) {
    errors.locador = "Cadastre o nome do proprietário nos dados do imóvel";
  }
  if (!draft.termType) {
    errors.startDate = "Informe o prazo da vigência";
  }
  if (termoRequerDates(draft) && !draft.startDate) {
    errors.startDate = "Informe o início da vigência";
  }
  if (draft.termType === "Determinado" && !draft.endDate) {
    errors.endDate = "Informe o fim da vigência";
  }
  if (
    draft.termType === "Determinado" &&
    draft.startDate &&
    draft.endDate &&
    draft.endDate <= draft.startDate
  ) {
    errors.endDate = "O fim deve ser posterior ao início da vigência";
  }
  if (draft.startDate && !dataIsoValida(draft.startDate)) errors.startDate = "Data de início inválida";
  if (draft.endDate && !dataIsoValida(draft.endDate)) errors.endDate = "Data de término inválida";
  if (draft.guaranteeType === "Caução" && (parseValorPtBr(draft.depositAmount ?? "") ?? 0) <= 0) {
    errors.guarantees = "Informe o valor da caução.";
  }
  if (draft.partyMode === "existente" && !draft.existingTenantId) {
    errors.inquilino = "Selecione um inquilino";
  }
  if (
    draft.partyMode === "novo" &&
    (!draft.newTenant.nome.trim() ||
      !cpfValido(draft.newTenant.documento) ||
      !emailValido(draft.newTenant.email) ||
      !draft.newTenant.telefone.trim() ||
      !draft.newTenant.dataNascimento ||
      !draft.newTenant.estadoCivil ||
      !((parseValorPtBr(draft.newTenant.renda) ?? -1) >= 0))
  ) {
    errors.novoInquilino =
      "Confira nome, CPF, contato, data de nascimento, estado civil e renda do novo inquilino.";
  }
  if (draft.guaranteeType === "Fiador" && draft.guarantors.length === 0) {
    errors.guarantees = "Adicione ao menos 1 fiador.";
  }
  if (
    draft.guaranteeType === "Fiador" &&
    draft.guarantors.some(
      (fiador) =>
        (fiador.nome.trim() || fiador.cpf.trim()) &&
        (!fiador.nome.trim() ||
          !cpfValido(fiador.cpf) ||
          !fiador.telefone.trim()),
    )
  ) {
    errors.guarantees =
      "Complete nome, CPF (11 dígitos) e telefone de cada fiador.";
  }
  if (
    (draft.guaranteeType === "Caução" || draft.guaranteeType === "Fiador") &&
    draft.attachmentStatus !== "done"
  ) {
    errors.upload = "Anexe o comprovante (renda ou depósito).";
  }

  if (Object.keys(errors).length > 0) {
    throw new DomainError(
      "Verifique os campos obrigatórios do contrato.",
      "INVALID_CONTRACT_DRAFT",
      errors,
    );
  }
}

function termoRequerDates(draft: ContractDraft): boolean {
  return draft.termType === "Determinado" || draft.termType === "Indeterminado";
}
