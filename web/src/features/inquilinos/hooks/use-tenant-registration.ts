"use client";

import { formatTaxId, formatPhone } from "@/lib/formatters";
import { useCallback, useState } from "react";
import { currentDateISO } from "@/lib/identifiers";
import { HttpError } from "@/core/errors/http-error";
import { getReadableErrorMessage } from "@/services/api/get-readable-error-message";
import { useFeedback } from "@/components/ui/feedback-provider";
import { createTenant } from "../services/tenant-service";
import {
  buildTenantPayload,
  validateTenantRegistration,
  type TenantFieldErrors,
  type TenantRegistrationDraft,
} from "../use-cases/validate-tenant-registration";

const EMPTY_DRAFT: TenantRegistrationDraft = {
  nome: "",
  cpf: "",
  rg: "",
  email: "",
  telefone: "",
  dataNascimento: "",
  estadoCivil: "",
  profissao: "",
  renda: "",
  endereco: "",
};

function mapearErrosDaApi(error: unknown): TenantFieldErrors {
  if (!(error instanceof HttpError)) return {};
  const campos = (error.body as { campos?: Record<string, string | string[]> })
    ?.campos;
  if (!campos) return {};

  const erros: TenantFieldErrors = {};
  for (const [campo, mensagens] of Object.entries(campos)) {
    erros[campo as keyof TenantFieldErrors] = Array.isArray(mensagens)
      ? mensagens.join(" ")
      : mensagens;
  }
  return erros;
}

export function useTenantRegistration(onRegistered: () => void) {
  const { pushFeedback } = useFeedback();
  const [draft, setDraft] = useState<TenantRegistrationDraft>(EMPTY_DRAFT);
  const [fieldErrors, setFieldErrors] = useState<TenantFieldErrors>({});
  const [submitError, setSubmitError] = useState("");
  const [submitting, setSubmitting] = useState(false);

  const patchField = useCallback(
    <K extends keyof TenantRegistrationDraft>(
      field: K,
      value: TenantRegistrationDraft[K],
    ) => {
      setDraft((current) => ({ ...current, [field]: field === "cpf" ? formatTaxId(String(value).replace(/\D/g, "").slice(0, 11)) : field === "telefone" ? formatPhone(String(value)) : value }));
      setFieldErrors((current) => ({ ...current, [field]: undefined }));
      setSubmitError("");
    },
    [],
  );

  const reset = useCallback(() => {
    setDraft(EMPTY_DRAFT);
    setFieldErrors({});
    setSubmitError("");
  }, []);

  const register = useCallback(async () => {
    let payload: TenantRegistrationDraft;
    try {
      validateTenantRegistration(draft, { todayISO: currentDateISO() });
      payload = draft;
    } catch (error) {
      setFieldErrors(
        (error as { details?: TenantFieldErrors }).details ?? {},
      );
      setSubmitError(getReadableErrorMessage(error));
      return;
    }

    setSubmitting(true);
    setSubmitError("");
    setFieldErrors({});
    try {
      const inquilino = await createTenant(buildTenantPayload(payload));
      pushFeedback(
        "success",
        `Inquilino ${inquilino.nome} cadastrado com sucesso.`,
      );
      setDraft(EMPTY_DRAFT);
      onRegistered();
    } catch (error) {
      setFieldErrors(mapearErrosDaApi(error));
      setSubmitError(getReadableErrorMessage(error));
      pushFeedback("error", getReadableErrorMessage(error));
    } finally {
      setSubmitting(false);
    }
  }, [draft, onRegistered, pushFeedback]);

  return {
    draft,
    fieldErrors,
    submitError,
    submitting,
    patchField,
    reset,
    register,
  };
}
