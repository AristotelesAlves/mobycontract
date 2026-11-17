import { api } from "./api/base-api";
import { normalizePostalCode } from "./address-service";

const BRASIL_API_BASE_URL = "https://brasilapi.com.br/api";
const TAX_ID_LENGTH = 14;

export interface CompanyLookup {
  legalName: string;
  postalCode: string;
  street: string;
  number: string;
  district: string;
  city: string;
  state: string;
}

interface BrasilApiCompanyResponse {
  razao_social: string;
  cep: string;
  logradouro: string;
  numero: string;
  bairro: string;
  municipio: string;
  uf: string;
}

export function normalizeTaxId(taxId: string): string {
  return taxId.replace(/\D/g, "");
}

export async function fetchCompanyByTaxId(taxId: string): Promise<CompanyLookup | null> {
  const normalizedTaxId = normalizeTaxId(taxId);
  if (normalizedTaxId.length !== TAX_ID_LENGTH) return null;

  try {
    const response = await api.get<BrasilApiCompanyResponse>(
      `${BRASIL_API_BASE_URL}/cnpj/v1/${normalizedTaxId}`,
    );
    return {
      legalName: response.razao_social,
      postalCode: normalizePostalCode(response.cep),
      street: response.logradouro,
      number: response.numero,
      district: response.bairro,
      city: response.municipio,
      state: response.uf,
    };
  } catch {
    return null;
  }
}