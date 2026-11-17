"use client";

import { useCallback, useEffect, useState } from "react";
import { useRouter } from "next/navigation";
import { getReadableErrorMessage } from "@/services/api/get-readable-error-message";
import { useFeedback } from "@/components/ui/feedback-provider";
import { deleteVistoria, listVistorias } from "../services/vistoria-service";
import { downloadVistoriaReport } from "../services/vistoria-report-service";
import {
  VISTORIA_STATUS,
  type Vistoria,
  type VistoriaStatus,
  type VistoriaStatusFilter,
} from "../data/vistoria-types";

const PAGE_SIZE_PADRAO = 8;

export interface UseVistoriasOptions {
  contratoId: string;
  tamanhoPagina?: number;
  contagens?: boolean;
}

interface VistoriaCounts {
  agendadas: number;
  emAndamento: number;
  concluidas: number;
  canceladas: number;
}

export function useVistorias(options: UseVistoriasOptions) {
  const router = useRouter();
  const { pushFeedback } = useFeedback();
  const contratoId = options.contratoId;
  const tamanhoPagina = options.tamanhoPagina ?? PAGE_SIZE_PADRAO;

  const [vistorias, setVistorias] = useState<Vistoria[]>([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState("");
  const [reloadKey, setReloadKey] = useState(0);

  const [status, setStatus] = useState<VistoriaStatusFilter>("todos");
  const [page, setPage] = useState(1);
  const [filteredCount, setFilteredCount] = useState(0);
  const [totalPages, setTotalPages] = useState(1);
  const [counts, setCounts] = useState<VistoriaCounts | null>(null);
  const [removendo, setRemovendo] = useState<string | null>(null);
  const [baixandoRegistro, setBaixandoRegistro] = useState<string | null>(null);

  const changeStatus = useCallback((value: VistoriaStatusFilter) => {
    setStatus(value);
    setPage(1);
  }, []);

  const retry = useCallback(() => {
    setReloadKey((current) => current + 1);
  }, []);

  const reload = useCallback(() => {
    setReloadKey((current) => current + 1);
  }, []);

  const urlNovaVistoria = `/contratos/${contratoId}/vistorias/novo`;

  const urlDetalhe = useCallback(
    (vistoriaId: string) => `/contratos/${contratoId}/vistorias/${vistoriaId}/visualizar`,
    [contratoId],
  );

  const urlEdicao = useCallback(
    (vistoriaId: string) => `/contratos/${contratoId}/vistorias/${vistoriaId}/editar`,
    [contratoId],
  );

  const goToVistoria = useCallback(
    (vistoriaId: string) => {
      router.push(urlDetalhe(vistoriaId));
    },
    [router, urlDetalhe],
  );

  const goToNovaVistoria = useCallback(() => {
    router.push(urlNovaVistoria);
  }, [router, urlNovaVistoria]);

  const goToEdicao = useCallback(
    (vistoriaId: string) => {
      router.push(urlEdicao(vistoriaId));
    },
    [router, urlEdicao],
  );

  useEffect(() => {
    if (!options.contagens || !contratoId) return;
    let cancelado = false;

    async function carregarContagens() {
      try {
        const [agendadas, emAndamento, concluidas, canceladas] =
          await Promise.all(
            VISTORIA_STATUS.map((situacao) =>
              listVistorias({ contratoId, status: situacao, tamanhoPagina: 1 }),
            ),
          );
        if (cancelado) return;
        setCounts({
          agendadas: agendadas.total,
          emAndamento: emAndamento.total,
          concluidas: concluidas.total,
          canceladas: canceladas.total,
        });
      } catch {
        if (!cancelado) setCounts(null);
      }
    }

    void carregarContagens();
    return () => {
      cancelado = true;
    };
  }, [contratoId, options.contagens, reloadKey]);

  useEffect(() => {
    if (!contratoId) {
      setLoading(false);
      return;
    }
    let cancelado = false;

    async function carregar() {
      setLoading(true);
      setError("");
      try {
        const resultado = await listVistorias({
          contratoId,
          status: status === "todos" ? undefined : (status as VistoriaStatus),
          pagina: page,
          tamanhoPagina,
        });
        if (cancelado) return;
        setVistorias(resultado.itens);
        setFilteredCount(resultado.total);
        setTotalPages(resultado.totalPaginas);
        if (resultado.pagina && resultado.pagina !== page) {
          setPage(resultado.pagina);
        }
      } catch (erro) {
        if (cancelado) return;
        setVistorias([]);
        setFilteredCount(0);
        setTotalPages(1);
        setError(getReadableErrorMessage(erro));
      } finally {
        if (!cancelado) setLoading(false);
      }
    }

    void carregar();
    return () => {
      cancelado = true;
    };
  }, [contratoId, status, page, tamanhoPagina, reloadKey]);

  const remover = useCallback(
    (vistoriaId: string, identificador: string, podeExcluir = true) => {
      if (!podeExcluir || removendo) return;
      const confirmado = window.confirm(
        `Excluir a vistoria ${identificador}? Esta ação não pode ser desfeita.`,
      );
      if (!confirmado) return;

      setRemovendo(vistoriaId);
      void deleteVistoria(contratoId, vistoriaId)
        .then(() => {
          pushFeedback("success", "Vistoria excluída com sucesso.");
          reload();
        })
        .catch((erro) => {
          pushFeedback("error", getReadableErrorMessage(erro));
        })
        .finally(() => {
          setRemovendo(null);
        });
    },
    [contratoId, removendo, pushFeedback, reload],
  );

  return {
    contratoId,
    vistorias,
    loading,
    error,
    retry,
    reload,
    status,
    page,
    pageSize: tamanhoPagina,
    filteredCount,
    totalPages,
    counts,
    removendo,
    baixandoRegistro,
    baixarRegistro: (vistoriaId: string) => {
      if (baixandoRegistro) return;
      setBaixandoRegistro(vistoriaId);
      void downloadVistoriaReport(contratoId, vistoriaId)
        .catch((erro) => pushFeedback("error", getReadableErrorMessage(erro)))
        .finally(() => setBaixandoRegistro(null));
    },
    changeStatus,
    goToPage: setPage,
    urlNovaVistoria,
    urlVistoria: urlDetalhe,
    urlEdicao,
    goToVistoria,
    goToNovaVistoria,
    goToEdicao,
    remover,
  };
}

export type VistoriasListData = ReturnType<typeof useVistorias>;
