"use client";

import type { Editor } from "@tiptap/core";
import { BracketsCurly, CaretDown } from "@phosphor-icons/react/dist/ssr";
import { Button } from "@/components/ui/button";
import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuGroup,
  DropdownMenuItem,
  DropdownMenuLabel,
  DropdownMenuSeparator,
  DropdownMenuTrigger,
} from "@/components/ui/dropdown-menu";
import {
  documentVariableReference,
  type DocumentVariableCatalog,
} from "@/lib/tiptap/document-variables";

interface DocumentVariableControlsProps {
  editorInstance: Editor;
  variableCatalog: DocumentVariableCatalog;
}

export function DocumentVariableControls(
  props: DocumentVariableControlsProps,
) {
  function insertVariableIntoDocument(path: string) {
    props.editorInstance
      .chain()
      .focus()
      .insertContent(documentVariableReference(path))
      .run();
  }

  return (
    <DropdownMenu>
      <DropdownMenuTrigger
        render={
          <Button variant="ghost" title="Inserir variável dinâmica">
            <BracketsCurly size={20} />
            <span className="hidden sm:inline">Variáveis</span>
            <CaretDown size={12} />
          </Button>
        }
      />
      <DropdownMenuContent className="max-h-80 w-auto min-w-64 overflow-y-auto">
        {props.variableCatalog.map((variableGroup, index) => (
          <DropdownMenuGroup key={variableGroup.group}>
            {index > 0 && <DropdownMenuSeparator />}
            <DropdownMenuLabel>{variableGroup.group}</DropdownMenuLabel>
            {variableGroup.items.map((variable) => (
              <DropdownMenuItem
                key={variable.path}
                className="flex-col items-start gap-0.5 py-2"
                onClick={() => insertVariableIntoDocument(variable.path)}
              >
                <span>{variable.label}</span>
                <span className="text-muted-foreground font-mono text-xs">
                  {documentVariableReference(variable.path)}
                </span>
              </DropdownMenuItem>
            ))}
          </DropdownMenuGroup>
        ))}
      </DropdownMenuContent>
    </DropdownMenu>
  );
}
