import type { MargensModelo, TipografiaModelo } from "../services/modelos-service";
export interface IModeloClonar {
    uuid: string;
    codigo: string | number;
    titulo: string;
    tipo: string;
    status: boolean;
    versao: number;
    conteudoHtml: string;
    garantia?: string;
    margens?: MargensModelo;
    tipografia?: TipografiaModelo;
    podeAlterar?: boolean;
    vinculadoAContrato?: boolean;
}
