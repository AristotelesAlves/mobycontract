import { api } from "@/services/api/base-api";
import type { Inquilino } from "../data/inquilino-types";
import type {
  TenantContractsPayload,
  TenantContractFilter,
  TenantIndicatorsDTO,
  TenantIndicatorsEnvelope,
  TenantOrderDirection,
  TenantOrderField,
  TenantPageEnvelope,
  TenantResourceDTO,
  TenantResourceEnvelope,
} from "../data/tenant-api-types";

interface TenantListFilters {
  situacao?: TenantContractFilter;
  texto?: string;
  pagina?: number;
  tamanhoPagina?: number;
  ordenarPor?: TenantOrderField;
  direcao?: TenantOrderDirection;
}

export interface TenantListResult {
  itens: Inquilino[];
  pagina: number;
  tamanhoPagina: number;
  totalPaginas: number;
  total: number;
}

function mapearTenant(recurso: TenantResourceDTO): Inquilino {
  return {
    id: String(recurso.id),
    nome: recurso.nome,
    cpf: recurso.cpf,
    rg: recurso.rg ?? undefined,
    email: recurso.email,
    telefone: recurso.telefone,
    dataNascimento: recurso.dataNascimento,
    estadoCivil: recurso.estadoCivil,
    profissao: recurso.profissao ?? undefined,
    renda: recurso.renda ?? 0,
    endereco: recurso.endereco,
    dataCadastro: recurso.dataCadastro ?? undefined,
    idade: recurso.idade,
    contratoAtivo: recurso.contratoAtivo ?? null,
  };
}

export async function listTenants(
  filters: TenantListFilters = {},
): Promise<TenantListResult> {
  const resposta = await api.get<TenantPageEnvelope>("/inquilinos", {
    query: {
      situacao:
        filters.situacao && filters.situacao !== "todos"
          ? filters.situacao
          : undefined,
      texto: filters.texto?.trim() || undefined,
      pagina: filters.pagina,
      tamanhoPagina: filters.tamanhoPagina,
      ordenarPor: filters.ordenarPor ?? "nome",
      direcao: filters.direcao ?? "asc",
    },
  });
  const pagina = resposta.dados;
  return {
    itens: (pagina.itens ?? []).map(mapearTenant),
    pagina: pagina.pagina ?? filters.pagina ?? 1,
    tamanhoPagina: pagina.tamanhoPagina ?? filters.tamanhoPagina ?? 0,
    totalPaginas: pagina.totalPaginas ?? 1,
    total: pagina.total ?? 0,
  };
}

export async function getTenant(id: string): Promise<Inquilino> {
  const resposta = await api.get<TenantResourceEnvelope>(`/inquilinos/${id}`);
  return mapearTenant(resposta.dados);
}

export async function getTenantIndicators(
  id: string,
): Promise<TenantIndicatorsDTO> {
  const resposta = await api.get<TenantIndicatorsEnvelope>(
    `/inquilinos/${id}/indicadores`,
  );
  return resposta.dados;
}

export async function createTenant(
  payload: TenantContractsPayload,
): Promise<Inquilino> {
  const resposta = await api.post<TenantResourceEnvelope>("/inquilinos", payload);
  return mapearTenant(resposta.dados);
}

export async function updateTenant(
  id: string,
  payload: TenantContractsPayload,
): Promise<Inquilino> {
  const resposta = await api.put<TenantResourceEnvelope>(
    `/inquilinos/${id}`,
    payload,
  );
  return mapearTenant(resposta.dados);
}

export async function removeTenant(id: string): Promise<void> {
  await api.delete<void>(`/inquilinos/${id}`);
}
