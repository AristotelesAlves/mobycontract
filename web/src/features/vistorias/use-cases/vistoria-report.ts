import { systemDocumentHtml } from "@/lib/system-document-html";
import { escapeDocumentTextForHtml as escape } from "@/lib/tiptap/document-variables";
import { formatDate } from "@/lib/formatters";
import type { Vistoria } from "../data/vistoria-types";

export interface VistoriaReportPhoto { id: string; dataUrl: string }

export function vistoriaReportHtml(vistoria: Vistoria, photos: VistoriaReportPhoto[], issuedAt = new Date()): string {
  const rows = vistoria.itens.map((item) => `<tr><td>${escape(item.ambiente)}</td><td>${escape(item.item)}</td><td>${escape(item.estado)}</td><td class="registro-texto">${escape(item.observacao ?? "—")}</td></tr>`).join("");
  const images = vistoria.fotos.map((photo, index) => {
    const image = photos.find((entry) => entry.id === photo.id);
    if (!image || !/^data:image\/(jpeg|png|webp);base64,[a-zA-Z0-9+/=]+$/.test(image.dataUrl)) {
      throw new Error("Não foi possível incluir todas as fotos no relatório. Tente novamente.");
    }
    return `<figure class="registro-foto"><img src="${image.dataUrl}" alt="Foto ${index + 1}" /><figcaption><strong>Foto ${index + 1}: ${escape(photo.nomeAnexo ?? "Foto da vistoria")}</strong>${photo.descricao ? `<p class="registro-texto">${escape(photo.descricao)}</p>` : ""}</figcaption></figure>`;
  }).join("");
  return systemDocumentHtml("Relatório de vistoria", `Vistoria nº ${vistoria.numero} • Contrato nº ${vistoria.contratoNumero ?? vistoria.contratoId}`, `
    <p><strong>Imóvel:</strong> ${escape(vistoria.imovelTitulo ?? "Não informado")}</p>
    <p><strong>Tipo:</strong> ${escape(vistoria.tipo)} • <strong>Status:</strong> ${escape(vistoria.status)}</p>
    <p><strong>Data da vistoria:</strong> ${escape(formatDate(vistoria.data))}</p>
    <p><strong>Vistoriador:</strong> ${escape(vistoria.vistoriador ?? "Não informado")}</p>
    <h2>Observações</h2><p class="registro-texto">${escape(vistoria.observacoes?.trim() || "Sem observações registradas.")}</p>
    <h2>Checklist (${vistoria.itens.length} itens)</h2>
    ${rows ? `<table class="registro-tabela"><thead><tr><th>Ambiente</th><th>Item</th><th>Estado</th><th>Observação</th></tr></thead><tbody>${rows}</tbody></table>` : "<p>Nenhum item registrado.</p>"}
    <h2>Registro fotográfico (${vistoria.fotos.length} fotos)</h2>${images || "<p>Nenhuma foto anexada.</p>"}
    <p class="registro-meta">Registro da vistoria: contrato #${escape(vistoria.contratoId)} / vistoria #${escape(vistoria.id)}.</p>`, issuedAt);
}
