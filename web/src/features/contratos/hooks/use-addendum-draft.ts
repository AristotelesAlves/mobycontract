"use client";

import { useCallback, useState } from "react";
import { getReadableErrorMessage } from "@/services/api/get-readable-error-message";
import { currentDateISO } from "@/lib/identifiers";
import type { MotivoAditivo } from "../data/contrato-types";
import {
  buildAddendumExtraData,
  EMPTY_ADDENDUM_DRAFT,
  toggleAddendumReason,
  validateAddendumDraft,
  type AddendumDraft,
  type AddendumFieldErrors,
} from "../use-cases/addendum-draft";

export function useAddendumDraft(initialDraft?: Partial<AddendumDraft>) {
  const [draft, setDraft] = useState<AddendumDraft>(() => ({
    ...EMPTY_ADDENDUM_DRAFT,
    data: currentDateISO(),
    ...initialDraft,
  }));
  const [fieldErrors, setFieldErrors] = useState<AddendumFieldErrors>({});
  const [submitError, setSubmitError] = useState("");

  const patchDraft = useCallback(
    (field: keyof AddendumDraft, value: string) => {
      setDraft((current) => ({ ...current, [field]: value }));
      if (field === "titulo" || field === "modeloId") {
        setFieldErrors((current) => ({
          ...current,
          [field === "modeloId" ? "modelo" : "titulo"]: undefined,
        }));
      }
    },
    [],
  );

  const toggleReason = useCallback((reason: MotivoAditivo) => {
    setDraft((current) => ({
      ...current,
      motivos: toggleAddendumReason(current.motivos, reason),
    }));
    setFieldErrors((current) => ({ ...current, motivos: undefined }));
  }, []);

  const hydrate = useCallback((next: AddendumDraft) => {
    setDraft(next);
    setFieldErrors({});
    setSubmitError("");
  }, []);

  const validate = useCallback((): boolean => {
    try {
      validateAddendumDraft(draft);
      setFieldErrors({});
      setSubmitError("");
      return true;
    } catch (error) {
      setFieldErrors(
        (error as { details?: AddendumFieldErrors }).details ?? {},
      );
      setSubmitError(getReadableErrorMessage(error));
      return false;
    }
  }, [draft]);

  const extraData = useCallback(() => buildAddendumExtraData(draft), [draft]);

  return {
    draft,
    fieldErrors,
    submitError,
    patchDraft,
    toggleReason,
    hydrate,
    validate,
    extraData,
  };
}