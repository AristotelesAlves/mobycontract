"use client";

import { useEffect, useState } from "react";
import type { IModeloClonar } from "../data/modelo-types";
import { listModelTemplates } from "../services/modelos-service";

export function useDocumentTemplates(): IModeloClonar[] {
  const [templates, setTemplates] = useState<IModeloClonar[]>([]);

  useEffect(() => {
    let active = true;
    listModelTemplates()
      .then((models) => {
        if (active) setTemplates(Array.isArray(models) ? models : []);
      })
      .catch(() => {
        if (active) setTemplates([]);
      });
    return () => {
      active = false;
    };
  }, []);

  return templates;
}