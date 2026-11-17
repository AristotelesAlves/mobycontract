export type ImovelTipo = "Apartamento" | "Casa" | "Studio" | "Cobertura" | "Comercial" | "Terreno";
export type ImovelStatus = "Disponível" | "Alugado" | "Vendido" | "Em negociação";
export interface Imovel {
    id: string;
    codigo: string;
    titulo: string;
    nomeProprietario?: string;
    tipo: ImovelTipo;
    status: ImovelStatus;
    matricula?: string;
    endereco: {
        cep: string;
        logradouro: string;
        numero: string;
        complemento?: string;
        bairro: string;
        cidade: string;
        estado: string;
    };
    valorAluguel: number;
    valorCondominio: number;
    valorIptu: number;
    area: number;
    quartos: number;
    banheiros: number;
    vagas: number;
    descricao: string;
    caracteristicas: string[];
    proprietarioId: string;
    criadoEm: string;
    atualizadoEm: string;
}
export interface AgendamentoVisita {
    id: string;
    imovelId: string;
    data: string;
    hora: string;
    interessado: string;
    telefone?: string;
    status: "Agendado" | "Realizado" | "Cancelado";
    criadoEm: string;
}
export interface AnotacaoImovel {
    id: string;
    imovelId: string;
    texto: string;
    autorNome: string | null;
    criadoEm: string;
}
export type InteressadoTipo = "Comprar" | "Alugar" | "Visitar";
export type InteressadoStatus = "Novo" | "Em contato" | "Desistiu" | "Fechou";
export interface InteressadoImovel {
    id: string;
    imovelId: string;
    nome: string;
    telefone: string;
    email?: string;
    tipo: InteressadoTipo;
    status: InteressadoStatus;
    criadoEm: string;
}
export type ManutencaoStatus = "Aberto" | "Em andamento" | "Concluido" | "Cancelado";
export interface ManutencaoImovel {
    id: string;
    imovelId: string;
    titulo: string;
    descricao?: string;
    responsavel?: string;
    custo?: number;
    status: ManutencaoStatus;
    criadoEm: string;
}
