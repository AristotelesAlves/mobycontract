"use client";

import { useCallback, useEffect, useMemo, useState } from "react";
import { useRouter } from "next/navigation";
import { getReadableErrorMessage } from "@/services/api/get-readable-error-message";
import { useFeedback } from "@/components/ui/feedback-provider";
import {
  changeContractAddendumStatus,
  confirmAddendumPhysicalDocument,
  getContract,
  getContractAddendum,
  registerAddendumOnBlockchain,
  signContractAddendum,
} from "../services/contract-service";
import {
  resolveAddendumTemplate,
  addendumChangesHtml,
  buildAddendumDocumentValues,
} from "../use-cases/addendum-document";
import type { Contrato, ContratoAditivo, StatusAditivo } from "../data/contrato-types";
import type { IModeloClonar } from "@/features/modelos/data/modelo-types";
import { listModelTemplates } from "@/features/modelos/services/modelos-service";
import { documentHtmlForExport } from "@/lib/tiptap/html";
import { DOCUMENT_DEFAULT_MARGINS } from "@/lib/tiptap/document-geometry";
import { DOCUMENT_DEFAULT_TYPOGRAPHY } from "@/lib/tiptap/document-style";

export function useAddendumPreview(contractId: string, addendumId: string) {
  const router = useRouter();
  const { pushFeedback } = useFeedback();

  const [contract, setContract] = useState<Contrato | null>(null);
  const [addendum, setAddendum] = useState<ContratoAditivo | null>(null);
  const [templates, setTemplates] = useState<IModeloClonar[]>([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState("");
  const [reloadKey, setReloadKey] = useState(0);
  const [working, setWorking] = useState(false);
  const [uploadFile, setUploadFile] = useState<File | null>(null);
  const [isStepperVisible, setStepperVisible] = useState(false);

  const retry = useCallback(() => {
    setReloadKey((current) => current + 1);
  }, []);

  useEffect(() => {
    let cancelado = false;

    async function carregar() {
      setLoading(true);
      setError("");
      try {
        const [contratoCarregado, aditivoCarregado, modelos] =
          await Promise.all([
            getContract(contractId),
            getContractAddendum(contractId, addendumId),
            listModelTemplates().catch(() => [] as IModeloClonar[]),
          ]);
        if (cancelado) return;
        setContract(contratoCarregado);
        setAddendum(aditivoCarregado);
        setTemplates(Array.isArray(modelos) ? modelos : []);
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

  const withWork = useCallback(
    async (acao: () => Promise<ContratoAditivo | void>) => {
      if (working) return;
      setWorking(true);
      try {
        const atualizado = await acao();
        if (atualizado) setAddendum(atualizado);
      } catch (erro) {
        pushFeedback("error", getReadableErrorMessage(erro));
      } finally {
        setWorking(false);
      }
    },
    [working, pushFeedback],
  );

  const template = useMemo(
    () => resolveAddendumTemplate(addendum?.modeloId, templates),
    [addendum, templates],
  );

  const htmlContent = useMemo(() => {
    if (!addendum || !contract) return null;

    const exportDocument = {
      title: `Aditivo ${addendum.numero}`,
      htmlContent: `${addendum.snapshot?.modelo.conteudo_html ?? template.conteudo}${addendumChangesHtml(addendum)}`,
      margins: DOCUMENT_DEFAULT_MARGINS,
      typography: DOCUMENT_DEFAULT_TYPOGRAPHY,
      pageCount: 1,
      values: buildAddendumDocumentValues(contract, addendum),
    };

    return documentHtmlForExport(exportDocument);
  }, [addendum, contract, template]);

  const changeStatus = useCallback(
    (novoStatus: StatusAditivo, motivo?: string) => {
      void withWork(async () =>
        changeContractAddendumStatus(contractId, addendumId, novoStatus, motivo),
      );
    },
    [contractId, addendumId, withWork],
  );

  const assinar = useCallback(
    (assinaturaId: string) => {
      void withWork(async () => {
        const updated = await signContractAddendum(contractId, addendumId, assinaturaId);
        setAddendum(updated);
        if (updated.status === "Pendente de assinatura" && updated.assinaturas?.length && updated.assinaturas.every((signature) => signature.assinado)) {
          return changeContractAddendumStatus(contractId, addendumId, "Assinado");
        }
        return updated;
      });
    },
    [contractId, addendumId, withWork],
  );

  const confirmUpload = useCallback(() => {
    void withWork(async () =>
      confirmAddendumPhysicalDocument(contractId, addendumId, uploadFile),
    );
  }, [contractId, addendumId, uploadFile, withWork]);

  const registerOnBlockchain = useCallback(() => {
    void withWork(async () =>
      registerAddendumOnBlockchain(contractId, addendumId),
    );
  }, [contractId, addendumId, withWork]);

  const isBlockchainRegistered =
    addendum?.status === "Validado" || Boolean(addendum?.hashBlockchain);

  return {
    contract,
    addendum,
    template,
    htmlContent,
    isBlockchainRegistered,
    uploadFile,
    setUploadFile,
    isStepperVisible,
    toggleStepper: () => setStepperVisible((current) => !current),
    changeStatus,
    assinar,
    confirmUpload,
    registerOnBlockchain,
    working,
    loading,
    error,
    retry,
    isNotFound: !contract || !addendum,
    goToContracts: () => router.push(`/contratos/${contractId}?aba=aditivos`),
    goToEdit: () => {
      router.push(`/contratos/${contractId}/aditivos/${addendumId}/editar`);
    },
  };
}
