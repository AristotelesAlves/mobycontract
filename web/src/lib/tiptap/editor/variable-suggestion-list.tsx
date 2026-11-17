"use client";

import {
  forwardRef,
  useCallback,
  useEffect,
  useImperativeHandle,
  useState,
} from "react";
import type { SuggestionProps } from "@tiptap/suggestion";
import { documentVariableReference } from "../document-variables";

export type DocumentVariableSuggestionItem = {
  path: string;
  label: string;
};

export type DocumentVariableSuggestionRef = {
  onKeyDown: (props: { event: KeyboardEvent }) => boolean;
};

type VariableSuggestionListProps = SuggestionProps<DocumentVariableSuggestionItem>;

export const VariableSuggestionList = forwardRef<
  DocumentVariableSuggestionRef,
  VariableSuggestionListProps
>(function VariableSuggestionList(props, ref) {
  const [selectedIndex, setSelectedIndex] = useState(0);
  const itemCount = props.items.length;

  useEffect(() => {
    setSelectedIndex(0);
  }, [props.query]);

  useEffect(() => {
    setSelectedIndex((current) =>
      itemCount === 0 ? 0 : Math.min(current, itemCount - 1),
    );
  }, [itemCount]);

  const selectedItemRef = useCallback(
    (element: HTMLElement | null) => {
      if (!element) return;
      element.scrollIntoView({ block: "nearest" });
    },
    [],
  );

  useImperativeHandle(
    ref,
    () => ({
      onKeyDown: ({ event }) => {
        if (itemCount === 0) return false;

        if (event.key === "ArrowUp") {
          setSelectedIndex(
            (current) => (current - 1 + itemCount) % itemCount,
          );
          return true;
        }

        if (event.key === "ArrowDown") {
          setSelectedIndex((current) => (current + 1) % itemCount);
          return true;
        }

        if (event.key !== "Enter" || event.isComposing) return false;

        const item = props.items[selectedIndex];
        if (!item) return true;
        props.command(item);
        return true;

      },
    }),
    [props, selectedIndex, itemCount],
  );

  if (itemCount === 0) {
    return (
      <div className="w-64 rounded-lg border bg-popover p-3 text-sm text-muted-foreground shadow-lg">
        Nenhuma variável encontrada
      </div>
    );
  }

  return (
    <div className="w-72 rounded-lg border bg-popover text-popover-foreground shadow-lg">
      <ul className="max-h-64 overflow-y-auto p-1">
        {props.items.map((item, index) => {
          const selecionado = index === selectedIndex;

          return (
            <li key={item.path}>
              <button
                ref={selecionado ? selectedItemRef : undefined}
                type="button"
                className={`flex w-full flex-col items-start gap-0.5 rounded-md px-2.5 py-2 text-left text-sm outline-none ${
                  selecionado ? "bg-primary text-primary-foreground" : ""
                }`}
                onMouseEnter={() => setSelectedIndex(index)}
                onClick={() => props.command(item)}
              >
                <span className="w-full truncate font-medium">
                  {item.label}
                </span>
                <code
                  className={`w-full truncate font-mono text-xs ${
                    selecionado
                      ? "text-primary-foreground/80"
                      : "text-muted-foreground"
                  }`}
                >
                  {documentVariableReference(item.path)}
                </code>
              </button>
            </li>
          );
        })}
      </ul>
    </div>
  );
});