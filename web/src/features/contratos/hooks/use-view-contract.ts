"use client";

import { useEffect, useMemo, useState } from "react";
import { listModelTemplates } from "@/features/modelos/services/modelos-service";
import type { IModeloClonar } from "@/features/modelos/data/modelo-types";
import { getContract } from "../services/contract-service";
import type { Contrato } from "../data/contrato-types";
import { gerarHtmlContrato } from "../use-cases/generate-contract-html";

export function useVisualizarContrato(idInicial: string) {
  const [contrato, setContrato] = useState<Contrato | null>(null);
  const [templates, setTemplates] = useState<IModeloClonar[]>([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState("");
  const [reloadKey, setReloadKey] = useState(0);

  useEffect(() => {
    let cancelado = false;

    async function carregar() {
      setLoading(true);
      setError("");
      try {
        const contratoCarregado = await getContract(idInicial);
        const modelos = contratoCarregado.snapshot ? [] : await listModelTemplates().catch(() => [] as IModeloClonar[]);
        if (cancelado) return;
        setContrato(contratoCarregado);
        setTemplates(Array.isArray(modelos) ? modelos : []);
      } catch (erro) {
        if (cancelado) return;
        setError(
          erro instanceof Error ? erro.message : "Falha ao carregar contrato.",
        );
      } finally {
        if (!cancelado) setLoading(false);
      }
    }

    carregar();
    return () => {
      cancelado = true;
    };
  }, [idInicial, reloadKey]);

  const modelo = useMemo(
    () =>
      contrato
        ? templates.find((m) => m.uuid === contrato.modeloId)
        : undefined,
    [contrato, templates],
  );

  const htmlContrato = useMemo(() => {
    if (!contrato) return null;
    return gerarHtmlContrato(contrato, modelo);
  }, [contrato, modelo]);

  return { contrato, htmlContrato, loading, error, reload: () => setReloadKey((current) => current + 1) };
}
