"use client";

import { useCallback, useState } from "react";
import { getReadableErrorMessage } from "@/services/api/get-readable-error-message";
import {
  DOCUMENT_TEMPLATE_TYPES,
  buildDocumentSaveData,
  supportsRelatedGuarantee,
  validateDocumentTemplateDraft,
  type DocumentSaveData,
  type DocumentTemplateDraft,
  type DocumentTemplateFieldErrors,
  type DocumentTemplateType,
} from "../use-cases/document-template-draft";

export interface DocumentTemplateSeed {
  title: string;
  type?: string;
  version?: number;
  guarantee?: string;
}

export function useDocumentTemplateDraft(seed: DocumentTemplateSeed) {
  const [draft, setDraft] = useState<DocumentTemplateDraft>({
    title: seed.title,
    type: (seed.type as DocumentTemplateType | undefined) ?? "",
    guarantee: seed.guarantee ?? "",
    version: seed.version ?? 1,
  });
  const [fieldErrors, setFieldErrors] = useState<DocumentTemplateFieldErrors>(
    {},
  );
  const [submitError, setSubmitError] = useState("");

  const reset = useCallback(() => {
    setDraft({
      title: seed.title,
      type: (seed.type as DocumentTemplateType | undefined) ?? "",
      guarantee: seed.guarantee ?? "",
      version: seed.version ?? 1,
    });
    setFieldErrors({});
    setSubmitError("");
  }, [seed.title, seed.type, seed.version, seed.guarantee]);

  const changeTitle = useCallback((value: string) => {
    setDraft((current) => ({ ...current, title: value }));
    setFieldErrors((current) => ({ ...current, title: undefined }));
  }, []);

  const changeType = useCallback((value: DocumentTemplateType) => {
    setDraft((current) => ({
      ...current,
      type: value,
      guarantee: supportsRelatedGuarantee(value) ? current.guarantee : "",
    }));
    setFieldErrors((current) => ({ ...current, tipo: undefined }));
  }, []);

  const changeGuarantee = useCallback((value: string) => {
    setDraft((current) => ({ ...current, guarantee: value }));
  }, []);

  const submit = useCallback(
    async (
      htmlContent: string,
      onSave: (data: DocumentSaveData) => boolean | Promise<boolean>,
    ): Promise<boolean> => {
      try {
        validateDocumentTemplateDraft(draft);
        setFieldErrors({});
        setSubmitError("");
      } catch (error) {
        setFieldErrors(
          (error as { details?: DocumentTemplateFieldErrors }).details ?? {},
        );
        setSubmitError(getReadableErrorMessage(error));
        return false;
      }

      try {
        const resultado = await onSave(buildDocumentSaveData(draft, htmlContent));
        return resultado !== false;
      } catch (error) {
        setSubmitError(getReadableErrorMessage(error));
        return false;
      }
    },
    [draft],
  );

  return {
    templateTypes: DOCUMENT_TEMPLATE_TYPES,
    draft,
    fieldErrors,
    submitError,
    guaranteeVisible: supportsRelatedGuarantee(draft.type),
    changeTitle,
    changeType,
    changeGuarantee,
    reset,
    submit,
  };
}

export type {
  DocumentSaveData,
  DocumentTemplateDraft,
  DocumentTemplateFieldErrors,
  DocumentTemplateType,
} from "../use-cases/document-template-draft";
