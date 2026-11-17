import { api } from "./api/base-api";

const BRASIL_API_BASE_URL = "https://brasilapi.com.br/api";
const POSTAL_CODE_LENGTH = 8;

export interface AddressLookup {
  postalCode: string;
  street: string;
  district: string;
  city: string;
  state: string;
}

interface BrasilApiPostalCodeResponse {
  cep: string;
  street: string;
  neighborhood: string;
  city: string;
  state: string;
}

export function normalizePostalCode(postalCode: string): string {
  return postalCode.replace(/\D/g, "");
}

export async function fetchAddressByPostalCode(postalCode: string): Promise<AddressLookup | null> {
  const normalizedPostalCode = normalizePostalCode(postalCode);
  if (normalizedPostalCode.length !== POSTAL_CODE_LENGTH) return null;

  try {
    const response = await api.get<BrasilApiPostalCodeResponse>(
      `${BRASIL_API_BASE_URL}/cep/v1/${normalizedPostalCode}`,
    );
    return {
      postalCode: response.cep,
      street: response.street,
      district: response.neighborhood,
      city: response.city,
      state: response.state,
    };
  } catch {
    return null;
  }
}