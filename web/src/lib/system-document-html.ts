import { documentHtmlForExport } from "@/lib/tiptap/html";
import { escapeDocumentTextForHtml as escape } from "@/lib/tiptap/document-variables";
import { DOCUMENT_DEFAULT_MARGINS } from "@/lib/tiptap/document-geometry";
import { DOCUMENT_DEFAULT_TYPOGRAPHY } from "@/lib/tiptap/document-style";

export function systemDocumentHtml(title: string, reference: string, content: string, issuedAt = new Date()): string {
  const emission = issuedAt.toLocaleString("pt-BR", { timeZone: "America/Fortaleza" });
  return documentHtmlForExport({
    title,
    margins: DOCUMENT_DEFAULT_MARGINS,
    typography: DOCUMENT_DEFAULT_TYPOGRAPHY,
    pageCount: 1,
    htmlContent: `<style>
      .registro-cabecalho { border-bottom: 2px solid #0f172a; padding-bottom: 16px; margin-bottom: 24px; }
      .registro-meta { color: #475569; font-size: 9pt; }
      .registro-tabela { width: 100%; border-collapse: collapse; margin: 16px 0; font-size: 10pt; }
      .registro-tabela th, .registro-tabela td { border: 1px solid #cbd5e1; padding: 8px; text-align: left; overflow-wrap: anywhere; }
      .registro-tabela th { background: #f1f5f9; }
      .registro-tabela thead { display: table-header-group; }
      .registro-tabela tr, .registro-foto { break-inside: avoid; }
      .registro-foto { margin: 20px 0; }
      .registro-foto img { display: block; max-width: 100%; max-height: 160mm; object-fit: contain; }
      .registro-texto { white-space: pre-wrap; }
      .registro-rodape { border-top: 1px solid #cbd5e1; margin-top: 24px; padding-top: 12px; font-size: 9pt; color: #475569; }
    </style>
    <header class="registro-cabecalho"><p class="registro-meta">MobiContract • Registro do sistema</p><h1>${escape(title)}</h1><p>${escape(reference)}</p><p class="registro-meta">Emitido em ${escape(emission)} (horário de Fortaleza)</p></header>
    ${content}
    <footer class="registro-rodape">Documento gerado pelo MobiContract com as informações registradas no sistema na data de emissão.</footer>`,
  });
}
