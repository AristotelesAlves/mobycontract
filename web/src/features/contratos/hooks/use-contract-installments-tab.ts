"use client";

import { useCallback, useMemo, useState } from "react";
import type { ParcelaFinanceira } from "../data/contrato-types";
import type {
  LancarEncargoPayload,
  RegistrarPagamentoPayload,
} from "../services/contract-service";
import {
  countInstallments,
  installmentDisplayTotal,
  INSTALLMENT_PAGE_SIZE,
  queryInstallmentList,
  type ChargeEntry,
  type InstallmentStatusFilter,
} from "../use-cases/installment-list-query";

export function useContractInstallmentsTab(
  parcelas: ParcelaFinanceira[],
  registrarPagamento: (parcelaId: string, dados: RegistrarPagamentoPayload) => Promise<boolean>,
  lancarEncargo: (parcelaId: string, dados: LancarEncargoPayload) => void,
) {
  const [searchTerm, setSearchTerm] = useState("");
  const [statusFilter, setStatusFilter] =
    useState<InstallmentStatusFilter>("Todos");
  const [chargeDialogOpen, setChargeDialogOpen] = useState(false);
  const [selectedInstallmentId, setSelectedInstallmentId] = useState<
    string | null
  >(null);
  const [page, setPage] = useState(1);
  const [paymentId, setPaymentId] = useState<string | null>(null);
  const [paymentSaving, setPaymentSaving] = useState(false);

  const list = useMemo(
    () =>
      queryInstallmentList(parcelas, {
        searchTerm,
        statusFilter,
        page,
        pageSize: INSTALLMENT_PAGE_SIZE,
      }),
    [parcelas, searchTerm, statusFilter, page],
  );

  const counts = useMemo(() => countInstallments(parcelas), [parcelas]);

  const changePage = useCallback(
    (nextPage: number) => {
      setPage(Math.max(1, nextPage));
    },
    [],
  );

  const launchCharge = useCallback(
    (charge: ChargeEntry) => {
      if (!selectedInstallmentId) return;
      lancarEncargo(selectedInstallmentId, {
        tipo: charge.tipo,
        valor: charge.valor,
        motivo: charge.motivo,
      });
    },
    [selectedInstallmentId, lancarEncargo],
  );

  const confirmPayment = async (dados: RegistrarPagamentoPayload) => {
    if (!paymentId || paymentSaving) return;
    setPaymentSaving(true);
    try {
      if (await registrarPagamento(paymentId, dados)) setPaymentId(null);
    } finally {
      setPaymentSaving(false);
    }
  };

  const openChargeDialog = useCallback((installmentId: string) => {
    setSelectedInstallmentId(installmentId);
    setChargeDialogOpen(true);
  }, []);

  return {
    installments: list.paged,
    filteredCount: list.filtered.length,
    counts,
    page: Math.min(page, list.totalPages),
    pageSize: INSTALLMENT_PAGE_SIZE,
    totalPages: list.totalPages,
    searchTerm,
    statusFilter,
    chargeDialogOpen,
    selectedInstallmentId,
    changePage,
    changeSearchTerm: (value: string) => {
      setSearchTerm(value);
      changePage(1);
    },
    changeStatusFilter: (value: InstallmentStatusFilter) => {
      setStatusFilter(value);
      changePage(1);
    },
    setChargeDialogOpen,
    launchCharge,
    registerPayment: setPaymentId,
    paymentInstallment: parcelas.find((parcela) => parcela.id === paymentId),
    paymentSaving,
    closePayment: () => setPaymentId(null),
    confirmPayment,
    openChargeDialog,
    displayTotal: installmentDisplayTotal,
  };
}

export { installmentDisplayTotal };
