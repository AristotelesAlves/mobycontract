import { Decoration, Extension, type Editor } from "@tiptap/core";
import {
  DOCUMENT_PAGINATION_GAP_PIXELS,
  type DocumentPageGeometry,
} from "../document-geometry";

export const DOCUMENT_PAGINATION_EXTENSION_NAME = "documentoPaginacao";

export const DOCUMENT_SPACER_ATTRIBUTE = "data-documento-spacer";

export type DocumentPageBreak = {
  position: number;
  heightPixels: number;
};

export type DocumentPaginationStorage = {
  pageBreaks: DocumentPageBreak[];
};

declare module "@tiptap/core" {
  interface Storage {
    documentoPaginacao: DocumentPaginationStorage;
  }
}

export type DocumentPaginationMeasurement = {
  pageBreaks: DocumentPageBreak[];
  pageCount: number;
};

function documentSpacerElement(heightPixels: number) {
  const spacer = document.createElement("div");
  spacer.className = "documento-spacer";
  spacer.setAttribute(DOCUMENT_SPACER_ATTRIBUTE, "true");
  spacer.setAttribute("contenteditable", "false");
  spacer.setAttribute("aria-hidden", "true");
  spacer.style.height = `${heightPixels}px`;
  return spacer;
}

export const DocumentPaginationExtension = Extension.create<
  Record<string, never>,
  DocumentPaginationStorage
>({
  name: DOCUMENT_PAGINATION_EXTENSION_NAME,

  addStorage() {
    return { pageBreaks: [] };
  },

  addDecorations() {
    return {
      update: "manual",
      create: () =>
        this.storage.pageBreaks.map((pageBreak) =>
          Decoration.Widget(
            pageBreak.position,
            () => documentSpacerElement(pageBreak.heightPixels),
            {
              key: `documento-spacer-${pageBreak.position}-${Math.round(pageBreak.heightPixels)}`,
              side: -1,
              ignoreSelection: true,
            },
          ),
        ),
    };
  },
});

export type MeasuredDocumentBlock = {
  position: number;
  topoNaturalPixels: number;
  heightPixels: number;
};

function renderedSpacerHeight(spacer: HTMLElement) {
  const declaredHeight = Number.parseFloat(spacer.style.height);
  return Number.isFinite(declaredHeight)
    ? declaredHeight
    : spacer.offsetHeight;
}

export function measureDocumentBlocks(
  editorInstance: Editor,
): MeasuredDocumentBlock[] {
  const raizDoEditor = editorInstance.view.dom as HTMLElement;
  const documentoAtual = editorInstance.state.doc;
  const blocksMedidos: MeasuredDocumentBlock[] = [];

  let spaceresRenderizados = 0;
  let indexDoBloco = 0;
  let positionDoBloco = 0;

  for (const filho of Array.from(raizDoEditor.children)) {
    const bloco = filho as HTMLElement;

    if (bloco.hasAttribute(DOCUMENT_SPACER_ATTRIBUTE)) {
      spaceresRenderizados += renderedSpacerHeight(bloco);
      continue;
    }

    const noDoBloco = documentoAtual.maybeChild(indexDoBloco);
    if (!noDoBloco) break;

    blocksMedidos.push({
      position: positionDoBloco,
      topoNaturalPixels: bloco.offsetTop - spaceresRenderizados,
      heightPixels: bloco.offsetHeight,
    });

    positionDoBloco += noDoBloco.nodeSize;
    indexDoBloco += 1;
  }

  return blocksMedidos;
}

export function documentBlockBreaks(
  blocksMedidos: MeasuredDocumentBlock[],
  geometry: DocumentPageGeometry,
): DocumentPaginationMeasurement {
  const pageStep = geometry.pageStepPixels;
  const availableHeight = Math.max(
    1,
    geometry.contentHeightPixels - DOCUMENT_PAGINATION_GAP_PIXELS,
  );

  const pageBreaks: DocumentPageBreak[] = [];
  let spaceresCalculados = 0;
  let currentPage = 0;
  let emptyPage = true;

  for (const measuredBlock of blocksMedidos) {
    let blockTop = measuredBlock.topoNaturalPixels + spaceresCalculados;
    const pageLimit = currentPage * pageStep + availableHeight;

    const spacerHeight = (currentPage + 1) * pageStep - blockTop;
    if (
      !emptyPage &&
      blockTop + measuredBlock.heightPixels > pageLimit &&
      spacerHeight > 0
    ) {
      pageBreaks.push({
        position: measuredBlock.position,
        heightPixels: spacerHeight,
      });
      spaceresCalculados += spacerHeight;
      blockTop += spacerHeight;
      currentPage += 1;
      emptyPage = true;
    }

    const pageOverflow =
      blockTop +
      measuredBlock.heightPixels -
      (currentPage * pageStep + availableHeight);

    if (pageOverflow > 0) {
      currentPage += Math.ceil(pageOverflow / pageStep);
    }

    emptyPage = false;
  }

  return { pageBreaks, pageCount: currentPage + 1 };
}

export function documentPaginationMeasurement(
  editorInstance: Editor,
  geometry: DocumentPageGeometry,
): DocumentPaginationMeasurement {
  return documentBlockBreaks(
    measureDocumentBlocks(editorInstance),
    geometry,
  );
}

function documentBreaksAreEquivalent(
  previous: DocumentPageBreak[],
  next: DocumentPageBreak[],
) {
  return (
    previous.length === next.length &&
    previous.every(
      (pageBreak, index) =>
        pageBreak.position === next[index].position &&
        Math.abs(pageBreak.heightPixels - next[index].heightPixels) < 1,
    )
  );
}

export function documentPaginationStorage(editorInstance: Editor) {
  return editorInstance.storage.documentoPaginacao;
}

export function syncDocumentPagination(
  editorInstance: Editor,
  geometry: DocumentPageGeometry,
  numeroDePaginasAtual: number,
) {
  const raizDoEditor = editorInstance.view.dom as HTMLElement;
  if (editorInstance.isDestroyed || !raizDoEditor.isConnected) {
    return numeroDePaginasAtual;
  }

  const measurement = documentPaginationMeasurement(editorInstance, geometry);
  const storage = documentPaginationStorage(editorInstance);

  if (!documentBreaksAreEquivalent(storage.pageBreaks, measurement.pageBreaks)) {
    storage.pageBreaks = measurement.pageBreaks;
    editorInstance.commands.updateDecorations(DOCUMENT_PAGINATION_EXTENSION_NAME);
  }

  return measurement.pageCount;
}
