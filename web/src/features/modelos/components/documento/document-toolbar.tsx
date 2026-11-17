"use client";

import type { Editor } from "@tiptap/core";
import { useEditorState } from "@tiptap/react";
import {
  ArrowsOutLineVertical,
  CaretDown,
  ListBullets,
  ListChecks,
  ListNumbers,
  TextAlignCenter,
  TextAlignJustify,
  TextAlignLeft,
  TextAlignRight,
  TextB,
  TextItalic,
  TextStrikethrough,
  TextUnderline,
} from "@phosphor-icons/react/dist/ssr";
import { Button } from "@/components/ui/button";
import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuItem,
  DropdownMenuTrigger,
} from "@/components/ui/dropdown-menu";
import { Separator } from "@/components/ui/separator";
import { Toggle } from "@/components/ui/toggle";
import {
  DOCUMENT_LINE_HEIGHTS,
  type DocumentTypography,
} from "@/lib/tiptap/document-style";
import type { DocumentVariableCatalog } from "@/lib/tiptap/document-variables";
import { DocumentTitleControls } from "./document-title-controls";
import { DocumentVariableControls } from "./document-variable-controls";

const DOCUMENT_MARKS = [
  {
    name: "bold",
    label: "Negrito",
    icone: TextB,
    alternar: (editorInstancia: Editor) =>
      editorInstancia.chain().focus().toggleBold().run(),
  },
  {
    name: "italic",
    label: "Itálico",
    icone: TextItalic,
    alternar: (editorInstancia: Editor) =>
      editorInstancia.chain().focus().toggleItalic().run(),
  },
  {
    name: "underline",
    label: "Sublinhado",
    icone: TextUnderline,
    alternar: (editorInstancia: Editor) =>
      editorInstancia.chain().focus().toggleUnderline().run(),
  },
  {
    name: "strike",
    label: "Tachado",
    icone: TextStrikethrough,
    alternar: (editorInstancia: Editor) =>
      editorInstancia.chain().focus().toggleStrike().run(),
  },
];

const DOCUMENT_LISTS = [
  {
    name: "taskList",
    label: "List de tarefas",
    icone: ListChecks,
    alternar: (editorInstancia: Editor) =>
      editorInstancia.chain().focus().toggleTaskList().run(),
  },
  {
    name: "bulletList",
    label: "List com marcadores",
    icone: ListBullets,
    alternar: (editorInstancia: Editor) =>
      editorInstancia.chain().focus().toggleBulletList().run(),
  },
  {
    name: "orderedList",
    label: "List numerada",
    icone: ListNumbers,
    alternar: (editorInstancia: Editor) =>
      editorInstancia.chain().focus().toggleOrderedList().run(),
  },
];

const DOCUMENT_ALIGNMENTS = [
  { valor: "left", label: "Alinhar à esquerda", icone: TextAlignLeft },
  { valor: "center", label: "Centralizar", icone: TextAlignCenter },
  { valor: "right", label: "Alinhar à direita", icone: TextAlignRight },
  { valor: "justify", label: "Justificar", icone: TextAlignJustify },
];

const DOCUMENT_TOGGLE_BUTTONS = [...DOCUMENT_MARKS, ...DOCUMENT_LISTS];

interface DocumentToolbarProps {
  editorInstance: Editor | null;
  typography: DocumentTypography;
  variableCatalog: DocumentVariableCatalog;
}

export function DocumentToolbar(
  props: DocumentToolbarProps,
) {
  const estadoDaBarra = useEditorState({
    editor: props.editorInstance,
    selector: ({ editor }) => ({
      alternaveisAtivos: Object.fromEntries(
        DOCUMENT_TOGGLE_BUTTONS.map((button) => [
          button.name,
          editor?.isActive(button.name) ?? false,
        ]),
      ),
      alinhamentoAtivo:
        DOCUMENT_ALIGNMENTS.find((alignment) =>
          editor?.isActive({ textAlign: alignment.valor }),
        ) ?? DOCUMENT_ALIGNMENTS[0],
    }),
  });

  const editorInstance = props.editorInstance;

  if (!editorInstance || !estadoDaBarra) return null;

  const IconeDoAlinhamentoAtivo = estadoDaBarra.alinhamentoAtivo.icone;

  return (
    <div className="flex flex-wrap items-center gap-0.5">
      <DocumentTitleControls
        editorInstance={editorInstance}
        typography={props.typography}
      />

      <Separator orientation="vertical" className="mx-1 h-6" />

      {DOCUMENT_MARKS.map((mark) => (
        <Toggle
          key={mark.name}
          title={mark.label}
          pressed={estadoDaBarra.alternaveisAtivos[mark.name]}
          onPressedChange={() => mark.alternar(editorInstance)}
        >
          <mark.icone size={20} />
        </Toggle>
      ))}

      <Separator orientation="vertical" className="mx-1 h-6" />

      <DropdownMenu>
        <DropdownMenuTrigger
          render={
            <Button variant="ghost" title="Alinhamento">
              <IconeDoAlinhamentoAtivo size={20} />
              <CaretDown size={12} />
            </Button>
          }
        />
        <DropdownMenuContent className="w-auto min-w-44">
          {DOCUMENT_ALIGNMENTS.map((alignment) => (
            <DropdownMenuItem
              key={alignment.valor}
              onClick={() =>
                editorInstance
                  .chain()
                  .focus()
                  .setTextAlign(alignment.valor)
                  .run()
              }
            >
              <alignment.icone size={18} />
              {alignment.label}
            </DropdownMenuItem>
          ))}
        </DropdownMenuContent>
      </DropdownMenu>

      <DropdownMenu>
        <DropdownMenuTrigger
          render={
            <Button variant="ghost" title="Espaçamento entre linhas">
              <ArrowsOutLineVertical size={20} />
              <CaretDown size={12} />
            </Button>
          }
        />
        <DropdownMenuContent className="w-auto min-w-32">
          {DOCUMENT_LINE_HEIGHTS.map((altura) => (
            <DropdownMenuItem
              key={altura}
              onClick={() =>
                editorInstance
                  .chain()
                  .focus()
                  .setDocumentLineHeight(altura)
                  .run()
              }
            >
              {altura}
            </DropdownMenuItem>
          ))}
        </DropdownMenuContent>
      </DropdownMenu>

      <Separator orientation="vertical" className="mx-1 h-6" />

      {DOCUMENT_LISTS.map((list) => (
        <Toggle
          key={list.name}
          title={list.label}
          pressed={estadoDaBarra.alternaveisAtivos[list.name]}
          onPressedChange={() => list.alternar(editorInstance)}
        >
          <list.icone size={20} />
        </Toggle>
      ))}

      <Separator orientation="vertical" className="mx-1 h-6" />

      <DocumentVariableControls
        editorInstance={editorInstance}
        variableCatalog={props.variableCatalog}
      />
    </div>
  );
}
