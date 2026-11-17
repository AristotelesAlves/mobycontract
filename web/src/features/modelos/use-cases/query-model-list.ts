import type { IModeloClonar } from "../data/modelo-types";

export type ModelTypeFilter = "todos" | ModeloType;

export type ModeloType = "Contrato" | "Aditivo" | "Distrato" | "Laudo";

export interface ModelListQuery {
  searchTerm: string;
  typeFilter: ModelTypeFilter;
  page: number;
  pageSize: number;
}

export interface ModelTypeCounts {
  todos: number;
  contratos: number;
  aditivos: number;
  distratos: number;
  laudos: number;
}

export interface ModelListResult {
  filtered: IModeloClonar[];
  paged: IModeloClonar[];
  totalPages: number;
}

export function countModelsByType(modelos: IModeloClonar[]): ModelTypeCounts {
  return {
    todos: modelos.length,
    contratos: modelos.filter((modelo) => modelo.tipo === "Contrato").length,
    aditivos: modelos.filter((modelo) => modelo.tipo === "Aditivo").length,
    distratos: modelos.filter((modelo) => modelo.tipo === "Distrato").length,
    laudos: modelos.filter((modelo) => modelo.tipo === "Laudo").length,
  };
}

export function filterModels(
  modelos: IModeloClonar[],
  searchTerm: string,
  typeFilter: ModelTypeFilter,
): IModeloClonar[] {
  const term = searchTerm.trim().toLowerCase();

  return modelos.filter((modelo) => {
    if (typeFilter !== "todos" && modelo.tipo !== typeFilter) return false;
    if (!term) return true;
    return (
      modelo.titulo.toLowerCase().includes(term) ||
      modelo.codigo.toString().toLowerCase().includes(term) ||
      modelo.tipo.toLowerCase().includes(term)
    );
  });
}

export function queryModelList(
  modelos: IModeloClonar[],
  query: ModelListQuery,
): ModelListResult {
  const filtered = filterModels(modelos, query.searchTerm, query.typeFilter);
  const totalPages = Math.max(
    1,
    Math.ceil(filtered.length / Math.max(1, query.pageSize)),
  );
  const currentPage = Math.min(query.page, totalPages);
  const start = (currentPage - 1) * query.pageSize;

  return {
    filtered,
    paged: filtered.slice(start, start + query.pageSize),
    totalPages,
  };
}

export function buildModelEditorParams(modelo: IModeloClonar): string {
  const params = new URLSearchParams({
    uuid: modelo.uuid,
  });

  return params.toString();
}
