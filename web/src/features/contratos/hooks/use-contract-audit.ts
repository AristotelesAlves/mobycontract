"use client";

import { useCallback, useEffect, useMemo, useState } from "react";
import { useRouter } from "next/navigation";
import { getReadableErrorMessage } from "@/services/api/get-readable-error-message";
import { getContract, listContractInstallments } from "../services/contract-service";
import type { Contrato, ParcelaFinanceira } from "../data/contrato-types";
import { derivarAlteracoesContrato } from "../use-cases/contract-life-events";
import {
  AUDIT_PAGE_SIZE,
  availableAuditActions,
  queryContractAudit,
  type AuditActionFilter,
} from "../use-cases/contract-audit-query";

export function useContractAudit(contractId: string) {
  const router = useRouter();
  const [contract, setContract] = useState<Contrato | null>(null);
  const [parcelas, setParcelas] = useState<ParcelaFinanceira[]>([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState("");
  const [reloadKey, setReloadKey] = useState(0);
  const [page, setPage] = useState(1);
  const [searchTerm, setSearchTerm] = useState("");
  const [action, setAction] = useState<AuditActionFilter>("Todos");

  const retry = useCallback(() => {
    setReloadKey((current) => current + 1);
  }, []);

  useEffect(() => {
    let cancelado = false;

    async function carregar() {
      setLoading(true);
      setError("");
      try {
        const contratoCarregado = await getContract(contractId);
        if (cancelado) return;
        setContract(contratoCarregado);
        const parcelasCarregadas = await listContractInstallments(
          contractId,
        ).catch(() => [] as ParcelaFinanceira[]);
        if (cancelado) return;
        setParcelas(parcelasCarregadas);
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
  }, [contractId, reloadKey]);

  const events = useMemo(
    () => derive(contract, parcelas),
    [contract, parcelas],
  );

  const allActionCounts = useMemo(() => {
    const contador: Record<string, number> = {};
    events.forEach((evento) => {
      contador[evento.acao] = (contador[evento.acao] ?? 0) + 1;
    });
    return contador;
  }, [events]);

  const actions = useMemo(
    () => availableAuditActions(events),
    [events],
  );

  const audit = useMemo(
    () =>
      queryContractAudit(events, {
        searchTerm,
        action,
        page,
        pageSize: AUDIT_PAGE_SIZE,
      }),
    [action, events, page, searchTerm],
  );

  const changeSearchTerm = useCallback((value: string) => {
    setSearchTerm(value);
    setPage(1);
  }, []);

  const changeAction = useCallback((value: AuditActionFilter) => {
    setAction(value);
    setPage(1);
  }, []);

  return {
    contract,
    changes: audit.changes,
    actions,
    action,
    allActionCounts,
    searchTerm,
    page,
    totalItems: audit.total,
    pageSize: AUDIT_PAGE_SIZE,
    loading,
    error,
    retry,
    changeSearchTerm,
    changeAction,
    goToPage: setPage,
    goToContracts: () => router.push("/contratos"),
  };
}

function derive(
  contract: Contrato | null,
  parcelas: ParcelaFinanceira[],
) {
  if (!contract) return [];
  return derivarAlteracoesContrato(contract, parcelas);
}
