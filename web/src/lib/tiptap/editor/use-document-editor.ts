"use client";

import { useEffect, useMemo, useState } from "react";
import type { Editor } from "@tiptap/core";
import { TaskItem, TaskList } from "@tiptap/extension-list";
import TextAlign from "@tiptap/extension-text-align";
import { useEditor } from "@tiptap/react";
import StarterKit from "@tiptap/starter-kit";
import type { DocumentTypography } from "../document-style";
import {
  buildDocumentPageGeometry,
  type DocumentPageGeometry,
  type DocumentMargins,
} from "../document-geometry";
import {
  DOCUMENT_FORMATTABLE_BLOCKS,
  DocumentLineHeightExtension,
} from "./line-height-extension";
import {
  DocumentPaginationExtension,
  syncDocumentPagination,
} from "./pagination-extension";
import { DocumentVariableSuggestionExtension } from "./variable-suggestion-extension";
import { DocumentVariablesExtension } from "./variables-extension";
import type { DocumentVariableCatalog } from "../document-variables";

export type DocumentEditorConfiguration = {
  initialContent: string;
  margins: DocumentMargins;
  typography: DocumentTypography;
  knownVariablePaths: string[];
  variableCatalog: DocumentVariableCatalog;
};

export type DocumentEditorControl = {
  editorInstance: Editor | null;
  geometry: DocumentPageGeometry;
  pageCount: number;
};

function useSynchronizedDocumentPagination(
  editorInstance: Editor | null,
  geometry: DocumentPageGeometry,
  typography: DocumentTypography,
) {
  const [pageCount, setPageCount] = useState(1);

  useEffect(() => {
    if (!editorInstance) return;

    let scheduledFrame = 0;

    const measure = () => {
      scheduledFrame = 0;
      setPageCount((currentPageCount) =>
        syncDocumentPagination(
          editorInstance,
          geometry,
          currentPageCount,
        ),
      );
    };

    const scheduleMeasurement = () => {
      if (scheduledFrame) return;
      scheduledFrame = requestAnimationFrame(measure);
    };

    const contentObserver = new ResizeObserver(scheduleMeasurement);
    contentObserver.observe(editorInstance.view.dom);
    editorInstance.on("update", scheduleMeasurement);
    document.fonts?.ready.then(scheduleMeasurement).catch(() => undefined);
    scheduleMeasurement();

    return () => {
      if (scheduledFrame) cancelAnimationFrame(scheduledFrame);
      contentObserver.disconnect();
      editorInstance.off("update", scheduleMeasurement);
    };
  }, [editorInstance, geometry, typography]);

  return pageCount;
}

export function useDocumentEditor(
  configuration: DocumentEditorConfiguration,
): DocumentEditorControl {
  const geometry = useMemo(
    () => buildDocumentPageGeometry(configuration.margins),
    [configuration.margins],
  );

  const editorInstance = useEditor(
    {
      immediatelyRender: false,
      autofocus: "end",
      content: configuration.initialContent,
      editorProps: { attributes: { class: "documento" } },
      extensions: [
        StarterKit,
        TextAlign.configure({ types: [...DOCUMENT_FORMATTABLE_BLOCKS] }),
        DocumentLineHeightExtension,
        TaskList,
        TaskItem.configure({ nested: true }),
        DocumentVariablesExtension.configure({
          knownPaths: configuration.knownVariablePaths,
        }),
        DocumentVariableSuggestionExtension.configure({
          catalog: configuration.variableCatalog,
        }),
        DocumentPaginationExtension,
      ],
    },
    [],
  );

  const pageCount = useSynchronizedDocumentPagination(
    editorInstance,
    geometry,
    configuration.typography,
  );

  return {
    editorInstance,
    geometry,
    pageCount,
  };
}
