export const DOCUMENT_PAGE_MILLIMETERS = { width: 210, height: 297 } as const;

export const DOCUMENT_PAGE_GAP_MILLIMETERS = 12;

export const DOCUMENT_MARGIN_LIMITS_MILLIMETERS = { minimum: 5, maximum: 50 } as const;

export const DOCUMENT_PAGINATION_GAP_PIXELS = 4;

export const DOCUMENT_MARGIN_SIDES = ["top", "right", "bottom", "left"] as const;

export type DocumentMarginSide = (typeof DOCUMENT_MARGIN_SIDES)[number];

export type DocumentMargins = Record<DocumentMarginSide, number>;

export const DOCUMENT_MARGIN_LABELS: Record<DocumentMarginSide, string> = {
  top: "Margem superior",
  right: "Margem direita",
  bottom: "Margem inferior",
  left: "Margem esquerda",
};

export const DOCUMENT_DEFAULT_MARGINS: DocumentMargins = {
  top: 25,
  right: 20,
  bottom: 25,
  left: 20,
};

const PIXELS_PER_MILLIMETER = 96 / 25.4;

export function documentMillimetersToPixels(millimeters: number) {
  return millimeters * PIXELS_PER_MILLIMETER;
}

export function clampDocumentValue(
  value: number,
  minimum: number,
  maximum: number,
) {
  if (!Number.isFinite(value)) return minimum;
  return Math.min(maximum, Math.max(minimum, value));
}

export function normalizeDocumentMargin(value: number) {
  const { minimum, maximum } = DOCUMENT_MARGIN_LIMITS_MILLIMETERS;
  return Math.round(clampDocumentValue(value, minimum, maximum));
}

export function normalizeDocumentMargins(
  margins: Partial<DocumentMargins> & { superior?: number; direita?: number; inferior?: number; esquerda?: number },
): DocumentMargins {
  const resolved = {
    top: margins.top ?? margins.superior,
    right: margins.right ?? margins.direita,
    bottom: margins.bottom ?? margins.inferior,
    left: margins.left ?? margins.esquerda,
  };
  return DOCUMENT_MARGIN_SIDES.reduce(
    (normalized, side) => ({
      ...normalized,
      [side]: normalizeDocumentMargin(resolved[side] ?? DOCUMENT_DEFAULT_MARGINS[side]),
    }),
    {} as DocumentMargins,
  );
}

export type DocumentPageGeometry = {
  margins: DocumentMargins;
  pageWidthPixels: number;
  pageHeightPixels: number;
  pageGapPixels: number;
  pageStepPixels: number;
  contentHeightPixels: number;
  contentWidthMillimeters: number;
};

export function buildDocumentPageGeometry(
  margins: DocumentMargins,
): DocumentPageGeometry {
  const safeMargins = normalizeDocumentMargins(margins);
  const pageHeightPixels = documentMillimetersToPixels(
    DOCUMENT_PAGE_MILLIMETERS.height,
  );
  const pageGapPixels = documentMillimetersToPixels(
    DOCUMENT_PAGE_GAP_MILLIMETERS,
  );

  return {
    margins: safeMargins,
    pageWidthPixels: documentMillimetersToPixels(DOCUMENT_PAGE_MILLIMETERS.width),
    pageHeightPixels,
    pageGapPixels,
    pageStepPixels: pageHeightPixels + pageGapPixels,
    contentHeightPixels: documentMillimetersToPixels(
      DOCUMENT_PAGE_MILLIMETERS.height - safeMargins.top - safeMargins.bottom,
    ),
    contentWidthMillimeters:
      DOCUMENT_PAGE_MILLIMETERS.width - safeMargins.left - safeMargins.right,
  };
}

export function documentTotalHeightInPixels(
  geometry: DocumentPageGeometry,
  pageCount: number,
) {
  const pages = Math.max(1, Math.floor(pageCount));
  return pages * geometry.pageStepPixels - geometry.pageGapPixels;
}

export function documentPageCssVariables(
  geometry: DocumentPageGeometry,
): Record<string, string> {
  return {
    "--document-page-width": `${DOCUMENT_PAGE_MILLIMETERS.width}mm`,
    "--document-page-height": `${DOCUMENT_PAGE_MILLIMETERS.height}mm`,
    "--document-page-gap": `${DOCUMENT_PAGE_GAP_MILLIMETERS}mm`,
    "--document-margin-top": `${geometry.margins.top}mm`,
    "--document-margin-right": `${geometry.margins.right}mm`,
    "--document-margin-bottom": `${geometry.margins.bottom}mm`,
    "--document-margin-left": `${geometry.margins.left}mm`,
  };
}

export function documentCssVariablesText(variables: Record<string, string>) {
  return Object.entries(variables)
    .map(([property, value]) => `${property}: ${value}`)
    .join("; ");
}
