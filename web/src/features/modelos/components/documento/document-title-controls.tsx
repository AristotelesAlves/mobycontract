"use client";

import type { Editor } from "@tiptap/core";
import { useEditorState } from "@tiptap/react";
import { CaretDown, Check } from "@phosphor-icons/react/dist/ssr";
import { Button } from "@/components/ui/button";
import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuItem,
  DropdownMenuTrigger,
} from "@/components/ui/dropdown-menu";
import {
  DOCUMENT_HEADING_LEVELS,
  type DocumentHeadingLevel,
  type DocumentTypography,
} from "@/lib/tiptap/document-style";

const DOCUMENT_PLAIN_TEXT_LABEL = "Texto normal";

const DOCUMENTO_TAMANHO_MAXIMO_DA_AMOSTRA = 18;

interface DocumentTitleControlsProps {
  editorInstance: Editor;
  typography: DocumentTypography;
}

export function DocumentTitleControls(
  props: DocumentTitleControlsProps,
) {
  const activeLevel = useEditorState({
    editor: props.editorInstance,
    selector: ({ editor }) =>
      DOCUMENT_HEADING_LEVELS.find((level) =>
        editor.isActive("heading", { level: level }),
      ) ?? null,
  });

  const activeLabel = activeLevel
    ? props.typography.headings[activeLevel].label
    : DOCUMENT_PLAIN_TEXT_LABEL;

  function aplicarNivelDeTitulo(level: DocumentHeadingLevel | null) {
    const comandosDoEditor = props.editorInstance.chain().focus();

    if (level) {
      comandosDoEditor.setHeading({ level: level }).run();
      return;
    }

    comandosDoEditor.setParagraph().run();
  }

  return (
    <DropdownMenu>
      <DropdownMenuTrigger
        render={
          <Button
            variant="ghost"
            title="Estilo do parágrafo"
            className="w-32 justify-between sm:w-40"
          >
            <span className="truncate">{activeLabel}</span>
            <CaretDown size={12} />
          </Button>
        }
      />
      <DropdownMenuContent className="w-auto min-w-56">
        <DropdownMenuItem
          className="gap-3 py-2.5"
          onClick={() => aplicarNivelDeTitulo(null)}
        >
          <Check
            size={14}
            className={activeLevel ? "invisible" : undefined}
          />
          {DOCUMENT_PLAIN_TEXT_LABEL}
        </DropdownMenuItem>

        {DOCUMENT_HEADING_LEVELS.map((level) => {
          const headingStyle = props.typography.headings[level];

          return (
            <DropdownMenuItem
              key={level}
              className="gap-3 py-2.5"
              onClick={() => aplicarNivelDeTitulo(level)}
              style={{
                fontSize: `${Math.min(
                  DOCUMENTO_TAMANHO_MAXIMO_DA_AMOSTRA,
                  headingStyle.fontSizePoints,
                )}px`,
                fontWeight: headingStyle.weight,
                textTransform: headingStyle.uppercase ? "uppercase" : "none",
              }}
            >
              <Check
                size={14}
                className={
                  activeLevel === level ? undefined : "invisible"
                }
              />
              {headingStyle.label}
            </DropdownMenuItem>
          );
        })}
      </DropdownMenuContent>
    </DropdownMenu>
  );
}
