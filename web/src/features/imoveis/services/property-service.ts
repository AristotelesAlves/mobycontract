import { api } from "@/services/api/base-api";
import type { Imovel } from "../data/imovel-types";
import type {
  AgendamentoResourceDTO,
  AgendamentoResourceEnvelope,
  AgendamentosEnvelope,
  AnotacaoResourceDTO,
  AnotacaoResourceEnvelope,
  AnotacoesEnvelope,
  EnderecoCepEnvelope,
  ImovelContractsPayload,
  ImovelPageEnvelope,
  ImovelResourceDTO,
  ImovelResourceEnvelope,
  ImovelSituacaoPayload,
  InteressadoResourceDTO,
  InteressadoResourceEnvelope,
  InteressadosEnvelope,
  ManutencaoResourceDTO,
  ManutencaoResourceEnvelope,
  ManutencoesEnvelope,
} from "../data/imovel-api-types";

export interface PropertiesListFilters {
  status?: Imovel["status"] | "todos";
  tipo?: Imovel["tipo"];
  texto?: string;
  pagina?: number;
  tamanhoPagina?: number;
  ordenarPor?: "titulo" | "tipo" | "status" | "area" | "valor_aluguel" | "created_at";
  direcao?: "asc" | "desc";
}

export interface PropertiesListResult {
  itens: Imovel[];
  pagina: number;
  totalPaginas: number;
  total: number;
}

const SITUACAO_PARA_DOMINIO: Record<string, Imovel["status"]> = {
  "Disponível": "Disponível",
  "Alugado": "Alugado",
  "Em negociação": "Em negociação",
  "Vendido": "Vendido",
};

const DOMINIO_PARA_SITUACAO: Partial<Record<Imovel["status"], string>> = {
  "Disponível": "Disponível",
  "Alugado": "Alugado",
  "Em negociação": "Em negociação",
  "Vendido": "Vendido",
};

function mapearImovel(recurso: ImovelResourceDTO): Imovel {
  const caracteristicas: string[] = [];
  if (recurso.aguaInclusa) caracteristicas.push("agua");
  if (recurso.energiaInclusa) caracteristicas.push("energia");
  if (recurso.internetInclusa) caracteristicas.push("internet");
  if (recurso.gasIncluso) caracteristicas.push("gas");

  const tipo = recurso.tipo as Imovel["tipo"];
  const status = SITUACAO_PARA_DOMINIO[recurso.situacao] ?? "Disponível";

  return {
    id: String(recurso.id),
    codigo: String(recurso.matricula ?? recurso.id),
    titulo: recurso.titulo,
    nomeProprietario: recurso.nomeProprietario ?? undefined,
    tipo,
    status,
    matricula: recurso.matricula ?? undefined,
    endereco: {
      cep: recurso.cep,
      logradouro: recurso.logradouro,
      numero: recurso.numero,
      complemento: recurso.complemento ?? undefined,
      bairro: recurso.bairro ?? "",
      cidade: recurso.cidade,
      estado: recurso.estado ?? "",
    },
    valorAluguel: recurso.valorAluguel ?? 0,
    valorCondominio: recurso.valorCondominio ?? 0,
    valorIptu: recurso.valorIptu ?? 0,
    area: recurso.area ?? 0,
    quartos: recurso.quartos ?? 0,
    banheiros: recurso.banheiros ?? 0,
    vagas: recurso.vagasGaragem ?? 0,
    descricao: recurso.descricao ?? "",
    caracteristicas,
    proprietarioId: "",
    criadoEm: recurso.criadoEm ?? "",
    atualizadoEm: recurso.atualizadoEm ?? "",
  };
}

function mapearPayload(dados: ImovelContractsPayload): unknown {
  return {
    titulo: dados.titulo,
    nomeProprietario: dados.nomeProprietario,
    tipo: dados.tipo,
    cep: dados.cep,
    logradouro: dados.logradouro,
    numero: dados.numero,
    complemento: dados.complemento ?? null,
    bairro: dados.bairro ?? null,
    cidade: dados.cidade,
    estado: dados.estado ?? null,
    area: dados.area,
    quartos: dados.quartos,
    banheiros: dados.banheiros,
    vagasGaragem: dados.vagasGaragem,
    valorAluguel: dados.valorAluguel,
    valorIptu: dados.valorIptu ?? null,
    valorCondominio: dados.valorCondominio ?? null,
    aguaInclusa: dados.aguaInclusa ?? null,
    energiaInclusa: dados.energiaInclusa ?? null,
    internetInclusa: dados.internetInclusa ?? null,
    gasIncluso: dados.gasIncluso ?? null,
    matricula: dados.matricula ?? null,
    descricao: dados.descricao ?? null,
  };
}

export async function listProperties(
  filters: PropertiesListFilters = {},
): Promise<PropertiesListResult> {
  const resposta = await api.get<ImovelPageEnvelope>("/imoveis", {
    query: {
      status:
        filters.status && filters.status !== "todos"
          ? DOMINIO_PARA_SITUACAO[filters.status]
          : undefined,
      tipo: filters.tipo,
      texto: filters.texto?.trim() || undefined,
      pagina: filters.pagina,
      tamanhoPagina: filters.tamanhoPagina,
      ordenarPor: filters.ordenarPor ?? "created_at",
      direcao: filters.direcao ?? "desc",
    },
  });
  const pagina = resposta.dados;
  return {
    itens: (pagina.itens ?? []).map(mapearImovel),
    pagina: pagina.pagina ?? filters.pagina ?? 1,
    totalPaginas: pagina.totalPaginas ?? 1,
    total: pagina.total ?? 0,
  };
}

export async function getProperty(id: string): Promise<Imovel> {
  const resposta = await api.get<ImovelResourceEnvelope>(`/imoveis/${id}`);
  return mapearImovel(resposta.dados);
}

export async function createProperty(
  payload: ImovelContractsPayload,
): Promise<Imovel> {
  const resposta = await api.post<ImovelResourceEnvelope>(
    "/imoveis",
    mapearPayload(payload),
  );
  return mapearImovel(resposta.dados);
}

export async function updateProperty(
  id: string,
  payload: ImovelContractsPayload,
): Promise<Imovel> {
  const resposta = await api.put<ImovelResourceEnvelope>(
    `/imoveis/${id}`,
    mapearPayload(payload),
  );
  return mapearImovel(resposta.dados);
}

export async function changePropertyStatus(
  id: string,
  payload: ImovelSituacaoPayload,
): Promise<Imovel> {
  const resposta = await api.put<ImovelResourceEnvelope>(
    `/imoveis/${id}/situacao`,
    payload,
  );
  return mapearImovel(resposta.dados);
}

export async function removeProperty(id: string): Promise<void> {
  await api.delete<void>(`/imoveis/${id}`);
}

export async function fetchAddressByPostalCode(
  postalCode: string,
): Promise<{ street: string; district: string; city: string; state: string } | null> {
  const resposta = await api.get<EnderecoCepEnvelope>(
    `/imoveis/enderecos/${postalCode}`,
  );
  if (!resposta.dados.encontrado) return null;
  return {
    street: resposta.dados.logradouro,
    district: resposta.dados.bairro,
    city: resposta.dados.cidade,
    state: resposta.dados.estado,
  };
}

function mapearAnotacao(recurso: AnotacaoResourceDTO) {
  return {
    id: String(recurso.id),
    imovelId: String(recurso.imovelId),
    texto: recurso.texto,
    autorNome: recurso.autorNome,
    criadoEm: recurso.criadoEm ?? "",
  };
}

export async function listPropertyNotes(
  imovelId: string,
): Promise<ReturnType<typeof mapearAnotacao>[]> {
  const resposta = await api.get<AnotacoesEnvelope>(
    `/imoveis/${imovelId}/anotacoes`,
  );
  return (resposta.dados.itens ?? []).map(mapearAnotacao);
}

export async function createPropertyNote(
  imovelId: string,
  texto: string,
): Promise<ReturnType<typeof mapearAnotacao>> {
  const resposta = await api.post<AnotacaoResourceEnvelope>(
    `/imoveis/${imovelId}/anotacoes`,
    { texto },
  );
  return mapearAnotacao(resposta.dados);
}

export async function deletePropertyNote(
  imovelId: string,
  anotacaoId: string,
): Promise<void> {
  await api.delete<void>(`/imoveis/${imovelId}/anotacoes/${anotacaoId}`);
}

function mapearAgendamento(recurso: AgendamentoResourceDTO) {
  return {
    id: String(recurso.id),
    imovelId: String(recurso.imovelId),
    data: recurso.data,
    hora: recurso.hora,
    interessado: recurso.interessado,
    telefone: recurso.telefone ?? undefined,
    status: recurso.status as "Agendado" | "Realizado" | "Cancelado",
    criadoEm: recurso.criadoEm ?? "",
  };
}

export async function listPropertyVisits(
  imovelId: string,
): Promise<ReturnType<typeof mapearAgendamento>[]> {
  const resposta = await api.get<AgendamentosEnvelope>(
    `/imoveis/${imovelId}/agendamentos`,
  );
  return (resposta.dados.itens ?? []).map(mapearAgendamento);
}

export async function createPropertyVisit(
  imovelId: string,
  payload: { data: string; hora: string; interessado: string; telefone?: string },
): Promise<ReturnType<typeof mapearAgendamento>> {
  const resposta = await api.post<AgendamentoResourceEnvelope>(
    `/imoveis/${imovelId}/agendamentos`,
    payload,
  );
  return mapearAgendamento(resposta.dados);
}

export async function changePropertyVisitStatus(
  imovelId: string,
  agendamentoId: string,
  status: "Agendado" | "Realizado" | "Cancelado",
): Promise<ReturnType<typeof mapearAgendamento>> {
  const resposta = await api.put<AgendamentoResourceEnvelope>(
    `/imoveis/${imovelId}/agendamentos/${agendamentoId}/status`,
    { status },
  );
  return mapearAgendamento(resposta.dados);
}

function mapearInteressado(recurso: InteressadoResourceDTO) {
  return {
    id: String(recurso.id),
    imovelId: String(recurso.imovelId),
    nome: recurso.nome,
    telefone: recurso.telefone,
    email: recurso.email ?? undefined,
    tipo: recurso.tipo as "Comprar" | "Alugar" | "Visitar",
    status: recurso.status as "Novo" | "Em contato" | "Desistiu" | "Fechou",
    criadoEm: recurso.criadoEm ?? "",
  };
}

export async function listPropertyProspects(
  imovelId: string,
): Promise<ReturnType<typeof mapearInteressado>[]> {
  const resposta = await api.get<InteressadosEnvelope>(
    `/imoveis/${imovelId}/interessados`,
  );
  return (resposta.dados.itens ?? []).map(mapearInteressado);
}

export async function createPropertyProspect(
  imovelId: string,
  payload: { nome: string; telefone: string; email?: string; tipo: string },
): Promise<ReturnType<typeof mapearInteressado>> {
  const resposta = await api.post<InteressadoResourceEnvelope>(
    `/imoveis/${imovelId}/interessados`,
    payload,
  );
  return mapearInteressado(resposta.dados);
}

export async function changePropertyProspectStatus(
  imovelId: string,
  interessadoId: string,
  status: "Novo" | "Em contato" | "Desistiu" | "Fechou",
): Promise<ReturnType<typeof mapearInteressado>> {
  const resposta = await api.put<InteressadoResourceEnvelope>(
    `/imoveis/${imovelId}/interessados/${interessadoId}/status`,
    { status },
  );
  return mapearInteressado(resposta.dados);
}

function mapearManutencao(recurso: ManutencaoResourceDTO) {
  return {
    id: String(recurso.id),
    imovelId: String(recurso.imovelId),
    titulo: recurso.titulo,
    descricao: recurso.descricao ?? undefined,
    responsavel: recurso.responsavel ?? undefined,
    custo: recurso.custo ?? undefined,
    status: recurso.status as
      | "Aberto"
      | "Em andamento"
      | "Concluido"
      | "Cancelado",
    criadoEm: recurso.criadoEm ?? "",
  };
}

export async function listPropertyMaintenances(
  imovelId: string,
): Promise<ReturnType<typeof mapearManutencao>[]> {
  const resposta = await api.get<ManutencoesEnvelope>(
    `/imoveis/${imovelId}/manutencoes`,
  );
  return (resposta.dados.itens ?? []).map(mapearManutencao);
}

export async function createPropertyMaintenance(
  imovelId: string,
  payload: {
    titulo: string;
    descricao?: string;
    responsavel?: string;
    custo?: number;
  },
): Promise<ReturnType<typeof mapearManutencao>> {
  const resposta = await api.post<ManutencaoResourceEnvelope>(
    `/imoveis/${imovelId}/manutencoes`,
    payload,
  );
  return mapearManutencao(resposta.dados);
}

export async function updatePropertyMaintenance(
  imovelId: string,
  manutencaoId: string,
  payload: {
    titulo?: string;
    descricao?: string;
    responsavel?: string;
    custo?: number;
    status?: "Aberto" | "Em andamento" | "Concluido" | "Cancelado";
  },
): Promise<ReturnType<typeof mapearManutencao>> {
  const resposta = await api.put<ManutencaoResourceEnvelope>(
    `/imoveis/${imovelId}/manutencoes/${manutencaoId}`,
    payload,
  );
  return mapearManutencao(resposta.dados);
}

export async function uploadPropertyPhoto(id: string, foto: File): Promise<void> {
  const dados = new FormData();
  dados.append("foto", foto);
  await api.post(`/imoveis/${id}/fotos`, dados);
}
