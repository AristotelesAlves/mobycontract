"use client";

import { useCallback, useEffect, useMemo, useState } from "react";
import { useRouter } from "next/navigation";
import { getReadableErrorMessage } from "@/services/api/get-readable-error-message";
import { useFeedback } from "@/components/ui/feedback-provider";
import { listContracts } from "@/features/contratos/services/contract-service";
import { deleteVistoria, listVistorias } from "@/features/vistorias/services/vistoria-service";
import {
  VISTORIA_STATUS,
  type Vistoria,
  type VistoriaStatus,
} from "@/features/vistorias/data/vistoria-types";

const LIMITE_CONTRATOS = 20;
const LIMITE_VISTORIAS_CONTRATO = 50;

export interface VistoriaDoImovel extends Vistoria {
  contratoNumeroResolvido: string;
}

interface RowContrato {
  id: string;
  numero: string;
  status: string;
}

export function useImovelVistorias(imovelId: string) {
  const router = useRouter();
  const { pushFeedback } = useFeedback();

  const [vistorias, setVistorias] = useState<VistoriaDoImovel[]>([]);
  const [contratos, setContratos] = useState<RowContrato[]>([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState("");
  const [reloadKey, setReloadKey] = useState(0);
  const [status, setStatus] = useState<"todas" | VistoriaStatus>("todas");
  const [removendo, setRemovendo] = useState<string | null>(null);

  const retry = useCallback(() => {
    setReloadKey((current) => current + 1);
  }, []);

  useEffect(() => {
    let cancelado = false;

    async function carregar() {
      setLoading(true);
      setError("");
      try {
        const listaContratos = await listContracts({
          imovelId: Number(imovelId),
          tamanhoPagina: LIMITE_CONTRATOS,
          ordenarPor: "created_at",
          direcao: "desc",
        });
        if (cancelado) return;

        const resumo = listaContratos.itens.map((contrato) => ({
          id: contrato.id,
          numero: contrato.numero,
          status: contrato.status,
        }));
        setContratos(resumo);

        const paginas = await Promise.all(
          resumo.map((contrato) =>
            listVistorias({
              contratoId: contrato.id,
              tamanhoPagina: LIMITE_VISTORIAS_CONTRATO,
            }),
          ),
        );
        if (cancelado) return;

        const agregadas: VistoriaDoImovel[] = [];
        resumo.forEach((contrato, indice) => {
          for (const vistoria of paginas[indice].itens) {
            agregadas.push({
              ...vistoria,
              contratoNumeroResolvido: contrato.numero,
            });
          }
        });

        agregadas.sort((a, b) => (a.data < b.data ? 1 : a.data > b.data ? -1 : 0));
        setVistorias(agregadas);
      } catch (erro) {
        if (cancelado) return;
        setVistorias([]);
        setContratos([]);
        setError(getReadableErrorMessage(erro));
      } finally {
        if (!cancelado) setLoading(false);
      }
    }

    void carregar();
    return () => {
      cancelado = true;
    };
  }, [imovelId, reloadKey]);

  const visiveis = useMemo(
    () =>
      status === "todas"
        ? vistorias
        : vistorias.filter((vistoria) => vistoria.status === status),
    [vistorias, status],
  );

  const [contratoExpandido, setContratoExpandido] = useState<string | null>(null);

  const irParaVistorias = useCallback(
    (contratoId: string) => {
      router.push(`/contratos/${contratoId}?aba=vistorias`);
    },
    [router],
  );

  const irParaNova = useCallback(
    (contratoId: string) => {
      router.push(`/contratos/${contratoId}/vistorias/novo`);
    },
    [router],
  );

  const abrirVistoria = useCallback(
    (contratoId: string, vistoriaId: string) => {
      router.push(`/contratos/${contratoId}/vistorias/${vistoriaId}/visualizar`);
    },
    [router],
  );

  const abrirEdicao = useCallback(
    (contratoId: string, vistoriaId: string) => {
      router.push(`/contratos/${contratoId}/vistorias/${vistoriaId}/editar`);
    },
    [router],
  );

  const remover = useCallback(
    (contratoId: string, vistoriaId: string, numero: string, podeExcluir: boolean) => {
      if (!podeExcluir || removendo) return;
      if (
        !window.confirm(
          `Excluir a vistoria ${numero}? Esta ação não pode ser desfeita.`,
        )
      ) {
        return;
      }

      setRemovendo(vistoriaId);
      void deleteVistoria(contratoId, vistoriaId)
        .then(() => {
          pushFeedback("success", "Vistoria excluída com sucesso.");
          setReloadKey((current) => current + 1);
        })
        .catch((erro) => {
          pushFeedback("error", getReadableErrorMessage(erro));
        })
        .finally(() => {
          setRemovendo(null);
        });
    },
    [removendo, pushFeedback],
  );

  return {
    vistorias: visiveis,
    totalVistorias: vistorias.length,
    contratos,
    status,
    changeStatus: setStatus,
    loading,
    error,
    retry,
    removendo,
    contratoExpandido,
    setContratoExpandido,
    statusDisponiveis: VISTORIA_STATUS,
    irParaVistorias,
    irParaNova,
    abrirVistoria,
    abrirEdicao,
    remover,
  };
}
