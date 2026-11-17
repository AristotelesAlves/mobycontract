"use client";

import { useCallback, useEffect, useMemo, useState } from "react";
import { getReadableErrorMessage } from "@/services/api/get-readable-error-message";
import { listContracts } from "@/features/contratos/services/contract-service";
import { listTenants } from "@/features/inquilinos/services/tenant-service";
import { listProperties } from "@/features/imoveis/services/property-service";
import type { Contrato } from "@/features/contratos/data/contrato-types";
import type { Imovel } from "@/features/imoveis/data/imovel-types";
import type { Inquilino } from "@/features/inquilinos/data/inquilino-types";
import {
  buildDashboardMetrics,
  buildDashboardMovements,
  buildPortfolioSlices,
} from "../use-cases/build-dashboard-summary";

const DASHBOARD_PAGE_SIZE = 100;

export function useDashboardSummary() {
  const [properties, setProperties] = useState<Imovel[]>([]);
  const [contracts, setContracts] = useState<Contrato[]>([]);
  const [tenants, setTenants] = useState<Inquilino[]>([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState("");
  const [reloadKey, setReloadKey] = useState(0);

  const retry = useCallback(() => {
    setReloadKey((current) => current + 1);
  }, []);

  useEffect(() => {
    let cancelado = false;

    async function carregar() {
      setLoading(true);
      setError("");
      try {
        const [listaDeImoveis, listaDeContratos, listaDeInquilinos] =
          await Promise.all([
            listProperties({ tamanhoPagina: DASHBOARD_PAGE_SIZE }),
            listContracts({ tamanhoPagina: DASHBOARD_PAGE_SIZE }),
            listTenants({ tamanhoPagina: DASHBOARD_PAGE_SIZE }),
          ]);
        if (cancelado) return;
        setProperties(listaDeImoveis.itens);
        setContracts(listaDeContratos.itens);
        setTenants(listaDeInquilinos.itens);
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
  }, [reloadKey]);

  const metrics = useMemo(
    () => buildDashboardMetrics(properties, contracts, tenants),
    [properties, contracts, tenants],
  );

  const portfolioSlices = useMemo(
    () => buildPortfolioSlices(properties, contracts),
    [properties, contracts],
  );

  const movements = useMemo(
    () => buildDashboardMovements(contracts),
    [contracts],
  );

  const recentTenants = useMemo(() => tenants.slice(0, 4), [tenants]);

  return {
    metrics,
    portfolioSlices,
    movements,
    recentTenants,
    loading,
    error,
    retry,
  };
}
