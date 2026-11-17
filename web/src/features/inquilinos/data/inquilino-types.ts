export interface ContratoAtivoInquilino {
    id: number;
    numero: string;
    imovelTitulo: string;
    valorMensal: number;
    status: string;
}
export interface Inquilino {
    id: string;
    nome: string;
    cpf: string;
    rg?: string;
    email: string;
    telefone: string;
    dataNascimento: string;
    estadoCivil: string;
    profissao?: string;
    renda: number;
    endereco: string;
    dataCadastro?: string;
    idade: number;
    contratoAtivo: ContratoAtivoInquilino | null;
}
