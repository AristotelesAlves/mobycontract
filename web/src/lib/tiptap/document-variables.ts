export const DOCUMENT_VARIABLE_ATTRIBUTE = "data-documento-variavel";

export const DOCUMENT_VARIABLE_UNKNOWN_ATTRIBUTE =
  "data-documento-variavel-desconhecida";

const DOCUMENT_VARIABLE_PATTERN = "\\{\\{\\s*([a-zA-Z0-9_]+(?:\\.[a-zA-Z0-9_]+)*)\\s*\\}\\}";

export type DocumentVariableValues = Record<string, unknown>;

export type DocumentVariableCatalogItem = {
  path: string;
  label: string;
};

export type DocumentVariableCatalog = {
  group: string;
  items: DocumentVariableCatalogItem[];
}[];

export function documentVariableExpression() {
  return new RegExp(DOCUMENT_VARIABLE_PATTERN, "g");
}

export function documentVariableReference(path: string) {
  return `{{${path}}}`;
}

export function documentVariableCatalogPaths(
  catalog: DocumentVariableCatalog,
) {
  return catalog.flatMap((group) => group.items.map((item) => item.path));
}

export function documentVariablePathsFound(text: string) {
  const expression = documentVariableExpression();
  const paths = new Set<string>();
  let match = expression.exec(text);

  while (match) {
    paths.add(match[1]);
    match = expression.exec(text);
  }

  return Array.from(paths);
}

export function documentVariableValueByPath(
  values: DocumentVariableValues,
  path: string,
): unknown {
  return path.split(".").reduce<unknown>((current, key) => {
    if (current === null || typeof current !== "object") return undefined;
    return (current as Record<string, unknown>)[key];
  }, values);
}

export function escapeDocumentTextForHtml(text: string) {
  return text
    .replace(/&/g, "&amp;")
    .replace(/</g, "&lt;")
    .replace(/>/g, "&gt;")
    .replace(/"/g, "&quot;")
    .replace(/'/g, "&#39;");
}

export function documentVariableInterpolator(
  htmlContent: string,
  values: DocumentVariableValues = {},
) {
  return htmlContent.replace(
    documentVariableExpression(),
    (match: string, path: string) => {
      const value = documentVariableValueByPath(values, path);
      if (value === undefined || value === null || value === "") {
        return match;
      }
      return escapeDocumentTextForHtml(String(value));
    },
  );
}