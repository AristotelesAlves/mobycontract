export interface ApiEnvelope<T> {
  dados: T;
}

export interface ApiPageData<T> {
  pagina?: number;
  tamanhoPagina?: number;
  totalPaginas?: number;
  total?: number;
  itens?: T[];
}

export interface ApiErrorData {
  codigo?: string;
  mensagem?: string;
  campos?: Record<string, string | string[]>;
  detalhe?: string;
}
