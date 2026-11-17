"use client";

import {
  createContext,
  useCallback,
  useContext,
  useMemo,
  useState,
  type ReactNode,
} from "react";
import {
  CheckCircle,
  Info,
  WarningCircle,
  X,
} from "@phosphor-icons/react";
import { cn } from "@/lib/utils";

export type FeedbackType = "success" | "error" | "info";

interface FeedbackEntry {
  id: number;
  type: FeedbackType;
  message: string;
}

interface FeedbackContextValue {
  pushFeedback: (type: FeedbackType, message: string) => void;
}

const FeedbackContext = createContext<FeedbackContextValue | null>(null);

const FEEDBACK_TIMEOUT = 5000;

const FEEDBACK_STYLES: Record<FeedbackType, string> = {
  success: "border-emerald-500/40 bg-emerald-500/10 text-emerald-700 dark:text-emerald-400",
  error: "border-destructive/40 bg-destructive/10 text-destructive",
  info: "border-blue-500/40 bg-blue-500/10 text-blue-600 dark:text-blue-400",
};

const FEEDBACK_ICONS: Record<FeedbackType, ReactNode> = {
  success: <CheckCircle size={18} weight="bold" className="shrink-0" />,
  error: <WarningCircle size={18} weight="bold" className="shrink-0" />,
  info: <Info size={18} weight="bold" className="shrink-0" />,
};

export function useFeedback(): FeedbackContextValue {
  const context = useContext(FeedbackContext);
  if (!context) {
    throw new Error("useFeedback deve ser usado dentro de FeedbackProvider");
  }
  return context;
}

export function FeedbackProvider({ children }: { children: ReactNode }) {
  const [items, setItems] = useState<FeedbackEntry[]>([]);

  const removeFeedback = useCallback((id: number) => {
    setItems((current) => current.filter((entry) => entry.id !== id));
  }, []);

  const pushFeedback = useCallback(
    (type: FeedbackType, message: string) => {
      const id = Date.now() + Math.random();
      setItems((current) => [...current, { id, type, message }]);
      window.setTimeout(() => removeFeedback(id), FEEDBACK_TIMEOUT);
    },
    [removeFeedback],
  );

  const value = useMemo(() => ({ pushFeedback }), [pushFeedback]);

  return (
    <FeedbackContext.Provider value={value}>
      {children}
      <div
        aria-live="polite"
        className="pointer-events-none fixed right-4 top-4 z-[100] flex w-full max-w-sm flex-col gap-2"
      >
        {items.map((entry) => (
          <div
            key={entry.id}
            className={cn(
              "pointer-events-auto flex items-start gap-2 rounded-lg border p-3 pr-2 text-sm shadow-lg backdrop-blur-sm",
              FEEDBACK_STYLES[entry.type],
            )}
          >
            {FEEDBACK_ICONS[entry.type]}
            <p className="flex-1 font-medium leading-snug">{entry.message}</p>
            <button
              type="button"
              aria-label="Fechar notificação"
              className="rounded-md p-1 opacity-70 transition-opacity hover:opacity-100"
              onClick={() => removeFeedback(entry.id)}
            >
              <X size={14} weight="bold" />
            </button>
          </div>
        ))}
      </div>
    </FeedbackContext.Provider>
  );
}
