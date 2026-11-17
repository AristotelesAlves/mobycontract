"use client";

import { useCallback, useState } from "react";
import { formatPostalCode } from "@/lib/formatters";
import { fetchCompanyByTaxId, normalizeTaxId } from "@/services/company-service";
import { fetchAddressByPostalCode, normalizePostalCode } from "@/services/address-service";
import { getReadableErrorMessage } from "@/services/api/get-readable-error-message";
import type {
  ContractPartyMode,
  NewTenantDraft,
} from "../use-cases/validate-contract-draft";

const EMPTY_TENANT_DRAFT: NewTenantDraft = {
  nome: "",
  documento: "",
  rg: "",
  email: "",
  telefone: "",
  dataNascimento: "",
  estadoCivil: "",
  renda: "",
  cep: "",
  logradouro: "",
  numero: "",
  bairro: "",
  cidade: "",
  estado: "",
};

export function useContractParties() {
  const [partyMode, setPartyMode] = useState<ContractPartyMode>("existente");
  const [existingTenantId, setExistingTenantId] = useState("");
  const [newTenant, setNewTenant] = useState<NewTenantDraft>(EMPTY_TENANT_DRAFT);
  const [searchingCompany, setSearchingCompany] = useState(false);
  const [searchingAddress, setSearchingAddress] = useState(false);
  const [lookupError, setLookupError] = useState("");

  const patchNewTenant = useCallback(
    (field: keyof NewTenantDraft, value: string) => {
      setNewTenant((current) => ({ ...current, [field]: value }));
      setLookupError("");
    },
    [],
  );

  const changePartyMode = useCallback((mode: ContractPartyMode) => {
    setPartyMode(mode);
    setLookupError("");
  }, []);

  const searchCompany = useCallback(async () => {
    const digits = normalizeTaxId(newTenant.documento);
    if (digits.length !== 14) return;
    setSearchingCompany(true);
    setLookupError("");
    try {
      const company = await fetchCompanyByTaxId(digits);
      if (!company) {
        setLookupError("Empresa não encontrada.");
        return;
      }
      setNewTenant((current) => ({
        ...current,
        nome: company.legalName,
        logradouro: company.street,
        numero: company.number,
        bairro: company.district,
        cidade: company.city,
        estado: company.state,
      }));
    } catch (error) {
      setLookupError(getReadableErrorMessage(error));
    } finally {
      setSearchingCompany(false);
    }
  }, [newTenant.documento]);

  const searchAddress = useCallback(async () => {
    const postalCode = normalizePostalCode(newTenant.cep);
    if (postalCode.length !== 8) return;
    setSearchingAddress(true);
    setLookupError("");
    try {
      const address = await fetchAddressByPostalCode(postalCode);
      if (!address) {
        setLookupError("Endereço não encontrado.");
        return;
      }
      setNewTenant((current) => ({
        ...current,
        cep: formatPostalCode(address.postalCode),
        logradouro: address.street,
        bairro: address.district,
        cidade: address.city,
        estado: address.state,
      }));
    } catch (error) {
      setLookupError(getReadableErrorMessage(error));
    } finally {
      setSearchingAddress(false);
    }
  }, [newTenant.cep]);

  return {
    partyMode,
    existingTenantId,
    newTenant,
    searchingCompany,
    searchingAddress,
    lookupError,
    changePartyMode,
    setExistingTenantId,
    patchNewTenant,
    searchCompany,
    searchAddress,
  };
}