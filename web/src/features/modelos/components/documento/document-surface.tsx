"use client";

import type { CSSProperties } from "react";
import type { Editor } from "@tiptap/core";
import { EditorContent } from "@tiptap/react";
import { useScaleToFit } from "@/hooks/use-scale-to-fit";
import { documentTypographyCss, type DocumentTypography } from "@/lib/tiptap/document-style";
import {
  documentTotalHeightInPixels,
  documentPageCssVariables,
  type DocumentPageGeometry,
} from "@/lib/tiptap/document-geometry";

interface DocumentSurfaceProps {
  editorInstance: Editor | null;
  geometry: DocumentPageGeometry;
  typography: DocumentTypography;
  pageCount: number;
}

export function DocumentSurface(props: DocumentSurfaceProps) {
  const { referenciaDaArea, escala } = useScaleToFit(
    props.geometry.pageWidthPixels,
  );

  const alturaTotalPixels = documentTotalHeightInPixels(
    props.geometry,
    props.pageCount,
  );

  const estiloDaPilha = {
    ...documentPageCssVariables(props.geometry),
    height: alturaTotalPixels,
    transform: `scale(${escala})`,
  } as CSSProperties;

  return (
    <div
      ref={referenciaDaArea}
      className="documento-area h-full w-full overflow-auto rounded-md py-6"
    >
      <style>{documentTypographyCss(props.typography)}</style>
      <div
        className="documento-moldura"
        style={{
          width: props.geometry.pageWidthPixels * escala,
          height: alturaTotalPixels * escala,
        }}
      >
        <div className="documento-pilha" style={estiloDaPilha}>
          <div className="documento-folhas" aria-hidden="true" />
          <EditorContent
            editor={props.editorInstance}
            className="documento-conteudo"
          />
        </div>
      </div>
    </div>
  );
}
