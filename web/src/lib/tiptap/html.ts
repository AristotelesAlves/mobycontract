import {
  documentTotalHeightInPixels,
  buildDocumentPageGeometry,
  documentPageCssVariables,
  documentCssVariablesText,
  type DocumentMargins,
} from "./document-geometry";
import { documentTypographyCss, type DocumentTypography } from "./document-style";
import {
  DOCUMENT_VARIABLE_ATTRIBUTE,
  escapeDocumentTextForHtml,
  documentVariableInterpolator,
  type DocumentVariableValues,
} from "./document-variables";

export type DocumentExport = {
  title: string;
  htmlContent: string;
  margins: DocumentMargins;
  typography: DocumentTypography;
  pageCount: number;
  values?: DocumentVariableValues;
};

function documentContentWithoutEditorMarks(htmlContent: string) {
  if (typeof window === "undefined") return htmlContent;

  const htmlParser = new DOMParser();
  const temporaryDocument = htmlParser.parseFromString(
    `<body><div id="documento-raiz-temporaria">${htmlContent}</div></body>`,
    "text/html",
  );
  const temporaryRoot = temporaryDocument.getElementById(
    "documento-raiz-temporaria",
  );

  if (!temporaryRoot) return htmlContent;

  temporaryRoot
    .querySelectorAll(`[${DOCUMENT_VARIABLE_ATTRIBUTE}]`)
    .forEach((mark) =>
      mark.replaceWith(...Array.from(mark.childNodes)),
    );
  
    
    temporaryRoot
    .querySelectorAll("[contenteditable]")
    .forEach((element) => element.removeAttribute("contenteditable"));
    
  return temporaryRoot.innerHTML;
}

function documentPageRule(margins: DocumentMargins) {
  return `@page { size: A4; margin: ${margins.top}mm ${margins.right}mm ${margins.bottom}mm ${margins.left}mm; }`;
}

export function documentContentForExport(
  htmlContent: string,
  values?: DocumentVariableValues,
) {
  return documentVariableInterpolator(
    documentContentWithoutEditorMarks(htmlContent),
    values,
  );
}

export function documentHtmlForExport(documentExport: DocumentExport) {
  const geometry = buildDocumentPageGeometry(documentExport.margins);
  const preparedContent = documentContentForExport(
    documentExport.htmlContent,
    documentExport.values,
  );
  const totalHeightPixels = documentTotalHeightInPixels(
    geometry,
    documentExport.pageCount,
  );
  const pageVariables = documentCssVariablesText(
    documentPageCssVariables(geometry),
  );

  return `<!doctype html>
<html lang="pt-BR">
<head>
<meta charset="utf-8" />
<meta http-equiv="Content-Security-Policy" content="default-src 'none'; img-src data: blob:; style-src 'unsafe-inline'; font-src data:; form-action 'none'; base-uri 'none'" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>${escapeDocumentTextForHtml(documentExport.title)}</title>
<style>
${documentPageRule(geometry.margins)}

html, body { margin: 0; padding: 0; }

body {
  background-color: #e7eaf0;
  padding: 16px 0;
  -webkit-print-color-adjust: exact;
  print-color-adjust: exact;
}

@media print {
  body { background-color: transparent; padding: 0; }
}

${documentTypographyCss(documentExport.typography)}
</style>
</head>
<body class="documento-area">
<div class="documento-moldura">
<div class="documento-pilha" style="${pageVariables}; height: ${totalHeightPixels}px">
<div class="documento-folhas" aria-hidden="true"></div>
<div class="documento-conteudo"><div class="documento">${preparedContent}</div></div>
</div>
</div>
</body>
</html>`;
}
