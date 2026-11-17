import { Extension } from "@tiptap/core";

export const DOCUMENT_LINE_HEIGHT_EXTENSION_NAME = "documentLineHeight";

export const DOCUMENT_FORMATTABLE_BLOCKS = ["heading", "paragraph"] as const;

declare module "@tiptap/core" {
  interface Commands<ReturnType> {
    documentLineHeight: {
      setDocumentLineHeight: (lineHeight: string) => ReturnType;
    };
  }
}

export const DocumentLineHeightExtension = Extension.create({
  name: DOCUMENT_LINE_HEIGHT_EXTENSION_NAME,

  addGlobalAttributes() {
    return [
      {
        types: [...DOCUMENT_FORMATTABLE_BLOCKS],
        attributes: {
          documentLineHeight: {
            default: null,
            parseHTML: (elemento: HTMLElement) =>
              elemento.style.lineHeight || null,
            renderHTML: (atributos: Record<string, unknown>) =>
              atributos.documentLineHeight
                ? { style: `line-height: ${atributos.documentLineHeight}` }
                : {},
          },
        },
      },
    ];
  },

  addCommands() {
    return {
      setDocumentLineHeight:
        (lineHeight: string) =>
        ({ commands }) => {
          DOCUMENT_FORMATTABLE_BLOCKS.forEach((tipoDeBloco) =>
            commands.updateAttributes(tipoDeBloco, {
              documentLineHeight: lineHeight,
            }),
          );
          return true;
        },
    };
  },
});
