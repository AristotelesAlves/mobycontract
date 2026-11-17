import { api } from "@/services/api/base-api";

export async function downloadDocumentPdf(html: string, contractId: string, filename: string): Promise<void> {
  const { dados } = await api.post<{ dados: { autorizacao: string } }>(`/contratos/${contractId}/exportacao-pdf`);
  const response = await fetch("/documentos/pdf", {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify({ html, contractId, autorizacao: dados.autorizacao }),
  });
  if (!response.ok) {
    const error = await response.json().catch(() => null);
    throw new Error(error?.mensagem ?? "Não foi possível gerar o PDF. Tente novamente.");
  }
  const blob = await response.blob();
  const url = URL.createObjectURL(blob);
  const link = document.createElement("a");
  link.href = url;
  link.download = `${filename.replace(/[^a-zA-Z0-9-]/g, "-")}.pdf`;
  document.body.appendChild(link);
  link.click();
  link.remove();
  setTimeout(() => URL.revokeObjectURL(url), 1000);
}
