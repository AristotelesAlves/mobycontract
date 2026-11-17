import { api } from "@/services/api/base-api";
import type { IModeloClonar } from "../data/modelo-types";

interface RespostaPaginada<T> {
  dados?: {
    itens?: T[];
  };
}

interface RespostaRecurso<T> {
  dados?: T;
}

interface ModeloResource extends Omit<IModeloClonar, "uuid" | "status"> {
  id: number | string;
  ativo: boolean;
}

function mapearModelo(item: ModeloResource): IModeloClonar {
  return { uuid: String(item.id), codigo: item.codigo, titulo: item.titulo, tipo: item.tipo,
    status: item.ativo, versao: item.versao, conteudoHtml: item.conteudoHtml,
    garantia: item.garantia, margens: item.margens, tipografia: item.tipografia,
    podeAlterar: item.podeAlterar, vinculadoAContrato: item.vinculadoAContrato };
}

export async function getModelTemplate(id: string): Promise<IModeloClonar> {
  const resposta = await api.get<RespostaRecurso<ModeloResource>>(`/modelos/${encodeURIComponent(id)}`);
  if (!resposta.dados) throw new Error("Não foi possível carregar o modelo salvo.");
  return mapearModelo(resposta.dados);
}

export async function updateModelTemplate(id: string, payload: CriarModeloPayload): Promise<void> {
  await api.put(`/modelos/${encodeURIComponent(id)}`, payload);
}

export interface MargensModelo {
  superior: number;
  direita: number;
  inferior: number;
  esquerda: number;
}

export interface TipografiaModelo {
  corpo: number;
  entrelinha: number;
}

export interface CriarModeloPayload {
  titulo: string;
  tipo: string;
  versao: number;
  conteudoHtml: string;
  garantia?: string;
  margens: MargensModelo;
  tipografia: TipografiaModelo;
}

export async function listModelTemplates(): Promise<IModeloClonar[]> {
  const resposta = await api.get<RespostaPaginada<ModeloResource>>("/modelos");
  const itens = resposta.dados?.itens ?? [];
  return Array.isArray(itens)
    ? itens.map(mapearModelo)
    : [];
}

export async function createModelTemplate(
  payload: CriarModeloPayload,
): Promise<IModeloClonar | undefined> {
  const resposta = await api.post<RespostaRecurso<IModeloClonar>>(
    "/modelos",
    payload,
  );
  return resposta.dados;
}
