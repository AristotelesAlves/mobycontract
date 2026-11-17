import { HttpError } from "@/core/errors/http-error";
import { DomainError } from "@/core/errors/domain-error";
import { parseValorPtBr } from "@/lib/numeros";
import type { ImovelContractsPayload } from "../data/imovel-api-types";
import type { ImovelTipo } from "../data/imovel-types";

export interface PropertyFormValues {
  nomeProprietario?: string;
  titulo: string;
  tipo: ImovelTipo | "";
  cep: string;
  logradouro: string;
  numero: string;
  complemento: string;
  bairro: string;
  cidade: string;
  estado: string;
  area: string;
  quartos: string;
  banheiros: string;
  vagasGaragem: string;
  valorAluguel: string;
  valorCondominio: string;
  valorIptu: string;
  matricula: string;
  descricao: string;
}

export type PropertyFieldName = keyof PropertyFormValues;

export type PropertyFieldErrors = Partial<Record<PropertyFieldName, string>>;

export interface PropertyInclusionFlags {
  aguaInclusa: boolean;
  energiaInclusa: boolean;
  gasIncluso: boolean;
  internetInclusa: boolean;
}

export const EMPTY_PROPERTY_FORM: PropertyFormValues = {
  nomeProprietario: "",
  titulo: "",
  tipo: "",
  cep: "",
  logradouro: "",
  numero: "",
  complemento: "",
  bairro: "",
  cidade: "",
  estado: "",
  area: "",
  quartos: "",
  banheiros: "",
  vagasGaragem: "",
  valorAluguel: "",
  valorCondominio: "",
  valorIptu: "",
  matricula: "",
  descricao: "",
};

export function validatePropertyForm(values: PropertyFormValues): void {
  const errors: PropertyFieldErrors = {};

  if (!values.titulo.trim()) {
    errors.titulo = "O título é obrigatório";
  }

  if (!values.tipo) {
    errors.tipo = "Selecione o tipo";
  }

  if (values.cep.replace(/\D/g, "").length !== 8) {
    errors.cep = "Informe um CEP válido com 8 dígitos";
  }

  if (!values.logradouro.trim()) {
    errors.logradouro = "Logradouro é obrigatório";
  }

  if (!values.numero.trim()) {
    errors.numero = "Número é obrigatório";
  }

  if (!values.cidade.trim()) {
    errors.cidade = "A cidade é obrigatória";
  }

  if (values.estado.trim() && values.estado.trim().length !== 2) {
    errors.estado = "UF deve ter 2 letras";
  }

  const area = parseValorPtBr(values.area);
  if (area === null || area <= 0) {
    errors.area = "Informe a área em m² (ex.: 120,50)";
  }

  const quartos = Number(values.quartos);
  if (!Number.isFinite(quartos) || quartos < 0) {
    errors.quartos = "Informe a quantidade de quartos";
  }

  const banheiros = Number(values.banheiros);
  if (!Number.isFinite(banheiros) || banheiros < 0) {
    errors.banheiros = "Informe a quantidade de banheiros";
  }

  const vagas = Number(values.vagasGaragem);
  if (!Number.isFinite(vagas) || vagas < 0) {
    errors.vagasGaragem = "Informe a quantidade de vagas";
  }

  const valorAluguel = parseValorPtBr(values.valorAluguel);
  if (valorAluguel === null || valorAluguel <= 0) {
    errors.valorAluguel = "Informe o valor do aluguel (ex.: 1.500,00)";
  }

  if (Object.keys(errors).length > 0) {
    throw new DomainError(
      "Verifique os campos obrigatórios do imóvel.",
      "INVALID_PROPERTY_FORM",
      errors,
    );
  }
}

function numeroOuZero(valor: string): number {
  const numero = Number(valor);
  return Number.isFinite(numero) ? numero : 0;
}

export function buildPropertyPayload(
  values: PropertyFormValues,
  inclusions: PropertyInclusionFlags,
): ImovelContractsPayload {
  return {
    nomeProprietario: values.nomeProprietario?.trim() || undefined,
    titulo: values.titulo.trim(),
    tipo: values.tipo,
    cep: values.cep.replace(/\D/g, ""),
    logradouro: values.logradouro.trim(),
    numero: values.numero.trim(),
    complemento: values.complemento.trim() || undefined,
    bairro: values.bairro.trim() || undefined,
    cidade: values.cidade.trim(),
    estado: values.estado.trim().toUpperCase() || undefined,
    area: parseValorPtBr(values.area) ?? 0,
    quartos: numeroOuZero(values.quartos),
    banheiros: numeroOuZero(values.banheiros),
    vagasGaragem: numeroOuZero(values.vagasGaragem),
    valorAluguel: parseValorPtBr(values.valorAluguel) ?? 0,
    valorIptu: values.valorIptu ? (parseValorPtBr(values.valorIptu) ?? 0) : undefined,
    valorCondominio: values.valorCondominio
      ? (parseValorPtBr(values.valorCondominio) ?? 0)
      : undefined,
    aguaInclusa: inclusions.aguaInclusa,
    energiaInclusa: inclusions.energiaInclusa,
    gasIncluso: inclusions.gasIncluso,
    internetInclusa: inclusions.internetInclusa,
    matricula: values.matricula.trim() || undefined,
    descricao: values.descricao.trim() || undefined,
  };
}

export function propertyErrorsFromApi(error: unknown): PropertyFieldErrors {
  if (!(error instanceof HttpError) || error.statusCode !== 422) return {};
  const fields = (error.body as { campos?: Record<string, string | string[]> } | null)?.campos;
  if (!fields) return {};
  const errors: PropertyFieldErrors = {};
  for (const [field, messages] of Object.entries(fields)) {
    errors[field as PropertyFieldName] = Array.isArray(messages) ? messages.join(" ") : messages;
  }
  return errors;
}
