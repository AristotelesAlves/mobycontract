import { api } from "@/services/api/base-api";
import type { ApiEnvelope } from "@/services/api/api-envelope";

export type ContractVerificationResult = {
  contratoId: number;
  numero: string;
  resultado: "correspondente" | "diferente" | "nao_registrado" | "registro_antigo";
  mensagem: string;
  hashArquivo: string;
  hashRegistrado?: string;
  hashTransacao?: string;
  verificadoEm: string;
};

export type TransactionVerificationResult = {
  resultado: "confirmada" | "nao_encontrada";
  mensagem: string;
  hashTransacao: string;
  hashDocumento?: string;
  chaveBlockchain?: string;
  numeroBloco?: number;
  confirmacoes?: number;
  contratoId?: number | null;
  numero?: string | null;
};

export async function verifyContractDocument(contractId: string, file: File): Promise<ContractVerificationResult> {
  const form = new FormData();
  form.append("arquivo", file);
  const response = await api.post<ApiEnvelope<ContractVerificationResult>>(`/contratos/${contractId}/verificar-documento`, form);
  return response.dados;
}

export async function verifyBlockchainTransaction(hashTransacao: string): Promise<TransactionVerificationResult> {
  const response = await api.post<ApiEnvelope<TransactionVerificationResult>>("/contratos/verificar-transacao", { hashTransacao });
  return response.dados;
}
