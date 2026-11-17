"use client";

import { formatTaxId, formatPhone } from "@/lib/formatters";
import { useCallback, useEffect, useState } from "react";
import { currentDateISO } from "@/lib/identifiers";
import { HttpError } from "@/core/errors/http-error";
import { getReadableErrorMessage } from "@/services/api/get-readable-error-message";
import { useFeedback } from "@/components/ui/feedback-provider";
import { getTenant, updateTenant } from "../services/tenant-service";
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

function draftDeInquilino(tenant: Awaited<ReturnType<typeof getTenant>>): TenantRegistrationDraft {
  return {
    nome: tenant.nome ?? "",
    cpf: formatTaxId(tenant.cpf ?? ""),
    rg: tenant.rg ?? "",
    email: tenant.email ?? "",
    telefone: formatPhone(tenant.telefone ?? ""),
    dataNascimento: tenant.dataNascimento ?? "",
    estadoCivil: tenant.estadoCivil ?? "",
    profissao: tenant.profissao ?? "",
    renda: String(tenant.renda ?? ""),
    endereco: tenant.endereco ?? "",
  };
}

export function useTenantEdit(tenantId: string, onSaved: () => void) {
  const { pushFeedback } = useFeedback();
  const [draft, setDraft] = useState<TenantRegistrationDraft>(EMPTY_DRAFT);
  const [loading, setLoading] = useState(true);
  const [loadError, setLoadError] = useState("");
  const [fieldErrors, setFieldErrors] = useState<TenantFieldErrors>({});
  const [submitError, setSubmitError] = useState("");
  const [saving, setSaving] = useState(false);
  const [reloadKey, setReloadKey] = useState(0);

  const retry = useCallback(() => {
    setReloadKey((current) => current + 1);
  }, []);

  useEffect(() => {
    let cancelado = false;

    async function carregar() {
      setLoading(true);
      setLoadError("");
      try {
        const inquilino = await getTenant(tenantId);
        if (cancelado) return;
        setDraft(draftDeInquilino(inquilino));
      } catch (erro) {
        if (cancelado) return;
        setLoadError(getReadableErrorMessage(erro));
      } finally {
        if (!cancelado) setLoading(false);
      }
    }

    carregar();
    return () => {
      cancelado = true;
    };
  }, [tenantId, reloadKey]);

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

  const save = useCallback(async () => {
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

    setSaving(true);
    setSubmitError("");
    setFieldErrors({});
    try {
      const inquilino = await updateTenant(tenantId, buildTenantPayload(payload));
      pushFeedback(
        "success",
        `Dados de ${inquilino.nome} atualizados com sucesso.`,
      );
      onSaved();
    } catch (error) {
      setFieldErrors(mapearErrosDaApi(error));
      setSubmitError(getReadableErrorMessage(error));
      pushFeedback("error", getReadableErrorMessage(error));
    } finally {
      setSaving(false);
    }
  }, [draft, tenantId, onSaved, pushFeedback]);

  return {
    draft,
    fieldErrors,
    submitError,
    loading,
    loadError,
    saving,
    retry,
    patchField,
    save,
  };
}
