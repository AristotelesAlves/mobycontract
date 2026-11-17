"use client";

import { useCallback, useEffect, useState } from "react";
import { getReadableErrorMessage } from "@/services/api/get-readable-error-message";
import type { Inquilino } from "../data/inquilino-types";
import type { TenantContractFilter } from "../data/tenant-api-types";
import { listTenants } from "../services/tenant-service";

const PAGE_SIZE = 8;

export interface TenantFilterCounts {
  todos: number;
  comContrato: number;
  semContrato: number;
}

export function useTenantList() {
  const [tenants, setTenants] = useState<Inquilino[]>([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState("");
  const [reloadKey, setReloadKey] = useState(0);

  const [searchTerm, setSearchTerm] = useState("");
  const [contractFilter, setContractFilter] =
    useState<TenantContractFilter>("todos");
  const [page, setPage] = useState(1);

  const [totalPages, setTotalPages] = useState(1);
  const [filteredCount, setFilteredCount] = useState(0);
  const [counts, setCounts] = useState<TenantFilterCounts | null>(null);
  const [countsError, setCountsError] = useState("");

  const changeSearchTerm = useCallback((value: string) => {
    setSearchTerm(value);
    setPage(1);
  }, []);

  const changeContractFilter = useCallback((value: TenantContractFilter) => {
    setContractFilter(value);
    setPage(1);
  }, []);

  const retry = useCallback(() => {
    setReloadKey((current) => current + 1);
  }, []);

  useEffect(() => {
    let cancelado = false;

    async function carregar() {
      setLoading(true);
      setError("");
      try {
        const resultado = await listTenants({
          texto: searchTerm,
          situacao: contractFilter,
          pagina: page,
          tamanhoPagina: PAGE_SIZE,
        });
        if (cancelado) return;
        setTenants(resultado.itens);
        setFilteredCount(resultado.total);
        setTotalPages(resultado.totalPaginas);
        if (resultado.pagina && resultado.pagina !== page) setPage(resultado.pagina);
      } catch (erro) {
        if (cancelado) return;
        setTenants([]);
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
  }, [searchTerm, contractFilter, page, reloadKey]);

  useEffect(() => {
    let cancelado = false;

    async function carregarContagens() {
      try {
        const [todos, comContrato, semContrato] = await Promise.all([
          listTenants({ tamanhoPagina: 1 }),
          listTenants({ situacao: "com_contrato", tamanhoPagina: 1 }),
          listTenants({ situacao: "sem_contrato", tamanhoPagina: 1 }),
        ]);
        if (cancelado) return;
        setCounts({
          todos: todos.total,
          comContrato: comContrato.total,
          semContrato: semContrato.total,
        });
        setCountsError("");
      } catch (erro) {
        if (cancelado) return;
        setCounts(null);
        setCountsError(getReadableErrorMessage(erro));
      }
    }

    carregarContagens();
    return () => {
      cancelado = true;
    };
  }, [reloadKey]);

  return {
    searchTerm,
    contractFilter,
    page,
    tenants,
    loading,
    error,
    retry,
    totalTenants: counts?.todos ?? 0,
    tenantsWithContract: counts?.comContrato ?? 0,
    tenantsWithoutContract: counts?.semContrato ?? 0,
    countsError,
    filteredCount,
    pageSize: PAGE_SIZE,
    totalPages,
    changeSearchTerm,
    changeContractFilter,
    goToPage: setPage,
  };
}
