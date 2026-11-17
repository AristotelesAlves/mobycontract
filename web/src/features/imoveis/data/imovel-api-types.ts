import type { ApiEnvelope, ApiPageData } from "@/services/api/api-envelope";
export interface ImovelResourceDTO {
    id: number;
    titulo: string;
    tipo: string;
    situacao: string;
    cep: string;
    logradouro: string;
    numero: string;
    complemento: string | null;
    bairro: string | null;
    cidade: string;
    estado: string | null;
    endereco: string;
    area: number;
    quartos: number;
    banheiros: number;
    vagasGaragem: number;
    valorAluguel: number;
    valorIptu: number | null;
    valorCondominio: number | null;
    aguaInclusa: boolean;
    energiaInclusa: boolean;
    internetInclusa: boolean;
    gasIncluso: boolean;
    matricula: string | null;
    descricao: string | null;
    criadoEm: string | null;
    atualizadoEm: string | null;
    nomeProprietario: string | null;
}
export type ImovelResourceEnvelope = ApiEnvelope<ImovelResourceDTO>;
export type ImovelPageEnvelope = ApiEnvelope<ApiPageData<ImovelResourceDTO>>;
export type ImovelContractsPayload = Omit<ImovelResourceDTO, "id" | "situacao" | "endereco" | "criadoEm" | "atualizadoEm" | "complemento" | "bairro" | "estado" | "valorIptu" | "valorCondominio" | "matricula" | "descricao" | "nomeProprietario"> & {
    complemento?: string;
    bairro?: string;
    estado?: string;
    valorIptu?: number;
    valorCondominio?: number;
    matricula?: string;
    descricao?: string;
    nomeProprietario?: string;
};
export interface ImovelSituacaoPayload {
    situacao: string;
}
export type EnderecoCepEnvelope = ApiEnvelope<{
    encontrado: boolean;
    logradouro: string;
    bairro: string;
    cidade: string;
    estado: string;
}>;
export interface AnotacaoResourceDTO {
    id: number;
    imovelId: number;
    texto: string;
    autorId: number;
    autorNome: string | null;
    criadoEm: string | null;
}
export type AnotacaoResourceEnvelope = ApiEnvelope<AnotacaoResourceDTO>;
export type AnotacoesEnvelope = ApiEnvelope<{
    itens: AnotacaoResourceDTO[];
}>;
export interface AgendamentoResourceDTO {
    id: number;
    imovelId: number;
    data: string;
    hora: string;
    interessado: string;
    telefone: string | null;
    status: string;
    criadoEm: string | null;
}
export type AgendamentoResourceEnvelope = ApiEnvelope<AgendamentoResourceDTO>;
export type AgendamentosEnvelope = ApiEnvelope<{
    itens: AgendamentoResourceDTO[];
}>;
export interface InteressadoResourceDTO {
    id: number;
    imovelId: number;
    nome: string;
    telefone: string;
    email: string | null;
    tipo: string;
    status: string;
    criadoEm: string | null;
}
export type InteressadoResourceEnvelope = ApiEnvelope<InteressadoResourceDTO>;
export type InteressadosEnvelope = ApiEnvelope<{
    itens: InteressadoResourceDTO[];
}>;
export interface ManutencaoResourceDTO {
    id: number;
    imovelId: number;
    titulo: string;
    descricao: string | null;
    responsavel: string | null;
    custo: number;
    status: string;
    criadoEm: string | null;
}
export type ManutencaoResourceEnvelope = ApiEnvelope<ManutencaoResourceDTO>;
export type ManutencoesEnvelope = ApiEnvelope<{
    itens: ManutencaoResourceDTO[];
}>;
