import {
  Decoration,
  Extension,
  type DecorationCreateProps,
} from "@tiptap/core";
import {
  DOCUMENT_VARIABLE_ATTRIBUTE,
  DOCUMENT_VARIABLE_UNKNOWN_ATTRIBUTE,
  documentVariableExpression,
} from "../document-variables";

export const DOCUMENT_VARIABLES_EXTENSION_NAME = "documentoVariaveis";

export type DocumentVariablesOptions = {
  knownPaths: string[];
};

type RootNode = DecorationCreateProps["state"]["doc"];

function documentoVariaveisDecoradas(
  rootNode: RootNode,
  rangeStart: number,
  rangeEnd: number,
  knownPaths: Set<string>,
) {
  const decoracoes: Decoration[] = [];

  rootNode.nodesBetween(rangeStart, rangeEnd, (no, position) => {
    if (!no.isText || !no.text) return;

    const expression = documentVariableExpression();
    let match = expression.exec(no.text);

    while (match) {
      const path = match[1];
      const start = position + match.index;

      decoracoes.push(
        Decoration.Inline(start, start + match[0].length, {
          class: "documento-variavel",
          [DOCUMENT_VARIABLE_ATTRIBUTE]: path,
          [DOCUMENT_VARIABLE_UNKNOWN_ATTRIBUTE]: String(
            !knownPaths.has(path),
          ),
        }),
      );

      match = expression.exec(no.text);
    }
  });

  return decoracoes;
}

export const DocumentVariablesExtension = Extension.create<DocumentVariablesOptions>({
  name: DOCUMENT_VARIABLES_EXTENSION_NAME,

  addOptions() {
    return { knownPaths: [] };
  },

  addDecorations() {
    const knownPaths = () => new Set(this.options.knownPaths);

    return {
      update: "changedRanges",
      create: ({ state }) =>
        documentoVariaveisDecoradas(
          state.doc,
          0,
          state.doc.content.size,
          knownPaths(),
        ),
      createInRange: ({ state, from, to }) =>
        documentoVariaveisDecoradas(state.doc, from, to, knownPaths()),
    };
  },
});
