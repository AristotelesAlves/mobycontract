"use client";

import { useCallback, useEffect, useState } from "react";
import { useRouter } from "next/navigation";
import { getReadableErrorMessage } from "@/services/api/get-readable-error-message";
import type { ImovelStatus } from "../data/imovel-types";
import { listProperties } from "../services/property-service";

const PROPERTY_PAGE_SIZE = 8;

export type PropertyStatusFilter = "todos" | ImovelStatus;

type PropertiesListResult = Awaited<ReturnType<typeof listProperties>>;

export function usePropertyList() {
  const router = useRouter();

  const [properties, setProperties] = useState<PropertiesListResult["itens"]>([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState("");
  const [reloadKey, setReloadKey] = useState(0);

  const [searchTerm, setSearchTerm] = useState("");
  const [status, setStatus] = useState<PropertyStatusFilter>("todos");
  const [page, setPage] = useState(1);

  const [filteredCount, setFilteredCount] = useState(0);
  const [totalPages, setTotalPages] = useState(1);
  const [counts, setCounts] = useState<{
    available: number;
    rented: number;
    underNegotiation: number;
    sold: number;
  } | null>(null);

  const changeSearchTerm = useCallback((value: string) => {
    setSearchTerm(value);
    setPage(1);
  }, []);

  const changeStatus = useCallback((value: PropertyStatusFilter) => {
    setStatus(value);
    setPage(1);
  }, []);

  const retry = useCallback(() => {
    setReloadKey((current) => current + 1);
  }, []);

  const goToNewProperty = useCallback(() => {
    router.push("/imoveis/novo");
  }, [router]);

  const goToProperty = useCallback(
    (propertyId: string) => {
      router.push(`/imoveis/${propertyId}`);
    },
    [router],
  );

  const goToEditProperty = useCallback(
    (propertyId: string) => {
      router.push(`/imoveis/${propertyId}/editar`);
    },
    [router],
  );

  useEffect(() => {
    let cancelado = false;

    async function carregar() {
      setLoading(true);
      setError("");
      try {
        const resultado = await listProperties({
          texto: searchTerm,
          status,
          pagina: page,
          tamanhoPagina: PROPERTY_PAGE_SIZE,
        });
        if (cancelado) return;
        setProperties(resultado.itens);
        setFilteredCount(resultado.total);
        setTotalPages(resultado.totalPaginas);
        if (resultado.pagina && resultado.pagina !== page) setPage(resultado.pagina);
      } catch (erro) {
        if (cancelado) return;
        setProperties([]);
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
  }, [searchTerm, status, page, reloadKey]);

  useEffect(() => {
    let cancelado = false;

    async function carregarContagens() {
      try {
        const [disponiveis, alugados, negociacao, vendidos] =
          await Promise.all([
            listProperties({ status: "Disponível", tamanhoPagina: 1 }),
            listProperties({ status: "Alugado", tamanhoPagina: 1 }),
            listProperties({ status: "Em negociação", tamanhoPagina: 1 }),
            listProperties({ status: "Vendido", tamanhoPagina: 1 }),
          ]);
        if (cancelado) return;
        setCounts({
          available: disponiveis.total,
          rented: alugados.total,
          underNegotiation: negociacao.total,
          sold: vendidos.total,
        });
      } catch {
        if (!cancelado) setCounts(null);
      }
    }

    carregarContagens();
    return () => {
      cancelado = true;
    };
  }, [reloadKey]);

  return {
    properties,
    loading,
    error,
    retry,
    searchTerm,
    status,
    page,
    pageSize: PROPERTY_PAGE_SIZE,
    filteredCount,
    totalPages,
    availableCount: counts?.available ?? 0,
    rentedCount: counts?.rented ?? 0,
    underNegotiationCount: counts?.underNegotiation ?? 0,
    soldCount: counts?.sold ?? 0,
    changeSearchTerm,
    changeStatus,
    goToPage: setPage,
    goToNewProperty,
    goToProperty,
    goToEditProperty,
  };
}
