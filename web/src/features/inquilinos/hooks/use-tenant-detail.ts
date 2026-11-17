"use client";

import { useCallback, useEffect, useMemo, useState } from "react";
import { getReadableErrorMessage } from "@/services/api/get-readable-error-message";
import { listTenantContractsWithInstallments } from "@/features/contratos/services/contract-service";
import type { Contrato } from "@/features/contratos/data/contrato-types";
import type { TenantIndicatorsDTO } from "../data/tenant-api-types";
import type { Inquilino } from "../data/inquilino-types";
import { getTenant, getTenantIndicators } from "../services/tenant-service";
import {
  flattenTenantInstallments,
  queryTenantContracts,
  queryTenantInstallments,
  TENANT_DETAIL_PAGE_SIZE,
  type ContractHistoryFilter,
  type InstallmentStatusFilter,
} from "../use-cases/tenant-detail-query";

export function useTenantDetail(tenantId: string) {
  const [tenant, setTenant] = useState<Inquilino | null>(null);
  const [indicators, setIndicators] = useState<TenantIndicatorsDTO | null>(null);
  const [tenantContracts, setTenantContracts] = useState<Contrato[]>([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState("");
  const [reloadKey, setReloadKey] = useState(0);

  const [contractPage, setContractPage] = useState(1);
  const [installmentPage, setInstallmentPage] = useState(1);
  const [contractSearch, setContractSearch] = useState("");
  const [contractFilter, setContractFilter] =
    useState<ContractHistoryFilter>("todos");
  const [installmentSearch, setInstallmentSearch] = useState("");
  const [installmentFilter, setInstallmentFilter] =
    useState<InstallmentStatusFilter>("todos");

  const retry = useCallback(() => {
    setReloadKey((current) => current + 1);
  }, []);

  useEffect(() => {
    let cancelado = false;

    async function carregar() {
      setLoading(true);
      setError("");
      try {
        const inquilino = await getTenant(tenantId);
        if (cancelado) return;
        setTenant(inquilino);

        const [indicadores, contratos] = await Promise.all([
          getTenantIndicators(tenantId),
          listTenantContractsWithInstallments(tenantId),
        ]);
        if (cancelado) return;
        setIndicators(indicadores);
        setTenantContracts(contratos);
      } catch (erro) {
        if (cancelado) return;
        setError(getReadableErrorMessage(erro));
      } finally {
        if (!cancelado) setLoading(false);
      }
    }

    carregar();
    return () => {
      cancelado = true;
    };
  }, [tenantId, reloadKey]);

  const allContracts = tenantContracts;
  const allInstallments = useMemo(
    () => flattenTenantInstallments(tenantContracts),
    [tenantContracts],
  );

  const contractHistory = useMemo(
    () =>
      queryTenantContracts(tenantContracts, {
        searchTerm: contractSearch,
        filter: contractFilter,
        page: contractPage,
        pageSize: TENANT_DETAIL_PAGE_SIZE,
      }),
    [tenantContracts, contractSearch, contractFilter, contractPage],
  );

  const installmentList = useMemo(
    () =>
      queryTenantInstallments(allInstallments, {
        searchTerm: installmentSearch,
        filter: installmentFilter,
        page: installmentPage,
        pageSize: TENANT_DETAIL_PAGE_SIZE,
      }),
    [allInstallments, installmentSearch, installmentFilter, installmentPage],
  );

  const activeContract = useMemo(
    () =>
      tenantContracts.find(
        (contract) => contract.status === "Vigente" || contract.status === "Validado",
      ),
    [tenantContracts],
  );

  const hasActiveContract =
    indicators?.possuiContratoAtivo ?? Boolean(activeContract);

  const age = indicators?.idade ?? null;
  const commitmentRate = indicators?.compromissoRenda ?? null;
  const commitmentAlert = indicators?.alertaCompromisso ?? false;

  const changeContractSearch = useCallback((value: string) => {
    setContractSearch(value);
    setContractPage(1);
  }, []);

  const changeContractFilter = useCallback((value: ContractHistoryFilter) => {
    setContractFilter(value);
    setContractPage(1);
  }, []);

  const changeInstallmentSearch = useCallback((value: string) => {
    setInstallmentSearch(value);
    setInstallmentPage(1);
  }, []);

  const changeInstallmentFilter = useCallback(
    (value: InstallmentStatusFilter) => {
      setInstallmentFilter(value);
      setInstallmentPage(1);
    },
    [],
  );

  return {
    tenant,
    indicators,
    hasActiveContract,
    activeContract,
    age,
    commitmentRate,
    commitmentAlert,
    allContracts,
    allInstallments,
    contracts: contractHistory.paged,
    filteredContractCount: contractHistory.filtered.length,
    contractTotalPages: contractHistory.totalPages,
    installments: installmentList.paged,
    filteredInstallmentCount: installmentList.filtered.length,
    installmentTotalPages: installmentList.totalPages,
    pageSize: TENANT_DETAIL_PAGE_SIZE,
    contractPage,
    installmentPage,
    contractSearch,
    contractFilter,
    installmentSearch,
    installmentFilter,
    loading,
    error,
    retry,
    changeContractSearch,
    changeContractFilter,
    changeInstallmentSearch,
    changeInstallmentFilter,
    goToContractPage: setContractPage,
    goToInstallmentPage: setInstallmentPage,
  };
}
