export type ContratoStatus = "Rascunho" | "Minuta" | "Pendente" | "Validado" | "Vigente" | "Encerrado" | "Cancelado";
export type StatusAditivo = "Rascunho" | "Pendente de assinatura" | "Assinado" | "Validado" | "Cancelado";
const MOTIVOS_ADITIVO_VALORES = ["Vigência", "Cláusula", "Valor", "Taxa de Condomínio", "IPTU", "Seguro Incêndio", "Água", "Energia"] as const;
export type MotivoAditivo = (typeof MOTIVOS_ADITIVO_VALORES)[number];
export const MOTIVOS_ADITIVO = MOTIVOS_ADITIVO_VALORES.map((value) => ({ value, label: value }));
export interface FiadorContrato {
    id: string;
    nome: string;
    cpf: string;
    email: string;
    telefone: string;
    renda: number;
    criadoEm: string;
}
export interface ContratoAssinatura {
    id: string;
    papel: string;
    nome: string;
    assinado: boolean;
    dataAssinatura?: string;
}
export interface ContratoHistorico {
    id: string;
    data: string;
    evento: string;
    detalhe: string;
}
export interface ContratoAlteracao {
    id: string;
    contratoId: string;
    data: string;
    usuario: string;
    acao: string;
    campo: string;
    valorAnterior: string;
    valorNovo: string;
    alteradoPor: string;
    alteradoEm: string;
    de: string;
    para: string;
}
export interface ParcelaEncargo {
    id: string;
    tipo: "multa" | "juros" | "taxa" | "desconto";
    valor: number;
    motivo: string;
    criadoEm: string;
}
export interface ParcelaFinanceira {
    id: string;
    contratoId: string;
    referencia?: string;
    numero: number;
    valor: number;
    dataVencimento: string;
    status: "pendente" | "pago" | "atrasado";
    juros?: number;
    taxasAdicionais?: number;
    valorTotal: number;
    valorPago?: number;
    dataPagamento?: string;
    pagoEm?: string;
    encargos?: ParcelaEncargo[];
}
export interface ContratoSnapshot {
    versao: number;
    capturadoEm: string;
    imovel: {
        titulo: string;
        endereco: string | null;
        cidade: string;
        matricula: string | null;
        valor_aluguel: number;
        valor_iptu: number | null;
        valor_condominio: number | null;
        area: number;
        quartos: number;
        banheiros: number;
        vagasGaragem: number;
    };
    inquilino: {
        nome: string;
        cpf: string;
        rg: string | null;
        email: string;
        telefone: string;
        endereco: string;
        estado_civil: string;
        renda: number;
    };
    locador: {
        nome: string;
    };
    fiadores: {
        nome: string;
        cpf: string;
        telefone: string;
    }[];
    modelo: {
        titulo: string;
        versao: number;
        conteudo_html: string;
        margens?: import("@/features/modelos/services/modelos-service").MargensModelo;
        tipografia?: import("@/features/modelos/services/modelos-service").TipografiaModelo;
    };
    condicoesIniciais: {
        valorMensal: number;
        descontoMensal: number;
        garantia: string;
        valorCaucao: number | null;
        dataInicio: string;
        dataFim: string | null;
        prazoVigencia: string;
        indiceReajuste: string;
        multaPorAtraso: number;
        multaRescisao: number;
        diaVencimentoParcela: number;
        observacoes: string | null;
    };
}
export interface Contrato {
    id: string;
    numero: string;
    status: ContratoStatus;
    dataInicio: string;
    dataFim: string;
    dataVencimento: number;
    valorAluguel: number;
    valorMensal?: number;
    descontoMensal?: number;
    multaPorAtraso?: number;
    multaRescisao: number;
    imovelId: string;
    imovelTitulo?: string;
    imovelEndereco?: string;
    imovelCidade?: string;
    imovelMatricula?: string;
    imovelArea?: number;
    imovelQuartos?: number;
    imovelBanheiros?: number;
    imovelVagas?: number;
    inquilinoId: string;
    inquilinoNome?: string;
    proprietarioId: string;
    modeloId?: string;
    modeloTitulo?: string;
    garantia?: string;
    valorCaucao?: number;
    destinacao?: string;
    prazoVigencia?: string;
    indiceReajuste?: string;
    responsabilidadeIptu?: string;
    valorEstimadoIptu?: number;
    responsabilidadeCondominio?: string;
    valorEstimadoCondominio?: number;
    consumoAgua?: string;
    consumoLuz?: string;
    consumoGas?: string;
    observacoes?: string;
    snapshot?: ContratoSnapshot | null;
    historico?: ContratoHistorico[];
    anexoContratoNome?: string;
    hashBlockchain?: string;
    dataRegistroBlockchain?: string;
    statusBlockchain?: string;
    motivoCancelamento?: string;
    documentoFisicoConfirmado?: boolean;
    diaVencimentoParcela?: number;
    criadoEm: string;
    atualizadoEm: string;
    fiadores?: FiadorContrato[];
    fiador?: FiadorContrato;
    imovelTipo?: string;
    responsavel?: string;
    redeBlockchain?: string;
    aditivos?: ContratoAditivo[];
    segundoFiador?: FiadorContrato;
    assinaturas?: ContratoAssinatura[];
    parcelas?: ParcelaFinanceira[];
}
export interface ContratoAditivo {
    id: string;
    contratoId: string;
    numero: string;
    titulo: string;
    tipo: string;
    descricao: string;
    data: string;
    status: StatusAditivo;
    criadoPor: string;
    motivos: MotivoAditivo[];
    modeloId?: string;
    modeloTitulo?: string;
    dadosExtras?: Record<string, unknown> | ({
        label: string;
        valor: string;
    } | {
        rotulo: string;
        valor: string;
    })[];
    uploadConfirmado?: boolean;
    anexoNome?: string;
    criadoEm: string;
    assinadoEm?: string;
    hashBlockchain?: string;
    assinaturas?: ContratoAssinatura[];
    snapshot?: {
        modelo: ContratoSnapshot["modelo"];
    } | null;
}
