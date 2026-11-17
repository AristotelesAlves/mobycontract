import { api } from "@/services/api/base-api";
import type { ApiEnvelope, ApiPageData } from "@/services/api/api-envelope";
import {
  VISTORIA_ESTADOS_ITEM,
  VISTORIA_STATUS,
  VISTORIA_TIPOS,
  type Vistoria,
  type VistoriaEstadoItem,
  type VistoriaFoto,
  type VistoriaItem,
  type VistoriaStatus,
  type VistoriaTipo,
} from "../data/vistoria-types";

/**
 * A API expõe vistorias sempre dentro do contrato
 * (`/contratos/{contratoId}/vistorias`); e neste recurso.
 */
export interface VistoriasListFilters {
  contratoId: string;
  status?: VistoriaStatus;
  pagina?: number;
  tamanhoPagina?: number;
}

export interface VistoriasListResult {
  itens: Vistoria[];
  pagina: number;
  totalPaginas: number;
  total: number;
}

export interface ItemVistoriaPayload {
  ambiente: string;
  item: string;
  estado: VistoriaEstadoItem;
  observacao?: string;
}

export interface CriarVistoriaPayload {
  tipo: VistoriaTipo;
  data: string;
  vistoriador?: string;
  observacoes?: string;
  itens?: ItemVistoriaPayload[];
}

export type AlterarVistoriaPayload = Partial<CriarVistoriaPayload>;

export interface FotoVistoriaPayload {
  arquivo: File;
  nomeAnexo: string;
  descricao?: string;
}

interface ItemResourceDTO {
  id?: number | string;
  ambiente?: string;
  item?: string;
  estado?: string;
  observacao?: string | null;
}

interface FotoResourceDTO {
  id?: number | string;
  descricao?: string | null;
  nomeAnexo?: string | null;
  anexo?: string | null;
}

interface VistoriaResourceDTO {
  id: number | string;
  contratoId?: number | string;
  contratoNumero?: string | number | null;
  imovelTitulo?: string | null;
  numero?: string | number;
  tipo?: string;
  data?: string;
  status?: string;
  vistoriador?: string | null;
  podeAlterar?: boolean;
  podeExcluir?: boolean;
  observacoes?: string | null;
  chaveAcesso?: string | null;
  itens?: ItemResourceDTO[];
  fotos?: FotoResourceDTO[];
  criadoEm?: string | null;
  atualizadoEm?: string | null;
}

type VistoriasPageEnvelope = ApiEnvelope<ApiPageData<VistoriaResourceDTO>>;
type VistoriaResourceEnvelope = ApiEnvelope<VistoriaResourceDTO>;
type FotoResourceEnvelope = ApiEnvelope<{ id?: number | string }>;

const STATUS_VALIDOS = new Set<string>(VISTORIA_STATUS);
const TIPOS_VALIDOS = new Set<string>(VISTORIA_TIPOS);
const ESTADOS_VALIDOS = new Set<string>(VISTORIA_ESTADOS_ITEM);

function mapearItem(recurso: ItemResourceDTO): VistoriaItem {
  const estado = recurso.estado ?? "";
  return {
    id: recurso.id != null ? String(recurso.id) : undefined,
    ambiente: recurso.ambiente ?? "",
    item: recurso.item ?? "",
    estado: (ESTADOS_VALIDOS.has(estado)
      ? estado
      : "Bom") as VistoriaEstadoItem,
    observacao: recurso.observacao ?? undefined,
  };
}

function mapearFoto(recurso: FotoResourceDTO): VistoriaFoto {
  return {
    id: String(recurso.id ?? ""),
    descricao: recurso.descricao ?? undefined,
    nomeAnexo: recurso.nomeAnexo ?? undefined,
    anexo: recurso.anexo ?? undefined,
  };
}

function mapearVistoria(recurso: VistoriaResourceDTO): Vistoria {
  const status = STATUS_VALIDOS.has(recurso.status ?? "")
    ? recurso.status
    : "Agendada";
  const tipo = TIPOS_VALIDOS.has(recurso.tipo ?? "") ? recurso.tipo : "Entrada";

  return {
    id: String(recurso.id),
    contratoId:
      recurso.contratoId != null ? String(recurso.contratoId) : "",
    contratoNumero:
      recurso.contratoNumero != null
        ? String(recurso.contratoNumero)
        : undefined,
    imovelTitulo: recurso.imovelTitulo ?? undefined,
    numero: recurso.numero != null ? String(recurso.numero) : "",
    tipo: tipo as VistoriaTipo,
    data: recurso.data ?? "",
    status: status as VistoriaStatus,
    vistoriador: recurso.vistoriador ?? undefined,
    podeAlterar: recurso.podeAlterar ?? undefined,
    podeExcluir: recurso.podeExcluir ?? undefined,
    observacoes: recurso.observacoes ?? undefined,
    chaveAcesso: recurso.chaveAcesso ?? undefined,
    itens: (recurso.itens ?? []).map(mapearItem),
    fotos: (recurso.fotos ?? []).map(mapearFoto),
    criadoEm: recurso.criadoEm ?? undefined,
    atualizadoEm: recurso.atualizadoEm ?? undefined,
  };
}

const base = (contratoId: string, vistoriaId?: string) =>
  `/contratos/${contratoId}/vistorias${vistoriaId ? `/${vistoriaId}` : ""}`;

export async function listVistorias(
  filtros: VistoriasListFilters,
): Promise<VistoriasListResult> {
  const resposta = await api.get<VistoriasPageEnvelope>(
    base(filtros.contratoId),
    {
      query: {
        status: filtros.status,
        pagina: filtros.pagina,
        tamanhoPagina: filtros.tamanhoPagina,
      },
    },
  );

  const pagina = resposta.dados;
  return {
    itens: (pagina.itens ?? []).map(mapearVistoria),
    pagina: pagina.pagina ?? filtros.pagina ?? 1,
    totalPaginas: pagina.totalPaginas ?? 1,
    total: pagina.total ?? 0,
  };
}

export async function getVistoria(
  contratoId: string,
  vistoriaId: string,
): Promise<Vistoria> {
  const resposta = await api.get<VistoriaResourceEnvelope>(
    base(contratoId, vistoriaId),
  );
  return mapearVistoria(resposta.dados);
}

export async function createVistoria(
  contratoId: string,
  payload: CriarVistoriaPayload,
): Promise<Vistoria> {
  const resposta = await api.post<VistoriaResourceEnvelope>(
    base(contratoId),
    payload,
  );
  return mapearVistoria(resposta.dados);
}

export async function updateVistoria(
  contratoId: string,
  vistoriaId: string,
  payload: AlterarVistoriaPayload,
): Promise<Vistoria> {
  const resposta = await api.put<VistoriaResourceEnvelope>(
    base(contratoId, vistoriaId),
    payload,
  );
  return mapearVistoria(resposta.dados);
}

export async function changeVistoriaStatus(
  contratoId: string,
  vistoriaId: string,
  status: VistoriaStatus,
  motivo?: string,
): Promise<Vistoria> {
  const resposta = await api.put<VistoriaResourceEnvelope>(
    `${base(contratoId, vistoriaId)}/status`,
    { status, motivo },
  );
  return mapearVistoria(resposta.dados);
}

export async function deleteVistoria(
  contratoId: string,
  vistoriaId: string,
): Promise<void> {
  await api.delete<void>(base(contratoId, vistoriaId));
}

export async function addVistoriaPhoto(
  contratoId: string,
  vistoriaId: string,
  payload: FotoVistoriaPayload,
): Promise<void> {
  const dados = new FormData();
  dados.append("anexo", payload.arquivo);
  dados.append("nomeAnexo", payload.nomeAnexo);
  if (payload.descricao) dados.append("descricao", payload.descricao);
  await api.post<FotoResourceEnvelope>(
    `${base(contratoId, vistoriaId)}/fotos`,
    dados,
  );
}

export async function removeVistoriaPhoto(
  contratoId: string,
  vistoriaId: string,
  fotoId: string,
): Promise<void> {
  await api.delete<void>(
    `${base(contratoId, vistoriaId)}/fotos/${fotoId}`,
  );
}

/**
 * Acesso público do vistoriador externo (sem autenticação): a chave em
 * UUID atua como token de leitura da vistoria.
 */
type VistoriaAcessoPublicoEnvelope = ApiEnvelope<VistoriaResourceDTO>;

export async function getVistoriaPorChaveAcesso(
  chave: string,
): Promise<Vistoria> {
  const resposta = await api.get<VistoriaAcessoPublicoEnvelope>(
    `/vistorias/acesso/${encodeURIComponent(chave)}`,
    { skipAuth: true },
  );
  return mapearVistoria(resposta.dados);
}

export async function getVistoriaPhoto(contratoId: string, vistoriaId: string, fotoId: string): Promise<Blob> {
  const blob = await api.arquivo(`${base(contratoId, vistoriaId)}/fotos/${fotoId}`);
  if (!["image/jpeg", "image/png", "image/webp"].includes(blob.type)) {
    throw new Error("O arquivo recebido não é uma foto JPG, PNG ou WebP.");
  }
  return blob;
}

export async function downloadVistoriaPhoto(contratoId: string, vistoriaId: string, fotoId: string): Promise<void> {
  const blob = await getVistoriaPhoto(contratoId, vistoriaId, fotoId);
  const url = URL.createObjectURL(blob);
  const link = document.createElement("a");
  link.href = url;
  link.download = `foto-vistoria-${fotoId}.${blob.type.split("/")[1] ?? "bin"}`;
  link.click();
  setTimeout(() => URL.revokeObjectURL(url), 1000);
}
