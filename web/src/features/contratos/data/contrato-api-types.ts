import type { ApiEnvelope, ApiPageData } from "@/services/api/api-envelope";
import type { ContratoSnapshot, ContratoHistorico, ParcelaEncargo } from "./contrato-types";
export interface ContratoFiadorDTO {
    id: number;
    nome: string;
    cpf: string;
    telefone: string;
}
export interface ContratoAssinaturaDTO {
    id: number;
    papel: string;
    nome: string;
    assinado: boolean;
    dataAssinatura: string | null;
}
export interface ContratoResourceDTO {
    id: number;
    numero: string;
    tipoModelo: string;
    status: string;
    situacaoEfetiva: string;
    podeAlterar: boolean;
    podeExcluir: boolean;
    imovelId: number;
    imovelTitulo: string;
    imovelEndereco: string | null;
    imovelCidade: string | null;
    imovelMatricula: string | null;
    inquilinoId: number;
    inquilinoNome: string;
    criadoPor: number;
    modeloId: number;
    modeloTitulo: string;
    modeloVersao: number;
    valorMensal: number;
    descontoMensal: number;
    multaPorAtraso: number;
    multaRescisao: number;
    garantia: string;
    valorCaucao: number | null;
    fiadores: ContratoFiadorDTO[];
    destinacao: string;
    prazoVigencia: string;
    dataInicio: string | null;
    dataFim: string | null;
    indiceReajuste: string;
    responsabilidadeIptu: string;
    valorEstimadoIptu: number | null;
    responsabilidadeCondominio: string;
    valorEstimadoCondominio: number | null;
    consumoAgua: string;
    consumoLuz: string;
    consumoGas: string;
    consumoInternet: string;
    anexoContratoNome: string | null;
    observacoes: string | null;
    motivoCancelamento: string | null;
    documentoFisicoConfirmado: boolean;
    diaVencimentoParcela: number;
    assinaturas: ContratoAssinaturaDTO[];
    hashBlockchain: string | null;
    dataRegistroBlockchain: string | null;
    statusBlockchain: string;
    criadoEm: string | null;
    atualizadoEm: string | null;
    snapshot: ContratoSnapshot | null;
    imovelArea: number | null;
    imovelQuartos: number | null;
    imovelBanheiros: number | null;
    imovelVagas: number | null;
    historico: ContratoHistorico[];
    anexoContrato: string | null;
    comprovanteGarantia: string | null;
}
export interface ParcelaResourceDTO {
    id: number;
    contratoId: number;
    referencia: string;
    vencimento: string | null;
    valor: number;
    juros: number;
    taxasAdicionais: number;
    totalDaParcela: number;
    valorPago: number;
    dataPagamento: string | null;
    status: string;
    valorExibido: number;
    pagoEm: string | null;
    encargos: (Omit<ParcelaEncargo, "id"> & {
        id: number;
    })[];
}
export type ContratoResourceEnvelope = ApiEnvelope<ContratoResourceDTO>;
export type ContratoPageEnvelope = ApiEnvelope<ApiPageData<ContratoResourceDTO>>;
export type ParcelaResourceEnvelope = ApiEnvelope<ParcelaResourceDTO>;
export type ParcelaPageEnvelope = ApiEnvelope<ApiPageData<ParcelaResourceDTO>>;
