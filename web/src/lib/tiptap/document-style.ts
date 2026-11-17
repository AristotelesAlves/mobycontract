export const DOCUMENT_HEADING_LEVELS = [1, 2, 3, 4, 5, 6] as const;

export type DocumentHeadingLevel = (typeof DOCUMENT_HEADING_LEVELS)[number];

export const DOCUMENT_HEADING_WEIGHTS = [400, 500, 600, 700, 800] as const;

export type DocumentHeadingWeight = (typeof DOCUMENT_HEADING_WEIGHTS)[number];

export const DOCUMENT_FONT_SIZE_LIMITS = { minimum: 6, maximum: 48 } as const;

export const DOCUMENT_LINE_HEIGHTS = ["1", "1.15", "1.5", "2"] as const;

export const DOCUMENT_FONT_FAMILIES = [
  { label: "Arial", value: "Arial, Helvetica, sans-serif" },
  { label: "Times New Roman", value: "'Times New Roman', Times, serif" },
  { label: "Georgia", value: "Georgia, 'Times New Roman', serif" },
  { label: "Verdana", value: "Verdana, Geneva, sans-serif" },
  { label: "Courier New", value: "'Courier New', Courier, monospace" },
] as const;

export type DocumentHeadingStyle = {
  label: string;
  fontSizePoints: number;
  weight: DocumentHeadingWeight;
  uppercase: boolean;
};

export type DocumentTypography = {
  fontFamily: string;
  bodyFontSizePoints: number;
  lineHeight: number;
  headings: Record<DocumentHeadingLevel, DocumentHeadingStyle>;
};

export const DOCUMENT_DEFAULT_TYPOGRAPHY: DocumentTypography = {
  fontFamily: DOCUMENT_FONT_FAMILIES[0].value,
  bodyFontSizePoints: 11,
  lineHeight: 1.5,
  headings: {
    1: { label: "Título 1", fontSizePoints: 20, weight: 700, uppercase: false },
    2: { label: "Título 2", fontSizePoints: 17, weight: 700, uppercase: false },
    3: { label: "Título 3", fontSizePoints: 15, weight: 600, uppercase: false },
    4: { label: "Título 4", fontSizePoints: 13, weight: 600, uppercase: false },
    5: { label: "Título 5", fontSizePoints: 12, weight: 600, uppercase: true },
    6: { label: "Título 6", fontSizePoints: 11, weight: 600, uppercase: true },
  },
};

const DOCUMENT_INK_COLOR = "#0f172a";
const DOCUMENT_PAPER_COLOR = "#ffffff";
const DOCUMENT_SHEET_BORDER_COLOR = "#dfe4ec";
const DOCUMENT_CANVAS_COLOR = "#e7eaf0";

export function normalizeDocumentFontSize(value: number) {
  const { minimum, maximum } = DOCUMENT_FONT_SIZE_LIMITS;
  if (!Number.isFinite(value)) return minimum;
  return Math.round(Math.min(maximum, Math.max(minimum, value)));
}

export function normalizeDocumentTypography(
  typography: Partial<Omit<DocumentTypography, "headings">> & {
    headings?: Partial<DocumentTypography["headings"]>;
    corpo?: number;
    entrelinha?: number;
    fonte?: string;
    tamanho?: number;
  },
): DocumentTypography {
  return {
    fontFamily: typography.fontFamily ?? typography.fonte ?? DOCUMENT_DEFAULT_TYPOGRAPHY.fontFamily,
    lineHeight: typography.lineHeight ?? typography.entrelinha ?? DOCUMENT_DEFAULT_TYPOGRAPHY.lineHeight,
    bodyFontSizePoints: normalizeDocumentFontSize(typography.bodyFontSizePoints ?? typography.corpo ?? typography.tamanho ?? DOCUMENT_DEFAULT_TYPOGRAPHY.bodyFontSizePoints),
    headings: DOCUMENT_HEADING_LEVELS.reduce(
      (headings, level) => ({
        ...headings,
        [level]: {
          ...DOCUMENT_DEFAULT_TYPOGRAPHY.headings[level],
          ...typography.headings?.[level],
          fontSizePoints: normalizeDocumentFontSize(
            typography.headings?.[level]?.fontSizePoints ?? DOCUMENT_DEFAULT_TYPOGRAPHY.headings[level].fontSizePoints,
          ),
        },
      }),
      {} as DocumentTypography["headings"],
    ),
  };
}

function documentHeadingStyles(typography: DocumentTypography) {
  return DOCUMENT_HEADING_LEVELS.map((level) => {
    const heading = typography.headings[level];
    const spaceAbove = (heading.fontSizePoints * 0.55).toFixed(2);
    const spaceBelow = (heading.fontSizePoints * 0.3).toFixed(2);

    return [
      `.documento h${level} {`,
      `  font-size: ${heading.fontSizePoints}pt;`,
      `  font-weight: ${heading.weight};`,
      `  text-transform: ${heading.uppercase ? "uppercase" : "none"};`,
      `  letter-spacing: ${heading.uppercase ? "0.04em" : "normal"};`,
      `  line-height: 1.25;`,
      `  padding-top: ${spaceAbove}pt;`,
      `  margin: 0 0 ${spaceBelow}pt;`,
      `}`,
    ].join("\n");
  }).join("\n");
}

export function documentTypographyCss(receivedTypography: DocumentTypography) {
  const typography = normalizeDocumentTypography(receivedTypography);
  const blockSpacing = (typography.bodyFontSizePoints * 0.6).toFixed(2);
  const firstLineHeight = (
    typography.bodyFontSizePoints * typography.lineHeight
  ).toFixed(2);

  return `
.documento-area {
  display: flex;
  justify-content: center;
  width: 100%;
  background-color: ${DOCUMENT_CANVAS_COLOR};
}

.documento-moldura {
  position: relative;
  flex: 0 0 auto;
}

.documento-pilha {
  position: relative;
  box-sizing: border-box;
  width: var(--document-page-width);
  transform-origin: top left;
}

.documento-folhas {
  position: absolute;
  inset: 0;
  z-index: 0;
  pointer-events: none;
  background-image: repeating-linear-gradient(
    to bottom,
    ${DOCUMENT_SHEET_BORDER_COLOR} 0,
    ${DOCUMENT_SHEET_BORDER_COLOR} 1px,
    ${DOCUMENT_PAPER_COLOR} 1px,
    ${DOCUMENT_PAPER_COLOR} calc(var(--document-page-height) - 1px),
    ${DOCUMENT_SHEET_BORDER_COLOR} calc(var(--document-page-height) - 1px),
    ${DOCUMENT_SHEET_BORDER_COLOR} var(--document-page-height),
    transparent var(--document-page-height),
    transparent calc(var(--document-page-height) + var(--document-page-gap))
  );
  filter: drop-shadow(0 2px 6px rgba(15, 23, 42, 0.18));
}

.documento-conteudo {
  position: relative;
  z-index: 1;
  box-sizing: border-box;
  padding: var(--document-margin-top) var(--document-margin-right)
    var(--document-margin-bottom) var(--document-margin-left);
}

.documento {
  position: relative;
  color: ${DOCUMENT_INK_COLOR};
  font-family: ${typography.fontFamily};
  font-size: ${typography.bodyFontSizePoints}pt;
  line-height: ${typography.lineHeight};
  text-align: left;
  overflow-wrap: break-word;
  outline: none;
}

.documento *,
.documento *::before,
.documento *::after {
  box-sizing: border-box;
}

.documento p,
.documento ul,
.documento ol,
.documento blockquote,
.documento pre,
.documento hr {
  margin: 0 0 ${blockSpacing}pt;
  padding: 0;
}

.documento > :first-child {
  padding-top: 0;
}

.documento > :last-child {
  margin-bottom: 0;
}

${documentHeadingStyles(typography)}

.documento strong { font-weight: 700; }
.documento em { font-style: italic; }
.documento s { text-decoration: line-through; }
.documento u { text-decoration: underline; }

.documento a {
  color: #1d4ed8;
  text-decoration: underline;
}

.documento hr {
  border: 0;
  border-top: 1px solid #cbd5e1;
}

.documento blockquote {
  border-left: 2pt solid #cbd5e1;
  padding-left: 8pt;
  color: #334155;
}

.documento code {
  font-family: 'Courier New', Courier, monospace;
  font-size: 0.92em;
  background-color: #f1f5f9;
}

.documento pre {
  font-family: 'Courier New', Courier, monospace;
  font-size: ${Math.max(6, typography.bodyFontSizePoints - 1)}pt;
  background-color: #f1f5f9;
  padding: 6pt;
  white-space: pre-wrap;
}

.documento ul {
  list-style: disc;
  padding-left: 16pt;
}

.documento ol {
  list-style: decimal;
  padding-left: 16pt;
}

.documento li {
  margin: 0 0 2pt;
}

.documento li > p,
.documento li > div > p {
  margin: 0;
}

.documento li > ul,
.documento li > ol {
  margin: 2pt 0 0;
}

.documento ul[data-type="taskList"] {
  list-style: none;
  padding-left: 0;
}

.documento ul[data-type="taskList"] > li {
  display: flex;
  align-items: flex-start;
  gap: 6pt;
}

.documento ul[data-type="taskList"] > li > label {
  display: flex;
  align-items: center;
  height: ${firstLineHeight}pt;
  margin: 0;
}

.documento ul[data-type="taskList"] > li > div {
  flex: 1 1 auto;
  min-width: 0;
}

.documento ul[data-type="taskList"] input[type="checkbox"] {
  appearance: none;
  -webkit-appearance: none;
  width: 9pt;
  height: 9pt;
  margin: 0;
  border: 1px solid #64748b;
  border-radius: 1pt;
  background-color: ${DOCUMENT_PAPER_COLOR};
}

.documento ul[data-type="taskList"] > li[data-checked="true"] input[type="checkbox"] {
  border-color: ${DOCUMENT_INK_COLOR};
  background-color: ${DOCUMENT_INK_COLOR};
}

.documento-variavel {
  background-color: #dbeafe;
  color: #1e40af;
  border-radius: 2px;
}

.documento-variavel[data-documento-variavel-desconhecida="true"] {
  background-color: #fee2e2;
  color: #991b1b;
}

.documento-espacador {
  display: block;
  width: 100%;
  user-select: none;
  pointer-events: none;
}

@media print {
  .documento-area {
    display: block;
    width: auto;
    background-color: transparent;
  }

  .documento-folhas {
    display: none;
  }

  .documento-moldura,
  .documento-pilha {
    width: auto !important;
    height: auto !important;
    transform: none !important;
  }

  .documento-conteudo {
    padding: 0 !important;
  }

  .documento-espacador {
    height: 0 !important;
    break-after: page;
    page-break-after: always;
  }
}
`;
}
