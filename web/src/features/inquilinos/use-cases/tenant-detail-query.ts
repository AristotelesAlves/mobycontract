import type {
  Contrato,
  ParcelaFinanceira,
} from "@/features/contratos/data/contrato-types";
import type { Inquilino } from "../data/inquilino-types";

export type ContractHistoryFilter = "todos" | "vigentes" | "encerrados";
export type InstallmentStatusFilter = "todos" | ParcelaFinanceira["status"];

export interface TenantInstallment extends ParcelaFinanceira {
  contractNumber: string;
  propertyTitle: string;
}

export interface TenantContractListQuery {
  searchTerm: string;
  filter: ContractHistoryFilter;
  page: number;
  pageSize: number;
}

export interface TenantInstallmentListQuery {
  searchTerm: string;
  filter: InstallmentStatusFilter;
  page: number;
  pageSize: number;
}

export interface PagedResult<TItem> {
  filtered: TItem[];
  paged: TItem[];
  totalPages: number;
}

export const TENANT_DETAIL_PAGE_SIZE = 5;

export function incomeCommitmentRate(
  tenant: Inquilino,
  activeContract: Contrato | undefined,
): number | null {
  const renda = tenant.renda ?? 0;
  if (!activeContract || renda <= 0) return null;
  const valorMensal = activeContract.valorMensal ?? activeContract.valorAluguel;
  return (valorMensal / renda) * 100;
}

function paginate<TItem>(
  items: TItem[],
  page: number,
  pageSize: number,
): PagedResult<TItem> {
  const totalPages = Math.max(1, Math.ceil(items.length / Math.max(1, pageSize)));
  const safePage = Math.min(page, totalPages);
  const start = (safePage - 1) * pageSize;

  return {
    filtered: items,
    paged: items.slice(start, start + pageSize),
    totalPages,
  };
}

export function queryTenantContracts(
  contracts: Contrato[],
  query: TenantContractListQuery,
): PagedResult<Contrato> {
  const term = query.searchTerm.trim().toLowerCase();

  const filtered = contracts.filter((contract) => {
    if (
      query.filter === "vigentes" &&
      contract.status !== "Validado" &&
      contract.status !== "Vigente"
    ) {
      return false;
    }
    if (
      query.filter === "encerrados" &&
      contract.status !== "Cancelado" &&
      contract.status !== "Encerrado"
    ) {
      return false;
    }
    if (!term) return true;
    return (
      contract.numero.toLowerCase().includes(term) ||
      (contract.imovelTitulo ?? "").toLowerCase().includes(term)
    );
  });

  return paginate(filtered, query.page, query.pageSize);
}

export function flattenTenantInstallments(
  contracts: Contrato[],
): TenantInstallment[] {
  return contracts.flatMap((contract) =>
    (Array.isArray(contract.parcelas) ? contract.parcelas : []).map(
      (installment) => ({
        ...installment,
        contractNumber: contract.numero,
        propertyTitle: contract.imovelTitulo ?? "",
      }),
    ),
  );
}

export function queryTenantInstallments(
  installments: TenantInstallment[],
  query: TenantInstallmentListQuery,
): PagedResult<TenantInstallment> {
  const term = query.searchTerm.trim().toLowerCase();

  const sorted = [...installments].sort(
    (a, b) =>
      new Date(b.dataVencimento).getTime() - new Date(a.dataVencimento).getTime(),
  );

  const filtered = sorted.filter((installment) => {
    if (query.filter !== "todos" && installment.status !== query.filter) {
      return false;
    }
    if (!term) return true;
    return (
      installment.contractNumber.toLowerCase().includes(term) ||
      installment.propertyTitle.toLowerCase().includes(term)
    );
  });

  return paginate(filtered, query.page, query.pageSize);
}

export function calculateAge(
  birthDate: string,
  referenceDate: string,
): number | null {
  const birth = new Date(birthDate);
  if (Number.isNaN(birth.getTime())) return null;

  const reference = new Date(referenceDate);
  if (Number.isNaN(reference.getTime())) return null;

  let age = reference.getFullYear() - birth.getFullYear();
  const monthDiff = reference.getMonth() - birth.getMonth();

  if (monthDiff < 0 || (monthDiff === 0 && reference.getDate() < birth.getDate())) {
    age -= 1;
  }

  return age;
}