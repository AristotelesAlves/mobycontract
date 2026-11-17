import { Extension } from "@tiptap/core";
import { PluginKey } from "@tiptap/pm/state";
import { ReactRenderer } from "@tiptap/react";
import { Suggestion, type SuggestionProps } from "@tiptap/suggestion";
import {
  documentVariableReference,
  type DocumentVariableCatalog,
} from "../document-variables";
import {
  VariableSuggestionList,
  type DocumentVariableSuggestionItem,
  type DocumentVariableSuggestionRef,
} from "./variable-suggestion-list";

export const DOCUMENT_VARIABLE_SUGGESTION_EXTENSION_NAME =
  "documentoSugestaoDeVariaveis";

export type DocumentVariableSuggestionOptions = {
  catalog: DocumentVariableCatalog;
};

type SuggestionComponentType =
  ReactRenderer<DocumentVariableSuggestionRef>;

export const DocumentVariableSuggestionExtension =
  Extension.create<DocumentVariableSuggestionOptions>({
    name: DOCUMENT_VARIABLE_SUGGESTION_EXTENSION_NAME,

    addOptions() {
      return { catalog: [] };
    },

    addProseMirrorPlugins() {
      const catalogItems: DocumentVariableSuggestionItem[] =
        this.options.catalog.flatMap((group) => group.items);

      return [
        Suggestion<
          DocumentVariableSuggestionItem,
          DocumentVariableSuggestionItem
        >({
          editor: this.editor,
          char: "{{",
          pluginKey: new PluginKey(DOCUMENT_VARIABLE_SUGGESTION_EXTENSION_NAME),

          items: ({ query }) => {
            const termo = query.trim().toLowerCase();
            if (!termo) return catalogItems;

            return catalogItems.filter(
              (item) =>
                item.path.toLowerCase().includes(termo) ||
                item.label.toLowerCase().includes(termo),
            );
          },

          command: ({ editor, range, props }) => {
            editor
              .chain()
              .focus()
              .insertContentAt(range, documentVariableReference(props.path))
              .run();
          },

          shouldShow: ({ query }) => !query.includes("}}"),

          render: () => {
            let componente: SuggestionComponentType | null = null;
            let desmontar: (() => void) | null = null;

            return {
              onStart: (props) => {
                componente = new ReactRenderer(VariableSuggestionList, {
                  editor: props.editor,
                  props,
                });
                desmontar = props.mount(componente.element);
              },

              onUpdate: (
                props: SuggestionProps<
                  DocumentVariableSuggestionItem,
                  DocumentVariableSuggestionItem
                >,
              ) => {
                componente?.updateProps(props);
              },

              onKeyDown: ({ event }) => {
                if (
                  event.key === "ArrowUp" ||
                  event.key === "ArrowDown" ||
                  event.key === "Enter"
                ) {
                  event.preventDefault();
                  return componente?.ref?.onKeyDown({ event }) ?? false;
                }
                return false;
              },

              onExit: () => {
                desmontar?.();
                componente?.destroy();
                componente = null;
                desmontar = null;
              },
            };
          },
        }),
      ];
    },
  });