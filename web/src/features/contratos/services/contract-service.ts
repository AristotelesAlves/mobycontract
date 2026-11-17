import { HttpError } from "@/core/errors/http-error";
import { api } from "@/services/api/base-api";
import type {
  Contrato,
  ContratoAditivo,
  ContratoAssinatura,
  FiadorContrato,
  MotivoAditivo,
  ParcelaFinanceira,
  StatusAditivo,
} from "../data/contrato-types";
import type {
  ContratoResourceDTO,
  ContratoAssinaturaDTO,
  ContratoFiadorDTO,
  ContratoPageEnvelope,
  ContratoResourceEnvelope,
  ParcelaPageEnvelope,
  ParcelaResourceDTO,
  ParcelaResourceEnvelope,
} from "../data/contrato-api-types";
import type { ApiEnvelope, ApiPageData } from "@/services/api/api-envelope";

export interface ContractsListFilters {
  inquilinoId?: string;
  imovelId?: number;
  status?: Contrato["status"];
  texto?: string;
  pagina?: number;
  tamanhoPagina?: number;
  ordenarPor?: "numero" | "status" | "valor_mensal" | "data_inicio" | "created_at";
  direcao?: "asc" | "desc";
}

export interface ContractsListResult {
  itens: Contrato[];
  pagina: number;
  totalPaginas: number;
  total: number;
}

export interface CriarContratoPayload {
  comprovanteGarantia?: File;
  imovelId: number;
  modeloId: number;
  prazoVigencia: string;
  dataInicio: string;
  dataFim?: string;
  indiceReajuste: string;
  modoInquilino: "existente" | "novo";
  inquilinoId?: number;
  inquilino?: {
    nome: string;
    cpf: string;
    rg?: string;
    email: string;
    telefone: string;
    dataNascimento: string;
    estadoCivil: string;
    profissao?: string;
    renda: number;
    endereco?: string;
  };
  valorCaucao?: number;
  fiadores?: Array<{ nome: string; cpf: string; telefone: string }>;
  nomeLocador: string;
  descontoMensal: number;
  multaPorAtraso: number;
  multaRescisao?: number;
  observacoes?: string;
  diaVencimentoParcela?: number;
}

export interface AlterarContratoPayload {
  descontoMensal?: number;
  multaPorAtraso?: number;
  multaRescisao?: number;
  valorCaucao?: number;
  observacoes?: string;
  fiadores?: Array<{ nome: string; cpf: string; telefone: string }>;
}

export interface RegistrarPagamentoPayload {
  corrigir?: boolean;
  dataPagamento: string;
  valorPago?: number;
}

export interface LancarEncargoPayload {
  tipo: "multa" | "juros" | "taxa";
  valor: number;
  motivo: string;
}

export type StatusAditivoApi =
  | "Rascunho"
  | "Pendente de assinatura"
  | "Assinado"
  | "Validado"
  | "Cancelado";

export interface AditivoResourceDTO {
  snapshot?: ContratoAditivo["snapshot"];
  id: number;
  contratoId: number;
  numero: number | string;
  titulo: string;
  data: string | null;
  descricao: string | null;
  criadoPor: string | null;
  status: StatusAditivoApi | string;
  podeAlterar?: boolean;
  podeExcluir?: boolean;
  motivos: string[];
  modeloId: number | null;
  modeloTitulo: string | null;
  dadosExtras: Record<string, unknown> | null;
  uploadConfirmado: boolean | null;
  anexoNome: string | null;
  hashBlockchain: string | null;
  dataRegistroBlockchain: string | null;
  statusBlockchain: string | null;
  criadoEm: string | null;
  atualizadoEm: string | null;
  assinaturas: ContratoAssinaturaDTO[];
}

export type AditivoPageEnvelope = ApiEnvelope<ApiPageData<AditivoResourceDTO>>;
export type AditivoResourceEnvelope = ApiEnvelope<AditivoResourceDTO>;

export interface EtapaResourceDTO {
  id?: number;
  titulo: string;
  concluida: boolean;
  ativa: boolean;
  assinaturaId?: number | string | null;
}

export type EtapasEnvelope = ApiEnvelope<{ itens?: EtapaResourceDTO[] }>;

export interface CriarAditivoPayload {
  titulo: string;
  descricao?: string;
  data?: string;
  modeloId: number;
  motivos: MotivoAditivo[];
  novoValor?: number;
  novaDataFim?: string;
  clausulaModificada?: string;
  novaTaxaCondominio?: number;
  novoIPTU?: number;
  novoSeguroIncendio?: number;
  novoAgua?: number;
  novoEnergia?: number;
}

type ParcelaStatusDominio = ParcelaFinanceira["status"];

const STATUS_PARCELA_PARA_DOMINIO: Record<string, ParcelaStatusDominio> = {
  Pendente: "pendente",
  Pago: "pago",
  Atrasado: "atrasado",
};

function mapearFiador(recurso: ContratoFiadorDTO): FiadorContrato {
  return {
    id: String(recurso.id),
    nome: recurso.nome,
    cpf: recurso.cpf,
    email: "",
    telefone: recurso.telefone,
    renda: 0,
    criadoEm: "",
  };
}

function mapearAssinatura(recurso: ContratoAssinaturaDTO): ContratoAssinatura {
  return {
    id: String(recurso.id),
    papel: recurso.papel,
    nome: recurso.nome,
    assinado: recurso.assinado,
    dataAssinatura: recurso.dataAssinatura ?? undefined,
  };
}

function mapearContrato(recurso: ContratoResourceDTO): Contrato {
  const fiadoresMapeados = (recurso.fiadores ?? []).map(mapearFiador);
  const fiadorDeLinha: FiadorContrato[] = fiadoresMapeados;

  return {
    id: String(recurso.id),
    numero: String(recurso.numero ?? ""),
    status: (recurso.status ?? "Rascunho") as Contrato["status"],
    dataInicio: recurso.dataInicio ?? "",
    dataFim: recurso.dataFim ?? "",
    dataVencimento: recurso.diaVencimentoParcela ?? 5,
    valorAluguel: recurso.valorMensal ?? 0,
    valorMensal: recurso.valorMensal ?? undefined,
    descontoMensal: recurso.descontoMensal ?? undefined,
    multaPorAtraso: recurso.multaPorAtraso ?? undefined,
    multaRescisao: recurso.multaRescisao ?? 0,
    imovelId: recurso.imovelId != null ? String(recurso.imovelId) : "",
    imovelTitulo: recurso.imovelTitulo ?? undefined,
    imovelEndereco: recurso.imovelEndereco ?? undefined,
    imovelCidade: recurso.imovelCidade ?? undefined,
    imovelMatricula: recurso.imovelMatricula ?? undefined,
    imovelArea: recurso.snapshot?.imovel.area ?? recurso.imovelArea ?? undefined,
    imovelQuartos: recurso.snapshot?.imovel.quartos ?? recurso.imovelQuartos ?? undefined,
    imovelBanheiros: recurso.snapshot?.imovel.banheiros ?? recurso.imovelBanheiros ?? undefined,
    imovelVagas: recurso.snapshot?.imovel.vagasGaragem ?? recurso.imovelVagas ?? undefined,
    inquilinoId: recurso.inquilinoId != null ? String(recurso.inquilinoId) : "",
    inquilinoNome: recurso.inquilinoNome ?? undefined,
    proprietarioId: "",
    modeloId: recurso.modeloId != null ? String(recurso.modeloId) : undefined,
    modeloTitulo: recurso.modeloTitulo ?? undefined,
    garantia: recurso.garantia ?? undefined,
    valorCaucao: recurso.valorCaucao ?? undefined,
    destinacao: recurso.destinacao ?? undefined,
    prazoVigencia: recurso.prazoVigencia ?? undefined,
    indiceReajuste: recurso.indiceReajuste ?? undefined,
    responsabilidadeIptu: recurso.responsabilidadeIptu ?? undefined,
    valorEstimadoIptu: recurso.valorEstimadoIptu ?? undefined,
    responsabilidadeCondominio: recurso.responsabilidadeCondominio ?? undefined,
    valorEstimadoCondominio: recurso.valorEstimadoCondominio ?? undefined,
    consumoAgua: recurso.consumoAgua ?? undefined,
    consumoLuz: recurso.consumoLuz ?? undefined,
    consumoGas: recurso.consumoGas ?? undefined,
    observacoes: recurso.observacoes ?? undefined,
    snapshot: recurso.snapshot,
    historico: (recurso.historico ?? []).map((evento) => ({ ...evento, id: String(evento.id) })),
    anexoContratoNome: recurso.anexoContratoNome ?? undefined,
    hashBlockchain: recurso.hashBlockchain ?? undefined,
    dataRegistroBlockchain: recurso.dataRegistroBlockchain ?? undefined,
    statusBlockchain: recurso.statusBlockchain ?? undefined,
    motivoCancelamento: recurso.motivoCancelamento ?? undefined,
    documentoFisicoConfirmado: recurso.documentoFisicoConfirmado ?? undefined,
    diaVencimentoParcela: recurso.diaVencimentoParcela ?? undefined,
    criadoEm: recurso.criadoEm ?? "",
    atualizadoEm: recurso.atualizadoEm ?? "",
    fiadores: fiadoresMapeados,
    fiador: fiadorDeLinha[0] ?? undefined,
    segundoFiador: fiadorDeLinha[1] ?? undefined,
    assinaturas: (recurso.assinaturas ?? []).map(mapearAssinatura),
  };
}

function mapearParcela(recurso: ParcelaResourceDTO): ParcelaFinanceira {
  const statusMapeado =
    STATUS_PARCELA_PARA_DOMINIO[recurso.status] ?? "pendente";
  return {
    referencia: recurso.referencia,
    encargos: (recurso.encargos ?? []).map((encargo) => ({ ...encargo, id: String(encargo.id) })),
    id: String(recurso.id),
    contratoId: String(recurso.contratoId),
    numero: Number.parseInt(recurso.referencia, 10) || 0,
    valor: recurso.valor ?? 0,
    dataVencimento: recurso.vencimento ?? "",
    status: statusMapeado,
    juros: recurso.juros ?? undefined,
    taxasAdicionais: recurso.taxasAdicionais ?? undefined,
    valorTotal: recurso.totalDaParcela ?? recurso.valor ?? 0,
    valorPago: recurso.valorPago ?? undefined,
    dataPagamento: recurso.dataPagamento ?? undefined,
    pagoEm: recurso.pagoEm ?? undefined,
  };
}

function mapearAditivo(recurso: AditivoResourceDTO): ContratoAditivo {
  return {
    snapshot: recurso.snapshot,
    id: String(recurso.id),
    contratoId: String(recurso.contratoId),
    numero: String(recurso.numero ?? ""),
    titulo: recurso.titulo,
    tipo: "outro",
    descricao: recurso.descricao ?? "",
    data: recurso.data ?? "",
    status: recurso.status as StatusAditivo,
    criadoPor: recurso.criadoPor ?? "",
    motivos: (recurso.motivos ?? []) as MotivoAditivo[],
    modeloId: recurso.modeloId != null ? String(recurso.modeloId) : undefined,
    modeloTitulo: recurso.modeloTitulo ?? undefined,
    dadosExtras: recurso.dadosExtras ?? undefined,
    uploadConfirmado: recurso.uploadConfirmado ?? undefined,
    anexoNome: recurso.anexoNome ?? undefined,
    criadoEm: recurso.criadoEm ?? "",
    assinadoEm:
      (recurso.assinaturas ?? []).find((a) => a.assinado)?.dataAssinatura ??
      undefined,
    hashBlockchain: recurso.hashBlockchain ?? undefined,
    assinaturas: (recurso.assinaturas ?? []).map(mapearAssinatura),
  };
}

export async function listContracts(
  filters: ContractsListFilters = {},
): Promise<ContractsListResult> {
  const resposta = await api.get<ContratoPageEnvelope>("/contratos", {
    query: {
      status: filters.status ? filters.status : undefined,
      inquilinoId: filters.inquilinoId,
      imovelId: filters.imovelId,
      texto: filters.texto?.trim() || undefined,
      pagina: filters.pagina,
      tamanhoPagina: filters.tamanhoPagina,
      ordenarPor: filters.ordenarPor ?? "created_at",
      direcao: filters.direcao ?? "desc",
    },
  });
  const pagina = resposta.dados;
  return {
    itens: (pagina.itens ?? []).map(mapearContrato),
    pagina: pagina.pagina ?? filters.pagina ?? 1,
    totalPaginas: pagina.totalPaginas ?? 1,
    total: pagina.total ?? 0,
  };
}

export async function getContract(contractId: string): Promise<Contrato> {
  const resposta = await api.get<ContratoResourceEnvelope>(
    `/contratos/${contractId}`,
  );
  return mapearContrato(resposta.dados);
}

export async function createContract(
  payload: CriarContratoPayload,
): Promise<Contrato> {
  const resposta = await api.post<ContratoResourceEnvelope>(
    "/contratos",
    payload.comprovanteGarantia ? contratoFormData(payload) : payload,
  );
  return mapearContrato(resposta.dados);
}

export async function updateContractDetails(
  contractId: string,
  payload: AlterarContratoPayload,
): Promise<Contrato> {
  const resposta = await api.put<ContratoResourceEnvelope>(
    `/contratos/${contractId}`,
    payload,
  );
  return mapearContrato(resposta.dados);
}

export async function changeContractStatus(
  contractId: string,
  status: string,
  motivo?: string,
): Promise<Contrato> {
  const resposta = await api.put<ContratoResourceEnvelope>(
    `/contratos/${contractId}/status`,
    { status, motivo },
  );
  return mapearContrato(resposta.dados);
}

export async function signContract(
  contractId: string,
  assinaturaId: string,
): Promise<Contrato> {
  const resposta = await api.post<ContratoResourceEnvelope>(
    `/contratos/${contractId}/assinaturas/${assinaturaId}/assinar`,
    {},
  );
  return mapearContrato(resposta.dados);
}

export async function listContractStages(
  contractId: string,
): Promise<EtapaResourceDTO[]> {
  const resposta = await api.get<EtapasEnvelope>(`/contratos/${contractId}/etapas`);
  return resposta.dados.itens ?? [];
}

export async function confirmContractPhysicalDocument(
  contractId: string,
  arquivo?: File | null,
): Promise<Contrato> {
  const resposta = await api.post<ContratoResourceEnvelope>(
    `/contratos/${contractId}/documento-fisico`,
    documentoFormData(arquivo),
  );
  return mapearContrato(resposta.dados);
}

export async function registerContractOnBlockchain(
  contractId: string,
): Promise<Contrato> {
  const resposta = await api.post<ContratoResourceEnvelope>(
    `/contratos/${contractId}/blockchain`,
    {},
  );
  return mapearContrato(resposta.dados);
}

export async function listContractInstallments(
  contractId: string,
): Promise<ParcelaFinanceira[]> {
  const parcelas: ParcelaFinanceira[] = [];
  let pagina = 1;
  let totalPaginas = 1;
  do {
    const resposta = await api.get<ParcelaPageEnvelope>(
      `/contratos/${contractId}/parcelas`, { query: { pagina } },
    );
    parcelas.push(...(resposta.dados.itens ?? []).map(mapearParcela));
    totalPaginas = resposta.dados.totalPaginas ?? 1;
    pagina += 1;
  } while (pagina <= totalPaginas);
  return parcelas;
}

export async function registerInstallmentPayment(
  contractId: string,
  parcelaId: string,
  payload: RegistrarPagamentoPayload,
): Promise<ParcelaFinanceira> {
  const resposta = await api.post<ParcelaResourceEnvelope>(
    `/contratos/${contractId}/parcelas/${parcelaId}/pagamento`,
    payload,
  );
  return mapearParcela(resposta.dados);
}

export async function addInstallmentCharge(
  contractId: string,
  parcelaId: string,
  payload: LancarEncargoPayload,
): Promise<ParcelaFinanceira> {
  const resposta = await api.post<ParcelaResourceEnvelope>(
    `/contratos/${contractId}/parcelas/${parcelaId}/encargos`,
    payload,
  );
  return mapearParcela(resposta.dados);
}

export async function listContractAddenda(
  contractId: string,
): Promise<ContratoAditivo[]> {
  const resposta = await api.get<AditivoPageEnvelope>(
    `/contratos/${contractId}/aditivos`,
  );
  return (resposta.dados.itens ?? []).map(mapearAditivo);
}

export async function getContractAddendum(
  contractId: string,
  aditivoId: string,
): Promise<ContratoAditivo> {
  const resposta = await api.get<AditivoResourceEnvelope>(
    `/contratos/${contractId}/aditivos/${aditivoId}`,
  );
  return mapearAditivo(resposta.dados);
}

export async function createContractAddendum(
  contractId: string,
  payload: CriarAditivoPayload,
): Promise<ContratoAditivo> {
  const resposta = await api.post<AditivoResourceEnvelope>(
    `/contratos/${contractId}/aditivos`,
    payload,
  );
  return mapearAditivo(resposta.dados);
}

export async function updateContractAddendum(
  contractId: string,
  aditivoId: string,
  payload: CriarAditivoPayload,
): Promise<ContratoAditivo> {
  const resposta = await api.put<AditivoResourceEnvelope>(
    `/contratos/${contractId}/aditivos/${aditivoId}`,
    payload,
  );
  return mapearAditivo(resposta.dados);
}

export async function removeContractAddendum(
  contractId: string,
  aditivoId: string,
): Promise<void> {
  await api.delete<void>(`/contratos/${contractId}/aditivos/${aditivoId}`);
}

export async function changeContractAddendumStatus(
  contractId: string,
  aditivoId: string,
  status: StatusAditivoApi,
  motivo?: string,
): Promise<ContratoAditivo> {
  const resposta = await api.put<AditivoResourceEnvelope>(
    `/contratos/${contractId}/aditivos/${aditivoId}/status`,
    { status, motivo },
  );
  return mapearAditivo(resposta.dados);
}

export async function signContractAddendum(
  contractId: string,
  aditivoId: string,
  assinaturaId: string,
): Promise<ContratoAditivo> {
  const resposta = await api.post<AditivoResourceEnvelope>(
    `/contratos/${contractId}/aditivos/${aditivoId}/assinaturas/${assinaturaId}/assinar`,
    {},
  );
  return mapearAditivo(resposta.dados);
}

export async function confirmAddendumPhysicalDocument(
  contractId: string,
  aditivoId: string,
  arquivo?: File | null,
): Promise<ContratoAditivo> {
  const resposta = await api.post<AditivoResourceEnvelope>(
    `/contratos/${contractId}/aditivos/${aditivoId}/documento-fisico`,
    documentoFormData(arquivo),
  );
  return mapearAditivo(resposta.dados);
}

export async function registerAddendumOnBlockchain(
  contractId: string,
  aditivoId: string,
): Promise<ContratoAditivo> {
  const resposta = await api.post<AditivoResourceEnvelope>(
    `/contratos/${contractId}/aditivos/${aditivoId}/blockchain`,
    {},
  );
  return mapearAditivo(resposta.dados);
}

export type DividaStatus = "Em aberto" | "Quitada";

export interface ContratoDivida {
  id: string;
  contratoId: string;
  descricao: string;
  valor: number;
  status: DividaStatus;
  dataQuitacao?: string | null;
  criadoEm?: string | null;
}

interface DividaResourceDTO {
  id: number;
  contratoId: number;
  descricao: string;
  valor: number | string;
  status: string;
  dataQuitacao: string | null;
  criadoEm: string | null;
}

type DividasEnvelope = ApiEnvelope<DividaResourceDTO[]>;
type DividaResourceEnvelope = ApiEnvelope<DividaResourceDTO>;

function mapearDivida(recurso: DividaResourceDTO): ContratoDivida {
  return {
    id: String(recurso.id),
    contratoId: String(recurso.contratoId),
    descricao: recurso.descricao ?? "",
    valor: Number(recurso.valor) || 0,
    status: recurso.status === "Quitada" ? "Quitada" : "Em aberto",
    dataQuitacao: recurso.dataQuitacao,
    criadoEm: recurso.criadoEm,
  };
}

export async function listContractDebts(
  contractId: string,
): Promise<ContratoDivida[]> {
  const resposta = await api.get<DividasEnvelope>(
    `/contratos/${contractId}/dividas`,
  );
  return (resposta.dados ?? []).map(mapearDivida);
}

export async function quitContractDebt(
  contractId: string,
  dividaId: string,
): Promise<ContratoDivida> {
  const resposta = await api.post<DividaResourceEnvelope>(
    `/contratos/${contractId}/dividas/${dividaId}/quitar`,
    {},
  );
  return mapearDivida(resposta.dados);
}

export async function listTenantContractsWithInstallments(
  tenantId: string,
): Promise<Contrato[]> {
  const contratos = await listContracts({
    inquilinoId: tenantId,
    tamanhoPagina: 50,
  });
  return Promise.all(
    contratos.itens.map(async (contrato) => ({
      ...contrato,
      parcelas: await listContractInstallments(contrato.id),
    })),
  );
}

function documentoFormData(arquivo?: File | null): FormData {
  const dados = new FormData();
  if (arquivo) {
    dados.append("anexo", arquivo);
    dados.append("nomeAnexo", arquivo.name);
  }
  return dados;
}

export async function getContractDocument(contractId: string, aditivoId?: string, garantia = false): Promise<Blob> {
  const caminho = garantia ? `/contratos/${contractId}/comprovante-garantia` : aditivoId ? `/contratos/${contractId}/aditivos/${aditivoId}/documento` : `/contratos/${contractId}/documento`;
  let blob: Blob;
  try {
    blob = await api.arquivo(caminho);
  } catch (error) {
    if (error instanceof HttpError && error.statusCode === 404) {
      throw new Error("O arquivo enviado não está disponível no armazenamento. O nome do anexo, sozinho, não permite recuperar o documento.");
    }
    throw error;
  }
  return blob;
}

export async function downloadContractDocument(contractId: string, aditivoId?: string, garantia = false): Promise<void> {
  const blob = await getContractDocument(contractId, aditivoId, garantia);
  const url = URL.createObjectURL(blob);
  const link = document.createElement("a");
  link.href = url;
  const extensao = ({ "application/pdf": "pdf", "image/jpeg": "jpg", "image/png": "png", "image/webp": "webp" } as Record<string, string>)[blob.type] ?? "bin";
  link.download = `documento-${aditivoId ?? contractId}.${extensao}`;
  document.body.appendChild(link);
  link.click();
  link.remove();
  setTimeout(() => URL.revokeObjectURL(url), 1000);
}

function contratoFormData(payload: CriarContratoPayload): FormData {
  const dados = new FormData();
  function adicionar(chave: string, valor: unknown): void {
    if (valor == null) return;
    if (valor instanceof File) dados.append(chave, valor);
    else if (typeof valor === "object") Object.entries(valor).forEach(([campo, item]) => adicionar(`${chave}[${campo}]`, item));
    else dados.append(chave, String(valor));
  }
  Object.entries(payload).forEach(([chave, valor]) => adicionar(chave, valor));
  return dados;
}
