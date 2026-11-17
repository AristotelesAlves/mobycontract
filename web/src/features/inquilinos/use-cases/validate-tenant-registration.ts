import { cpfValido, dataIsoValida } from "@/lib/documentos";
import { DomainError } from "@/core/errors/domain-error";
import { parseValorPtBr } from "@/lib/numeros";
import type { TenantContractsPayload } from "../data/tenant-api-types";

export interface TenantRegistrationDraft {
  nome: string;
  cpf: string;
  rg: string;
  email: string;
  telefone: string;
  dataNascimento: string;
  estadoCivil: string;
  profissao: string;
  renda: string;
  endereco: string;
}

export type TenantFieldName =
  | "nome"
  | "cpf"
  | "rg"
  | "email"
  | "telefone"
  | "dataNascimento"
  | "estadoCivil"
  | "profissao"
  | "renda"
  | "endereco";

export type TenantFieldErrors = Partial<Record<TenantFieldName, string>>;

export interface TenantRegistrationContext {
  todayISO: string;
}

const MINIMUM_TENANT_AGE = 18;

export function normalizeCpf(cpf: string): string {
  return cpf.replace(/\D/g, "");
}

function emailIsValid(email: string): boolean {
  return /^\S+@\S+\.\S+$/.test(email);
}

function telephoneHasContact(telefone: string): boolean {
  return telefone.replace(/\D/g, "").length >= 8;
}

function completedYearsAt(
  birthDate: string,
  referenceDateISO: string,
): number | null {
  if (!dataIsoValida(birthDate) || !dataIsoValida(referenceDateISO)) return null;
  const birth = new Date(`${birthDate}T00:00:00`);
  if (Number.isNaN(birth.getTime())) return null;

  const reference = new Date(`${referenceDateISO}T00:00:00`);
  if (Number.isNaN(reference.getTime())) return null;

  let age = reference.getFullYear() - birth.getFullYear();
  const monthsUntilBirthday =
    reference.getMonth() - birth.getMonth() ||
    reference.getDate() - birth.getDate();

  if (monthsUntilBirthday < 0) {
    age -= 1;
  }

  return age;
}

function rulesReport(
  draft: TenantRegistrationDraft,
  todayISO: string,
): TenantFieldErrors {
  const fieldIssues: TenantFieldErrors = {};

  if (!draft.nome.trim()) {
    fieldIssues.nome = "O nome é obrigatório";
  }

  if (!cpfValido(draft.cpf)) {
    fieldIssues.cpf = "Informe um CPF válido";
  }

  if (!emailIsValid(draft.email.trim())) {
    fieldIssues.email = "Informe um e-mail válido";
  }

  if (!telephoneHasContact(draft.telefone)) {
    fieldIssues.telefone = "Informe um telefone válido";
  }

  const idade = draft.dataNascimento
    ? completedYearsAt(draft.dataNascimento, todayISO)
    : null;

  if (!draft.dataNascimento) {
    fieldIssues.dataNascimento = "A data de nascimento é obrigatória";
  }
  if (draft.dataNascimento && idade === null) {
    fieldIssues.dataNascimento = "Data de nascimento inválida";
  }
  if (idade !== null && idade < MINIMUM_TENANT_AGE) {
    fieldIssues.dataNascimento = "O locatário deve ser maior de 18 anos";
  }

  if (!draft.estadoCivil.trim()) {
    fieldIssues.estadoCivil = "Selecione o estado civil";
  }

  const renda = parseValorPtBr(draft.renda);
  if (renda === null || renda < 0) {
    fieldIssues.renda = "Informe a renda em reais (ex.: 1.500,00)";
  }


  return fieldIssues;
}

export function validateTenantRegistration(
  draft: TenantRegistrationDraft,
  context: TenantRegistrationContext,
): void {
  const fieldIssues = rulesReport(draft, context.todayISO);
  if (Object.keys(fieldIssues).length > 0) {
    throw new DomainError(
      "Verifique os campos obrigatórios.",
      "INVALID_TENANT_REGISTRATION",
      fieldIssues,
    );
  }
}

export function buildTenantPayload(
  draft: TenantRegistrationDraft,
): TenantContractsPayload {
  return {
    nome: draft.nome.trim(),
    cpf: normalizeCpf(draft.cpf),
    rg: draft.rg.trim() || undefined,
    email: draft.email.trim(),
    telefone: draft.telefone.trim(),
    dataNascimento: draft.dataNascimento,
    estadoCivil: draft.estadoCivil.trim(),
    profissao: draft.profissao.trim() || undefined,
    renda: parseValorPtBr(draft.renda) ?? 0,
  };
}
