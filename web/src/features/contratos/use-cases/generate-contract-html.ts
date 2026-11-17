import { formatDate, formatCurrency } from "@/lib/formatters";
import { documentHtmlForExport, type DocumentExport } from "@/lib/tiptap/html";
import { DOCUMENT_DEFAULT_MARGINS, normalizeDocumentMargins } from "@/lib/tiptap/document-geometry";
import { DOCUMENT_DEFAULT_TYPOGRAPHY, normalizeDocumentTypography } from "@/lib/tiptap/document-style";
import { type Contrato } from "../data/contrato-types";
import type { IModeloClonar } from "@/features/modelos/data/modelo-types";

export function gerarHtmlContrato(
  contrato: Contrato,
  modelo?: IModeloClonar,
): string | null {
  if (!contrato) return null;

  const snapshot = contrato.snapshot;
  const htmlContent = snapshot?.modelo.conteudo_html ?? modelo?.conteudoHtml;
  if (!htmlContent) return null;
  const cliente = {
    ...(snapshot?.inquilino ?? { nome: contrato.inquilinoNome }),
    renda: snapshot?.inquilino.renda == null ? undefined : formatCurrency(snapshot.inquilino.renda),
  };
  const valorAluguel = formatCurrency(snapshot?.imovel.valor_aluguel ?? contrato.valorMensal ?? contrato.valorAluguel ?? 0);
  const locador = contrato.assinaturas?.find((parte) => parte.papel === "Locador");
  const values = {
    locador: snapshot?.locador ?? (locador ? { nome: locador.nome } : undefined),
    locatario: cliente,
    imovel: {
      ...snapshot?.imovel,
      valorAluguel,
      valor: valorAluguel,
      titulo: snapshot?.imovel.titulo ?? contrato.imovelTitulo,
      title: snapshot?.imovel.titulo ?? contrato.imovelTitulo,
      endereco: snapshot?.imovel.endereco ?? contrato.imovelEndereco,
      cidade: snapshot?.imovel.cidade ?? contrato.imovelCidade,
      matricula: snapshot?.imovel.matricula ?? contrato.imovelMatricula,
    },
    cliente,
    contrato: {
      numero: contrato.numero,
      dataInicio: formatDate(contrato.dataInicio),
      dataFim: contrato.dataFim ? formatDate(contrato.dataFim) : "Prazo indeterminado",
      valorMensal: formatCurrency(contrato.valorMensal ?? 0),
      multaPorAtraso: contrato.multaPorAtraso,
      multaRescisao: contrato.multaRescisao,
      garantia: contrato.garantia,
      valorCaucao: contrato.valorCaucao == null ? "Não aplicável" : formatCurrency(contrato.valorCaucao),
      indiceReajuste: contrato.indiceReajuste,
    },
  };

  const documentExport: DocumentExport = {
    title: `Contrato ${contrato.numero}`,
    htmlContent,
    margins: normalizeDocumentMargins(snapshot?.modelo.margens ?? DOCUMENT_DEFAULT_MARGINS),
    typography: normalizeDocumentTypography(snapshot?.modelo.tipografia ?? DOCUMENT_DEFAULT_TYPOGRAPHY),
    pageCount: 1,
    values,
  };

  return documentHtmlForExport(documentExport);
}
