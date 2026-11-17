"use client";

import { useCallback, useEffect, useState } from "react";
import { useRouter } from "next/navigation";
import { HttpError } from "@/core/errors/http-error";
import { getReadableErrorMessage } from "@/services/api/get-readable-error-message";
import { useFeedback } from "@/components/ui/feedback-provider";
import { currentDateISO } from "@/lib/identifiers";
import {
  createVistoria,
  getVistoria,
  updateVistoria,
  type AlterarVistoriaPayload,
  type ItemVistoriaPayload,
} from "../services/vistoria-service";
import { itensPadrao } from "../data/default-checklist-items";
import {
  VISTORIA_ESTADOS_ITEM,
  VISTORIA_TIPOS,
  type VistoriaEstadoItem,
  type VistoriaItem,
  type VistoriaTipo,
} from "../data/vistoria-types";

export interface VistoriaRegistrationOptions {
  contratoId: string;
  vistoriaId?: string;
}

export type VistoriaFieldError = Record<string, string>;

const TRADUCAO_REGRAS: Array<[string, string]> = [
  ["validation.required", "é obrigatório"],
  ["validation.string", "deve ser um texto"],
  ["validation.max", "é longo demais"],
  ["validation.date_format", "deve ser uma data no formato YYYY-MM-DD"],
  ["validation.in", "não está entre as opções permitidas"],
  ["validation.array", "deve ser uma lista"],
  ["validation.exists", "não foi encontrado"],
];

function lerErrosDeCampos(error: unknown): VistoriaFieldError {
  if (!(error instanceof HttpError) || error.statusCode !== 422) return {};
  const body = error.body as { campos?: Record<string, string[]> } | null;
  if (!body?.campos) return {};

  const erros: VistoriaFieldError = {};
  for (const [campo, mensagens] of Object.entries(body.campos)) {
    const campoFrontend = campo.startsWith("itens")
      ? "itens"
      : campo.replace(/\.\d+/g, "");
    erros[campoFrontend] = mensagens
      .map((mensagem) => {
        let texto = mensagem;
        for (const [regra, traducao] of TRADUCAO_REGRAS) {
          texto = texto.replace(regra, traducao);
        }
        return texto;
      })
      .join(". ");
  }
  return erros;
}

function itemParaPayload(item: VistoriaItem): ItemVistoriaPayload {
  return {
    ambiente: item.ambiente,
    item: item.item,
    estado: item.estado,
    observacao: item.observacao?.trim() || undefined,
  };
}

export function useVistoriaRegistration(options: VistoriaRegistrationOptions) {
  const router = useRouter();
  const { pushFeedback } = useFeedback();
  const modo: "criar" | "editar" = options.vistoriaId ? "editar" : "criar";
  const contratoId = options.contratoId;

  const [tipo, setTipo] = useState<VistoriaTipo>("Entrada");
  const [data, setData] = useState(currentDateISO());
  const [vistoriador, setVistoriador] = useState("");
  const [observacoes, setObservacoes] = useState("");
  const [itens, setItens] = useState<VistoriaItem[]>([]);

  const [errors, setErrors] = useState<VistoriaFieldError>({});
  const [bannerErro, setBannerErro] = useState("");
  const [saving, setSaving] = useState(false);
  const [carregando, setCarregando] = useState(false);

  // Em modo edição, carrega a vistoria para hidratar o formulário.
  useEffect(() => {
    if (!options.vistoriaId) return;
    let cancelado = false;

    async function carregar() {
      setCarregando(true);
      try {
        const vistoria = await getVistoria(
          options.contratoId,
          options.vistoriaId as string,
        );
        if (cancelado) return;
        if (!vistoria.podeAlterar) {
          setBannerErro(
            "Esta vistoria não aceita mais alterações (status encerrado).",
          );
        }
        setTipo(vistoria.tipo);
        setData(vistoria.data ? vistoria.data.slice(0, 10) : "");
        setVistoriador(vistoria.vistoriador ?? "");
        setObservacoes(vistoria.observacoes ?? "");
        setItens(vistoria.itens.map((item) => ({ ...item })));
      } catch (erro) {
        if (cancelado) return;
        setBannerErro(getReadableErrorMessage(erro));
      } finally {
        if (!cancelado) setCarregando(false);
      }
    }

    void carregar();
    return () => {
      cancelado = true;
    };
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [options.vistoriaId]);

  const changeItem = useCallback(
    (indice: number, patch: Partial<VistoriaItem>) => {
      setItens((atual) =>
        atual.map((item, posicao) =>
          posicao === indice ? { ...item, ...patch } : item,
        ),
      );
    },
    [],
  );

  const addItem = useCallback(() => {
    setItens((atual) => [
      ...atual,
      { ambiente: "", item: "", estado: "Bom" as VistoriaEstadoItem },
    ]);
  }, []);

  const removeItem = useCallback((indice: number) => {
    setItens((atual) => atual.filter((_, posicao) => posicao !== indice));
  }, []);

  const aplicarChecklistPadrao = useCallback(() => {
    setItens(itensPadrao());
  }, []);

  const limparChecklist = useCallback(() => {
    setItens([]);
  }, []);

  const submit = useCallback(
    (aoFinalizar?: () => void) => {
      if (modo === "editar" && bannerErro) return;

      const validacao: VistoriaFieldError = {};
      if (!tipo) validacao.tipo = "Selecione o tipo da vistoria.";
      if (!data) validacao.data = "Informe a data da vistoria.";
      const itensIncompletos = itens.some(
        (item) => !item.item.trim() || !item.ambiente.trim(),
      );
      if (itensIncompletos) {
        validacao.itens =
          "Todas as linhas do checklist precisam de ambiente e item.";
      }

      if (Object.keys(validacao).length > 0) {
        setErrors(validacao);
        setBannerErro("Verifique os campos destacados antes de continuar.");
        return;
      }
      setErrors({});
      setBannerErro("");
      setSaving(true);

      const itensPayload = itens.map(itemParaPayload);
      const requisicao =
        modo === "editar"
          ? updateVistoria(contratoId, options.vistoriaId as string, {
              tipo,
              data,
              vistoriador: vistoriador.trim() || undefined,
              observacoes: observacoes.trim() || undefined,
              itens: itensPayload,
            } as AlterarVistoriaPayload)
          : createVistoria(contratoId, {
              tipo,
              data,
              vistoriador: vistoriador.trim() || undefined,
              observacoes: observacoes.trim() || undefined,
              itens: itensPayload,
            });

      void requisicao
        .then((vistoria) => {
          pushFeedback(
            "success",
            modo === "editar"
              ? "Vistoria atualizada com sucesso."
              : "Vistoria criada com sucesso.",
          );
          aoFinalizar?.();
          router.push(
            `/contratos/${contratoId}/vistorias/${vistoria.id}/visualizar`,
          );
        })
        .catch((erro) => {
          const errosDeCampo = lerErrosDeCampos(erro);
          if (Object.keys(errosDeCampo).length > 0) {
            setErrors(errosDeCampo);
            setBannerErro(
              "A API rejeitou alguns campos. Revise e tente novamente.",
            );
          } else {
            pushFeedback("error", getReadableErrorMessage(erro));
          }
        })
        .finally(() => {
          setSaving(false);
        });
    },
    [
      modo,
      bannerErro,
      tipo,
      data,
      vistoriador,
      observacoes,
      itens,
      contratoId,
      options.vistoriaId,
      pushFeedback,
      router,
    ],
  );

  const voltar = useCallback(() => {
    if (modo === "editar" && options.vistoriaId) {
      router.push(
        `/contratos/${contratoId}/vistorias/${options.vistoriaId}/visualizar`,
      );
      return;
    }
    router.push(`/contratos/${contratoId}?aba=vistorias`);
  }, [modo, options.vistoriaId, contratoId, router]);

  return {
    modo,
    contratoId,
    formulario: {
      tipo,
      setTipo,
      data,
      setData,
      vistoriador,
      setVistoriador,
      observacoes,
      setObservacoes,
    },
    itens,
    tiposDisponiveis: VISTORIA_TIPOS,
    estadosDisponiveis: VISTORIA_ESTADOS_ITEM,
    errors,
    bannerErro,
    saving,
    carregando,
    changeItem,
    addItem,
    removeItem,
    aplicarChecklistPadrao,
    limparChecklist,
    submit,
    voltar,
  };
}

export type VistoriaRegistrationData = ReturnType<typeof useVistoriaRegistration>;
