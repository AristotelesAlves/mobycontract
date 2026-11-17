import type { ApiEnvelope, ApiPageData } from "@/services/api/api-envelope";
import type { ContratoAtivoInquilino } from "./inquilino-types";
export interface TenantResourceDTO {
    id: number;
    nome: string;
    cpf: string;
    tipoPessoa: string;
    rg: string | null;
    email: string;
    telefone: string;
    dataNascimento: string;
    estadoCivil: string;
    profissao: string | null;
    renda: number;
    endereco: string;
    clienteId: number | null;
    dataCadastro: string | null;
    idade: number;
    contratoAtivo: ContratoAtivoInquilino | null;
}
export interface TenantIndicatorsDTO {
    idade: number;
    possuiContratoAtivo: boolean;
    compromissoRenda: number | null;
    limiteSeguro: number;
    alertaCompromisso: boolean;
    contratosVigentes: number;
    contratosEncerrados: number;
    parcelasAtrasadas: number;
}
export type TenantResourceEnvelope = ApiEnvelope<TenantResourceDTO>;
export type TenantPageEnvelope = ApiEnvelope<ApiPageData<TenantResourceDTO>>;
export type TenantIndicatorsEnvelope = ApiEnvelope<TenantIndicatorsDTO>;
export type TenantContractFilter = "todos" | "com_contrato" | "sem_contrato";
export type TenantOrderDirection = "asc" | "desc";
export type TenantOrderField = "nome" | "created_at" | "data_cadastro" | "renda";
export interface TenantContractsPayload {
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
}
