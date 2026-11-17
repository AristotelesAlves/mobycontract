"use client";

import { useCallback, useEffect, useState } from "react";
import { useRouter } from "next/navigation";
import { HttpError } from "@/core/errors/http-error";
import { getReadableErrorMessage } from "@/services/api/get-readable-error-message";
import { useFeedback } from "@/components/ui/feedback-provider";
import type { Imovel } from "@/features/imoveis/data/imovel-types";
import { listProperties } from "@/features/imoveis/services/property-service";
import type { Inquilino } from "@/features/inquilinos/data/inquilino-types";
import { listTenants } from "@/features/inquilinos/services/tenant-service";
import { useDocumentTemplates } from "@/features/modelos/hooks/use-document-templates";
import { useContractParties } from "./use-contract-parties";
import { useContractGuarantees } from "./use-contract-guarantees";
import { parseValorPtBr } from "@/lib/numeros";
import {
  createContract,
  type CriarContratoPayload,
} from "../services/contract-service";
import {
  validateContractDraft,
  type ContractFieldErrors,
  type ContractFieldName,
} from "../use-cases/validate-contract-draft";

export type CreateContractHookData = ReturnType<typeof useCreateContract>;

const CAMPO_PARA_CAMPO_FRONTEND: Record<string, ContractFieldName> = {
  imovelId: "imovel",
  modeloId: "modelo",
  status: "modelo",
  prazoVigencia: "vigencia",
  dataInicio: "startDate",
  dataFim: "endDate",
  indiceReajuste: "reajuste",
  modoInquilino: "inquilino",
  inquilinoId: "inquilino",
  valorCaucao: "guarantees",
  comprovanteGarantia: "upload",
  fiadores: "guarantees",
  nomeLocador: "locador",
  descontoMensal: "desconto",
  multaPorAtraso: "multaAtraso",
  multaRescisao: "multaRescisao",
  observacoes: "observacoes",
};

/**
 * Converte a chave do erro enviada pela API (ex.: "inquilino.estadoCivil",
 * "fiadores.0.nome") na seção do formulário que mostra o erro. Campos sem
 * seção correspondente retornam null — a mensagem continua aparecendo no
 * resumo de erro exibido ao usuário.
 */
function campoParaFrontend(campo: string): ContractFieldName | null {
  if (campo.startsWith("inquilino.")) return "novoInquilino";
  const base = campo.split(".")[0] ?? campo;
  return CAMPO_PARA_CAMPO_FRONTEND[base] ?? null;
}

const TRADUCAO_ERRO: Record<string, string> = {
  "validation.exists": "não foi encontrado",
  "validation.required_if": "é obrigatório",
  "validation.required_with": "é obrigatório",
  "validation.prohibited_if": "não deve ser informado",
  "validation.integer": "deve ser um número válido",
  "validation.required": "é obrigatório",
  "validation.numeric": "deve ser um número",
  "validation.between": "está fora do intervalo permitido",
  "validation.min": "está abaixo do mínimo permitido",
  "validation.date": "data inválida",
  "validation.after_or_equal": "deve ser igual ou posterior à data de início",
  "validation.after": "deve ser posterior à data de início",
  "validation.array": "deve ser uma lista",
  "validation.string": "deve ser um texto",
  "validation.email": "e-mail inválido",
};

export interface ErrosDeCamposMapeados {
  campos: ContractFieldErrors;
  mensagem: string;
}

/**
 * Traduz a resposta 422 da API em: (a) erros por seção do formulário e
 * (b) uma mensagem única que lista todos os problemas, para o usuário ver
 * exatamente o que impediu a criação.
 */
export function mapearErrosDeCampos(error: unknown): ErrosDeCamposMapeados | null {
  if (!(error instanceof HttpError) || error.statusCode !== 422) return null;
  const body = error.body as {
    campos?: Record<string, string[]>;
    mensagem?: string;
  } | null;
  if (!body?.campos) return null;

  const campos: Record<string, string> = {};
  const detalhes: string[] = [];
  for (const [campo, mensagensRecebidas] of Object.entries(body.campos)) {
    // O backend envia os valores como lista (ValidationException) ou texto
    // simples (RegraNegocioException) — normaliza sempre para lista.
    const mensagens = Array.isArray(mensagensRecebidas)
      ? mensagensRecebidas
      : [String(mensagensRecebidas ?? "")].filter(Boolean);
    if (mensagens.length === 0) continue;
    const texto = mensagens
      .map((mensagem) => mensagem ?? "")
      .map((mensagem) => {
        let mensagemTraduzida = mensagem;
        for (const [chave, valor] of Object.entries(TRADUCAO_ERRO)) {
          mensagemTraduzida = mensagemTraduzida.replace(chave, valor);
        }
        return mensagemTraduzida;
      })
      .join(". ");
    const campoFrontend = campoParaFrontend(campo);
    if (campoFrontend && !campos[campoFrontend]) campos[campoFrontend] = texto;
    if (texto && !detalhes.includes(texto)) detalhes.push(texto);
  }

  return {
    campos: campos as ContractFieldErrors,
    mensagem: montarMensagemDeErros(
      body.mensagem ?? "Verifique os campos informados.",
      detalhes,
    ),
  };
}

/** Mensagem final exibida ao usuário: título + lista exata dos problemas. */
export function montarMensagemDeErros(mensagem: string, detalhes: string[]): string {
  const pendentes = detalhes
    .map((detalhe) => detalhe.trim())
    .filter((detalhe) => detalhe.length > 0 && detalhe !== mensagem && !mensagem.includes(detalhe));
  if (pendentes.length === 0) return mensagem;
  const lista = pendentes
    .map((detalhe) => (detalhe.endsWith(".") ? detalhe.slice(0, -1) : detalhe))
    .join(" · ");
  const separador = /[.:!?]$/.test(mensagem) ? " " : ": ";
  return `${mensagem}${separador}— ${lista}.`;
}

export function useCreateContract() {
  const router = useRouter();
  const { pushFeedback } = useFeedback();
  const parties = useContractParties();
  const guarantees = useContractGuarantees();

  const allTemplates = useDocumentTemplates();
  const contractTemplates = allTemplates.filter(
    (template) => template.tipo === "Contrato" && template.status
      && (template.garantia ?? "Sem garantia").trim().toLocaleLowerCase("pt-BR")
        === guarantees.guaranteeType.toLocaleLowerCase("pt-BR"),
  );

  const [properties, setProperties] = useState<Imovel[]>([]);
  const [tenants, setTenants] = useState<Inquilino[]>([]);
  const [loading, setLoading] = useState(true);
  const [dadosError, setDadosError] = useState("");
  const [reloadKey, setReloadKey] = useState(0);

  const [propertyId, setPropertyId] = useState("");
  const [templateId, setTemplateId] = useState("");
  const [termType, setTermType] = useState("Determinado");
  const [startDate, setStartDate] = useState("");
  const [endDate, setEndDate] = useState("");
  const [monthlyDiscount, setMonthlyDiscount] = useState("");
  const [adjustmentIndex, setAdjustmentIndex] = useState("IGP-M");
  const [lateFeePercent, setLateFeePercent] = useState("10");
  const [terminationFeePercent, setTerminationFeePercent] = useState("3");
  const [observations, setObservations] = useState("");
  const nomeLocador = properties.find((property) => property.id === propertyId)?.nomeProprietario?.trim() || "";

  const [fieldErrors, setFieldErrors] = useState<ContractFieldErrors>({});
  const [submitError, setSubmitError] = useState("");
  const [submitting, setSubmitting] = useState(false);

  const reload = useCallback(() => {
    setReloadKey((current) => current + 1);
  }, []);

  useEffect(() => {
    let cancelado = false;

    async function carregar() {
      setLoading(true);
      setDadosError("");
      try {
        const [resultadoImoveis, resultadoInquilinos] = await Promise.all([
          listProperties({ tamanhoPagina: 100 }),
          listTenants({ tamanhoPagina: 100 }),
        ]);
        if (cancelado) return;
        setProperties(resultadoImoveis.itens);
        setTenants(resultadoInquilinos.itens);
      } catch (erro) {
        if (cancelado) return;
        setDadosError(getReadableErrorMessage(erro));
      } finally {
        if (!cancelado) setLoading(false);
      }
    }

    carregar();
    return () => {
      cancelado = true;
    };
  }, [reloadKey]);

  const montarPayload = useCallback((): CriarContratoPayload => {
    const temFiador = guarantees.guaranteeType === "Fiador" && guarantees.guarantors.some((fiador) => fiador.nome.trim() || fiador.cpf.trim());
    const rendaNovoInquilino = parseValorPtBr(parties.newTenant.renda);
    const valorCaucao =
      guarantees.guaranteeType === "Caução"
        ? parseValorPtBr(guarantees.depositAmount)
        : null;
    // 0/zerado significa "usar o padrão do sistema (3 aluguéis)" — enviar 0
    // seria rejeitado pelo backend (mínimo de 1 aluguel).
    const multaRescisaoValor = Number.parseFloat(
      terminationFeePercent.replace(",", "."),
    );
    const multaRescisaoValida =
      Number.isFinite(multaRescisaoValor) && multaRescisaoValor > 0;

    const fiadores = temFiador
      ? guarantees.guarantors
          .filter((fiador) => fiador.nome.trim() || fiador.cpf.trim())
          .map((fiador) => ({
            nome: fiador.nome,
            cpf: fiador.cpf.replace(/\D/g, ""),
            telefone: fiador.telefone,
          }))
      : undefined;

    const inquilinoNovo = parties.partyMode === "novo"
      ? {
          nome: parties.newTenant.nome.trim(),
          cpf: parties.newTenant.documento.replace(/\D/g, ""),
          rg: parties.newTenant.rg?.trim() || undefined,
          email: parties.newTenant.email.trim(),
          telefone: parties.newTenant.telefone.trim(),
          dataNascimento: parties.newTenant.dataNascimento,
          estadoCivil: parties.newTenant.estadoCivil,
          renda: rendaNovoInquilino ?? 0,
          profissao: parties.newTenant.profissao?.trim() || undefined,
        }
      : undefined;

    return {
      comprovanteGarantia: ["Caução", "Fiador"].includes(guarantees.guaranteeType) ? guarantees.attachment : undefined,
      imovelId: Number.parseInt(propertyId, 10),
      modeloId: Number.parseInt(templateId, 10),
      prazoVigencia: termType,
      dataInicio: startDate,
      dataFim: termType === "Determinado" && endDate ? endDate : undefined,
      indiceReajuste: adjustmentIndex,
      modoInquilino: parties.partyMode,
      inquilinoId:
        parties.partyMode === "existente"
          ? Number.parseInt(parties.existingTenantId, 10)
          : undefined,
      inquilino: inquilinoNovo,
      valorCaucao:
        valorCaucao !== null && valorCaucao > 0 ? valorCaucao : undefined,
      fiadores:
        guarantees.guaranteeType === "Fiador" ? fiadores : undefined,
      nomeLocador: nomeLocador.trim(),
      descontoMensal: parseValorPtBr(monthlyDiscount) ?? 0,
      multaPorAtraso: parseValorPtBr(lateFeePercent) ?? 0,
      multaRescisao: multaRescisaoValida ? multaRescisaoValor : undefined,
      observacoes: observations.trim() || undefined,
      diaVencimentoParcela: 5,
    };
  }, [
    parties,
    guarantees,
    termType,
    startDate,
    endDate,
    nomeLocador,
    propertyId,
    templateId,
    adjustmentIndex,
    monthlyDiscount,
    lateFeePercent,
    terminationFeePercent,
    observations,
  ]);

  const submit = useCallback(async () => {
    const property = properties.find((candidate) => candidate.id === propertyId);
    const template = contractTemplates.find(
      (candidate) => candidate.uuid === templateId,
    );

    try {
      validateContractDraft(
        {
          partyMode: parties.partyMode,
          existingTenantId: parties.existingTenantId,
          newTenant: parties.newTenant,
          guaranteeType: guarantees.guaranteeType,
          depositAmount: guarantees.depositAmount,
          guarantors: guarantees.guarantors,
          attachmentStatus: guarantees.attachmentStatus,
          termType,
          startDate,
          endDate,
        },
        {
          hasProperty: Boolean(property),
          hasTemplate: Boolean(template),
          hasLocador: nomeLocador.trim().length > 0,
        },
      );
      setFieldErrors({});
      setSubmitError("");
    } catch (error) {
      setFieldErrors(
        (error as { details?: ContractFieldErrors }).details ?? {},
      );
      setSubmitError(getReadableErrorMessage(error));
      window.scrollTo({ top: 0, behavior: "smooth" });
      return;
    }

    if (!property || !template) return;

    setSubmitting(true);
    try {
      const criado = await createContract(montarPayload());
      pushFeedback(
        "success",
        `Contrato nº ${criado.numero} criado com sucesso.`,
      );
      router.push(`/contratos/${criado.id}`);
    } catch (error) {
      let erros: ErrosDeCamposMapeados | null = null;
      try {
        erros = mapearErrosDeCampos(error);
      } catch {
        // mapeamento nunca pode silenciar o erro original
        erros = null;
      }
      if (erros) {
        setFieldErrors(erros.campos);
        setSubmitError(erros.mensagem);
        pushFeedback("error", erros.mensagem);
      } else {
        const mensagem = getReadableErrorMessage(error);
        setSubmitError(mensagem);
        pushFeedback("error", mensagem);
      }
      window.scrollTo({ top: 0, behavior: "smooth" });
    } finally {
      setSubmitting(false);
    }
  }, [
    properties,
    contractTemplates,
    propertyId,
    templateId,
    parties,
    guarantees,
    termType,
    startDate,
    endDate,
    nomeLocador,
    montarPayload,
    pushFeedback,
    router,
  ]);

  return {
    properties,
    tenants,
    contractTemplates,
    propertyId,
    templateId,
    termType,
    startDate,
    endDate,
    monthlyDiscount,
    adjustmentIndex,
    lateFeePercent,
    terminationFeePercent,
    observations,
    nomeLocador,
    fieldErrors,
    submitError,
    submitting,
    loading,
    dadosError,
    reload,
    parties,
    guarantees,
    setPropertyId,
    setTemplateId,
    setTermType,
    setStartDate,
    setEndDate,
    setMonthlyDiscount,
    setAdjustmentIndex,
    setLateFeePercent,
    setTerminationFeePercent,
    setObservations,
    submit,
  };
}
