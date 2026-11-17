"use client";

import { useCallback, useEffect, useState } from "react";
import { getReadableErrorMessage } from "@/services/api/get-readable-error-message";
import type { Contrato, ContratoStatus } from "../data/contrato-types";
import { listContracts } from "../services/contract-service";

import { listProperties } from "@/features/imoveis/services/property-service";
import { listTenants } from "@/features/inquilinos/services/tenant-service";

const CONTRACTS_PAGE_SIZE = 8;

type FilterOption = { value: string; label: string };

async function loadOptions<T>(load: (filters: { pagina: number; tamanhoPagina: number }) => Promise<{ itens: T[]; totalPaginas: number }>, map: (item: T) => FilterOption) {
  const options: FilterOption[] = [];
  let page = 1;
  let totalPages = 1;
  do {
    const result = await load({ pagina: page, tamanhoPagina: 100 });
    options.push(...result.itens.map(map));
    totalPages = result.totalPaginas;
    page += 1;
  } while (page <= totalPages);
  return options;
}

export type ContractsStatusFilter =
  | "todos"
  | "Rascunho"
  | "Minuta"
  | "Pendente"
  | "Validado"
  | "Vigente"
  | "Encerrado"
  | "Cancelado";

const STATUS_FILTERS = [
  "Rascunho",
  "Minuta",
  "Pendente",
  "Validado",
  "Vigente",
  "Encerrado",
  "Cancelado",
] as const;

export function useContractsList() {
  const [contracts, setContracts] = useState<Contrato[]>([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState("");
  const [reloadKey, setReloadKey] = useState(0);

  const [propertyFilter, setPropertyFilter] = useState("");
  const [tenantFilter, setTenantFilter] = useState("");
  const [propertyOptions, setPropertyOptions] = useState<FilterOption[]>([]);
  const [tenantOptions, setTenantOptions] = useState<FilterOption[]>([]);
  const [filterError, setFilterError] = useState("");
  const [filtersLoading, setFiltersLoading] = useState(true);

  useEffect(() => {
    let cancelled = false;
    setFiltersLoading(true);
    setFilterError("");
    Promise.all([
      loadOptions(listProperties, (property) => ({ value: String(property.id), label: `${property.titulo} • ${property.codigo}` })),
      loadOptions(listTenants, (tenant) => ({ value: String(tenant.id), label: `${tenant.nome} • ${tenant.cpf}` })),
    ]).then(([properties, tenants]) => {
      if (cancelled) return;
      setPropertyOptions(properties);
      setTenantOptions(tenants);
    }).catch((error) => {
      if (!cancelled) setFilterError(getReadableErrorMessage(error));
    }).finally(() => { if (!cancelled) setFiltersLoading(false); });
    return () => { cancelled = true; };
  }, [reloadKey]);

  const [searchTerm, setSearchTerm] = useState("");
  const [statusFilter, setStatusFilter] = useState<ContractsStatusFilter>("todos");
  const [currentPage, setCurrentPage] = useState(1);

  const [filteredCount, setFilteredCount] = useState(0);
  const [totalPages, setTotalPages] = useState(1);
  const [counts, setCounts] = useState<Partial<Record<ContratoStatus, number>>>(
    {},
  );

  const retry = useCallback(() => {
    setReloadKey((current) => current + 1);
  }, []);

  const handleSearch = useCallback((term: string) => {
    setSearchTerm(term);
    setCurrentPage(1);
  }, []);

  const handleStatusFilter = useCallback(
    (status: ContractsStatusFilter) => {
      setStatusFilter(status);
      setCurrentPage(1);
    },
    [],
  );

  useEffect(() => {
    let cancelado = false;

    async function carregar() {
      setLoading(true);
      setError("");
      try {
        const resultado = await listContracts({
          status: statusFilter === "todos" ? undefined : statusFilter,
          texto: searchTerm,
          imovelId: propertyFilter ? Number(propertyFilter) : undefined,
          inquilinoId: tenantFilter || undefined,
          pagina: currentPage,
          tamanhoPagina: CONTRACTS_PAGE_SIZE,
        });
        if (cancelado) return;
        setContracts(resultado.itens);
        setFilteredCount(resultado.total);
        setTotalPages(resultado.totalPaginas);
        if (resultado.pagina && resultado.pagina !== currentPage) {
          setCurrentPage(resultado.pagina);
        }
      } catch (erro) {
        if (cancelado) return;
        setContracts([]);
        setFilteredCount(0);
        setTotalPages(1);
        setError(getReadableErrorMessage(erro));
      } finally {
        if (!cancelado) setLoading(false);
      }
    }

    carregar();
    return () => {
      cancelado = true;
    };
  }, [searchTerm, statusFilter, propertyFilter, tenantFilter, currentPage, reloadKey]);

  useEffect(() => {
    let cancelado = false;

    async function carregarContagens() {
      try {
        const resultados = await Promise.all(
          STATUS_FILTERS.map((status) =>
            listContracts({ status, tamanhoPagina: 1, imovelId: propertyFilter ? Number(propertyFilter) : undefined, inquilinoId: tenantFilter || undefined }),
          ),
        );
        if (cancelado) return;
        const contagens: Partial<Record<ContratoStatus, number>> = {};
        STATUS_FILTERS.forEach((status, index) => {
          contagens[status as ContratoStatus] = resultados[index]?.total ?? 0;
        });
        setCounts(contagens);
      } catch {
        if (!cancelado) setCounts({});
      }
    }

    carregarContagens();
    return () => {
      cancelado = true;
    };
  }, [reloadKey, propertyFilter, tenantFilter]);

  return {
    contracts,
    propertyFilter,
    tenantFilter,
    propertyOptions,
    tenantOptions,
    filterError,
    filtersLoading,
    handlePropertyFilter: (value: string) => { setPropertyFilter(value); setCurrentPage(1); },
    handleTenantFilter: (value: string) => { setTenantFilter(value); setCurrentPage(1); },
    searchTerm,
    statusFilter,
    currentPage,
    itemsPerPage: CONTRACTS_PAGE_SIZE,
    filteredCount,
    totalPages,
    counts,
    loading,
    error,
    retry,
    handleSearch,
    handleStatusFilter,
    setCurrentPage,
  };
}
