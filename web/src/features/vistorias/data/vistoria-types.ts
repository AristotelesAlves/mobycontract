export const VISTORIA_STATUS = ["Agendada", "Em andamento", "Concluída", "Cancelada"] as const;
export type VistoriaStatus = (typeof VISTORIA_STATUS)[number];
export const VISTORIA_TIPOS = ["Entrada", "Retirada", "Periódica"] as const;
export type VistoriaTipo = (typeof VISTORIA_TIPOS)[number];
export const VISTORIA_ESTADOS_ITEM = ["Ótimo", "Bom", "Regular", "Ruim", "Substituir"] as const;
export type VistoriaEstadoItem = (typeof VISTORIA_ESTADOS_ITEM)[number];
export const TRANSICOES_VISTORIA: Record<VistoriaStatus, VistoriaStatus[]> = {
    Agendada: ["Em andamento", "Concluída", "Cancelada"],
    "Em andamento": ["Concluída", "Cancelada"], "Concluída": [], Cancelada: [],
};
export interface VistoriaItem {
    id?: string;
    ambiente: string;
    item: string;
    estado: VistoriaEstadoItem;
    observacao?: string;
}
export interface VistoriaFoto {
    id: string;
    descricao?: string;
    nomeAnexo?: string;
    anexo?: string;
}
export interface Vistoria {
    id: string;
    contratoId: string;
    contratoNumero?: string;
    imovelTitulo?: string;
    numero: string;
    tipo: VistoriaTipo;
    data: string;
    status: VistoriaStatus;
    vistoriador?: string;
    podeAlterar?: boolean;
    podeExcluir?: boolean;
    observacoes?: string;
    chaveAcesso?: string;
    itens: VistoriaItem[];
    fotos: VistoriaFoto[];
    criadoEm?: string;
    atualizadoEm?: string;
}
export type VistoriaStatusFilter = "todos" | VistoriaStatus;
