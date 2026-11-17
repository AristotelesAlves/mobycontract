"use client";

import { useCallback, useEffect, useState } from "react";
import { useRouter } from "next/navigation";
import { getReadableErrorMessage } from "@/services/api/get-readable-error-message";
import { useFeedback } from "@/components/ui/feedback-provider";
import {
  addVistoriaPhoto,
  changeVistoriaStatus,
  deleteVistoria,
  getVistoria,
  removeVistoriaPhoto,
  type FotoVistoriaPayload,
} from "../services/vistoria-service";
import type { Vistoria } from "../data/vistoria-types";
import { downloadVistoriaReport } from "../services/vistoria-report-service";

const MOTIVO_MINIMO = 10;

export function useVistoriaDetail(contratoId: string, vistoriaId: string) {
  const router = useRouter();
  const { pushFeedback } = useFeedback();

  const [vistoria, setVistoria] = useState<Vistoria | null>(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState("");
  const [reloadKey, setReloadKey] = useState(0);
  const [working, setWorking] = useState("");

  const retry = useCallback(() => {
    setReloadKey((current) => current + 1);
  }, []);

  useEffect(() => {
    let cancelado = false;

    async function carregar() {
      setLoading(true);
      setError("");
      try {
        const carregada = await getVistoria(contratoId, vistoriaId);
        if (cancelado) return;
        setVistoria(carregada);
      } catch (erro) {
        if (cancelado) return;
        setError(getReadableErrorMessage(erro));
      } finally {
        if (!cancelado) setLoading(false);
      }
    }

    void carregar();
    return () => {
      cancelado = true;
    };
  }, [contratoId, vistoriaId, reloadKey]);

  const aplicar = useCallback((atualizada: Vistoria) => {
    setVistoria(atualizada);
  }, []);

  const changeStatus = useCallback(
    (novoStatus: Vistoria["status"]) => {
      if (working) return;

      const cancelando = novoStatus === "Cancelada";
      const informado = cancelando
        ? (window.prompt(
          "Informe o motivo do cancelamento (mínimo 10 caracteres):",
        ) ?? "").trim()
        : undefined;

      if (cancelando && (informado?.length ?? 0) < MOTIVO_MINIMO) {
        pushFeedback(
          "error",
          "O motivo do cancelamento precisa ter pelo menos 10 caracteres.",
        );
        return;
      }

      const motivo = informado;
      setWorking(`status-${novoStatus}`);
      void changeVistoriaStatus(contratoId, vistoriaId, novoStatus, motivo)
        .then((atualizada) => {
          aplicar(atualizada);
          pushFeedback("success", `Vistoria movida para ${atualizada.status}.`);
        })
        .catch((erro) => {
          pushFeedback("error", getReadableErrorMessage(erro));
        })
        .finally(() => {
          setWorking("");
        });
    },
    [working, contratoId, vistoriaId, aplicar, pushFeedback],
  );

  const remover = useCallback(() => {
    if (!vistoria || working) return;
    const identificador = vistoria.numero || `#${vistoria.id}`;
    if (
      !window.confirm(
        `Excluir a vistoria ${identificador}? Esta ação não pode ser desfeita.`,
      )
    ) {
      return;
    }

    setWorking("remover");
    void deleteVistoria(contratoId, vistoriaId)
      .then(() => {
        pushFeedback("success", "Vistoria excluída com sucesso.");
        router.push(`/contratos/${contratoId}?aba=vistorias`);
      })
      .catch((erro) => {
        pushFeedback("error", getReadableErrorMessage(erro));
        setWorking("");
      });
  }, [vistoria, working, contratoId, vistoriaId, router, pushFeedback]);

  const anexarFoto = useCallback(
    (payload: FotoVistoriaPayload, aoFinalizar?: () => void) => {
      if (working) return;
      setWorking("foto-adicionar");
      void addVistoriaPhoto(contratoId, vistoriaId, payload)
        .then(() => {
          pushFeedback("success", "Foto registrada na vistoria.");
          aoFinalizar?.();
          setReloadKey((current) => current + 1);
        })
        .catch((erro) => {
          pushFeedback("error", getReadableErrorMessage(erro));
        })
        .finally(() => {
          setWorking("");
        });
    },
    [working, contratoId, vistoriaId, pushFeedback],
  );

  const excluirFoto = useCallback(
    (fotoId: string, nome: string) => {
      if (working) return;
      if (!window.confirm(`Remover a foto "${nome}"?`)) return;

      setWorking(`foto-${fotoId}`);
      void removeVistoriaPhoto(contratoId, vistoriaId, fotoId)
        .then(() => {
          pushFeedback("success", "Foto removida da vistoria.");
          setReloadKey((current) => current + 1);
        })
        .catch((erro) => {
          pushFeedback("error", getReadableErrorMessage(erro));
        })
        .finally(() => {
          setWorking("");
        });
    },
    [working, contratoId, vistoriaId, pushFeedback],
  );

  const copiarChaveAcesso = useCallback(() => {
    if (!vistoria?.chaveAcesso) return;
    void navigator.clipboard
      .writeText(vistoria.chaveAcesso)
      .then(() => {
        pushFeedback("success", "Chave de acesso copiada.");
      })
      .catch(() => {
        pushFeedback("error", "Não foi possível copiar a chave de acesso.");
      });
  }, [vistoria, pushFeedback]);

  return {
    vistoria,
    loading,
    error,
    retry,
    working,
    changeStatus,
    remover,
    anexarFoto,
    excluirFoto,
    baixarRelatorio: () => {
      if (working) return;
      setWorking("pdf");
      void downloadVistoriaReport(contratoId, vistoriaId)
        .catch((erro) => pushFeedback("error", getReadableErrorMessage(erro)))
        .finally(() => setWorking(""));
    },
    copiarChaveAcesso,
    goToContrato: () => router.push(`/contratos/${contratoId}?aba=vistorias`),
    irParaEdicao: () =>
      router.push(`/contratos/${contratoId}/vistorias/${vistoriaId}/editar`),
  };
}
