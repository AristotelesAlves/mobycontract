import type { ContratoAlteracao } from "../data/contrato-types";

export const AUDIT_PAGE_SIZE = 8;

export const ALL_ACTIONS_FILTER = "Todos";

export type AuditActionFilter = string;

export interface ContractAuditQuery {
  searchTerm: string;
  action: AuditActionFilter;
  page: number;
  pageSize: number;
}

export interface PagedAuditResult {
  changes: ContratoAlteracao[];
  total: number;
  totalPages: number;
}

function matchesSearchTerm(change: ContratoAlteracao, term: string): boolean {
  return (
    change.usuario.toLowerCase().includes(term) ||
    change.campo.toLowerCase().includes(term) ||
    change.acao.toLowerCase().includes(term) ||
    String(change.de).toLowerCase().includes(term) ||
    String(change.para).toLowerCase().includes(term)
  );
}

export function availableAuditActions(changes: ContratoAlteracao[]): string[] {
  return [
    ALL_ACTIONS_FILTER,
    ...Array.from(new Set(changes.map((change) => change.acao))),
  ];
}

export function queryContractAudit(
  changes: ContratoAlteracao[],
  query: ContractAuditQuery,
): PagedAuditResult {
  const term = query.searchTerm.trim().toLowerCase();

  const filtered = [...changes]
    .reverse()
    .filter((change) => {
      const matchesAction =
        query.action === ALL_ACTIONS_FILTER || change.acao === query.action;
      if (!term) return matchesAction;
      return matchesAction && matchesSearchTerm(change, term);
    });

  const pageSize = Math.max(1, query.pageSize);
  const totalPages = Math.max(1, Math.ceil(filtered.length / pageSize));
  const safePage = Math.min(query.page, totalPages);
  const start = (safePage - 1) * pageSize;

  return {
    changes: filtered.slice(start, start + pageSize),
    total: filtered.length,
    totalPages,
  };
}