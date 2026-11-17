"use client";

import { useCallback, useEffect, useRef, useState } from "react";
import { useRouter } from "next/navigation";
import { getReadableErrorMessage } from "@/services/api/get-readable-error-message";
import { useFeedback } from "@/components/ui/feedback-provider";
import { currentDateISO } from "@/lib/identifiers";
import type { IModeloClonar } from "@/features/modelos/data/modelo-types";
import { listModelTemplates } from "@/features/modelos/services/modelos-service";
import { parseValorPtBr } from "@/lib/numeros";
import { useAddendumDraft } from "./use-addendum-draft";
import { addendumDraftFromRecord } from "../use-cases/addendum-draft";
import {
  createContractAddendum,
  getContract,
  getContractAddendum,
  updateContractAddendum,
  type CriarAditivoPayload,
} from "../services/contract-service";
import type {
  Contrato,
  ContratoAditivo,
} from "../data/contrato-types";
import { MOTIVOS_ADITIVO } from "../data/contrato-types";

export function useAddendumEditor(contractId: string, addendumId?: string) {
  const router = useRouter();
  const { pushFeedback } = useFeedback();
  const editor = useAddendumDraft();
  const hydratedIdRef = useRef<string | null>(null);

  const [contract, setContract] = useState<Contrato | null>(null);
  const [addendum, setAddendum] = useState<ContratoAditivo | null>(null);
  const [models, setModels] = useState<IModeloClonar[]>([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState("");
  const [reloadKey, setReloadKey] = useState(0);
  const [submitting, setSubmitting] = useState(false);

  const retry = useCallback(() => {
    setReloadKey((current) => current + 1);
  }, []);

  useEffect(() => {
    let cancelado = false;

    async function carregar() {
      setLoading(true);
      setError("");
      try {
        const [contratoCarregado, modelos] = await Promise.all([
          getContract(contractId),
          listModelTemplates().catch(() => [] as IModeloClonar[]),
        ]);
        if (cancelado) return;
        setContract(contratoCarregado);
        setModels(modelos.filter((item) => item.tipo === "Aditivo"));

        if (!addendumId) return;
        const aditivoCarregado = await getContractAddendum(
          contractId,
          addendumId,
        );
        if (cancelado) return;
        setAddendum(aditivoCarregado);
      } catch (erro) {
        if (cancelado) return;
        setError(getReadableErrorMessage(erro));
      } finally {
        if (!cancelado) setLoading(false);
      }
    }

    carregar();
    return () => {
      cancelado = true;
    };
  }, [contractId, addendumId, reloadKey]);

  useEffect(() => {
    if (!addendum || !addendumId) return;
    if (hydratedIdRef.current === addendumId) return;
    hydratedIdRef.current = addendumId;
    editor.hydrate(addendumDraftFromRecord(addendum));
  }, [addendum, addendumId, editor]);

  const save = useCallback(async (): Promise<boolean> => {
    if (!editor.validate()) return false;

    const { draft } = editor;
    const valorDe = (valor: string): number | undefined => {
      const numero = parseValorPtBr(valor);
      return numero !== null && numero > 0 ? numero : undefined;
    };

    const payload: CriarAditivoPayload = {
      titulo: draft.titulo.trim(),
      descricao: draft.descricao.trim() || undefined,
      data: draft.data || currentDateISO(),
      modeloId: Number.parseInt(draft.modeloId, 10),
      motivos: draft.motivos,
      novoValor: draft.motivos.includes("Valor")
        ? valorDe(draft.novoValor)
        : undefined,
      novaDataFim: draft.motivos.includes("Vigência")
        ? draft.novaDataFim || undefined
        : undefined,
      clausulaModificada: draft.motivos.includes("Cláusula")
        ? draft.clausulaModificada
        : undefined,
      novaTaxaCondominio: draft.motivos.includes("Taxa de Condomínio")
        ? valorDe(draft.novaTaxaCondominio)
        : undefined,
      novoIPTU: draft.motivos.includes("IPTU")
        ? valorDe(draft.novoIPTU)
        : undefined,
      novoSeguroIncendio: draft.motivos.includes("Seguro Incêndio")
        ? valorDe(draft.novoSeguroIncendio)
        : undefined,
      novoAgua: draft.motivos.includes("Água") ? valorDe(draft.novoAgua) : undefined,
      novoEnergia: draft.motivos.includes("Energia")
        ? valorDe(draft.novoEnergia)
        : undefined,
    };

    setSubmitting(true);
    try {
      if (addendumId) {
        await updateContractAddendum(contractId, addendumId, payload);
        pushFeedback("success", "Aditivo atualizado com sucesso.");
      } else {
        await createContractAddendum(contractId, payload);
        pushFeedback("success", "Aditivo criado com sucesso.");
      }
      router.push(`/contratos/${contractId}?aba=aditivos`);
      return true;
    } catch (erro) {
      pushFeedback("error", getReadableErrorMessage(erro));
      return false;
    } finally {
      setSubmitting(false);
    }
  }, [editor, addendumId, contractId, pushFeedback, router]);

  return {
    contract,
    addendum,
    draft: editor.draft,
    fieldErrors: editor.fieldErrors,
    patchDraft: editor.patchDraft,
    toggleReason: editor.toggleReason,
    submitError: editor.submitError,
    models,
    reasons: MOTIVOS_ADITIVO,
    save,
    submitting,
    loading,
    error,
    retry,
    goToContracts: () => router.push("/contratos"),
    isNotFound: !contract || (Boolean(addendumId) && !addendum),
  };
}
