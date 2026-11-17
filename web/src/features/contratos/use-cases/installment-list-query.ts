import { formatCurrency } from "@/lib/formatters";
import type { ParcelaFinanceira } from "../data/contrato-types";

export type InstallmentStatusFilter =
  | "Todos"
  | ParcelaFinanceira["status"];

export interface InstallmentListQuery {
  searchTerm: string;
  statusFilter: InstallmentStatusFilter;
  page: number;
  pageSize: number;
}

export interface InstallmentCounts {
  todos: number;
  pendentes: number;
  pagos: number;
  atrasados: number;
}

export interface InstallmentListResult {
  filtered: ParcelaFinanceira[];
  paged: ParcelaFinanceira[];
  totalPages: number;
}

export const INSTALLMENT_PAGE_SIZE = 6;

export function countInstallments(
  installments: ParcelaFinanceira[],
): InstallmentCounts {
  return {
    todos: installments.length,
    pendentes: installments.filter((item) => item.status === "pendente").length,
    pagos: installments.filter((item) => item.status === "pago").length,
    atrasados: installments.filter((item) => item.status === "atrasado").length,
  };
}

export function filterInstallments(
  installments: ParcelaFinanceira[],
  searchTerm: string,
  statusFilter: InstallmentStatusFilter,
): ParcelaFinanceira[] {
  const term = searchTerm.trim().toLowerCase();
return installments.filter((installment) => {
    if (statusFilter !== "Todos" && installment.status !== statusFilter) {
      return false;
    }

    if (!term) return true;
    return (
      (installment.referencia ?? "").toLowerCase().includes(term) ||
      String(installment.numero).includes(term) ||
      String(installment.valor).includes(term)
    );
  });
}

export function queryInstallmentList(
  installments: ParcelaFinanceira[],
  query: InstallmentListQuery,
): InstallmentListResult {
  const filtered = filterInstallments(
    installments,
    query.searchTerm,
    query.statusFilter,
  );
  const totalPages = Math.max(
    1,
    Math.ceil(filtered.length / Math.max(1, query.pageSize)),
  );
  const safePage = Math.min(query.page, totalPages);
  const start = (safePage - 1) * query.pageSize;

  return {
    filtered,
    paged: filtered.slice(start, start + query.pageSize),
    totalPages,
  };
}

export function installmentTotal(installment: ParcelaFinanceira): number {
  return installment.valor + (installment.juros ?? 0) + (installment.taxasAdicionais ?? 0);
}

export function installmentDisplayTotal(installment: ParcelaFinanceira): string {
  return formatCurrency(
    installment.status === "pago" && installment.valorPago !== undefined
      ? installment.valorPago
      : installmentTotal(installment),
  );
}

export interface ChargeEntry {
  tipo: "multa" | "juros" | "taxa";
  valor: number;
  motivo: string;
}
