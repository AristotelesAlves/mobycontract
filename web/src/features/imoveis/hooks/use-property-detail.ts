"use client";

import { useCallback, useEffect, useState } from "react";
import { useRouter } from "next/navigation";
import { getReadableErrorMessage } from "@/services/api/get-readable-error-message";
import { useFeedback } from "@/components/ui/feedback-provider";
import { listContracts } from "@/features/contratos/services/contract-service";
import type { Contrato } from "@/features/contratos/data/contrato-types";
import type {
  AgendamentoVisita,
  AnotacaoImovel,
  Imovel,
  InteressadoImovel,
  InteressadoStatus,
  InteressadoTipo,
  ManutencaoImovel,
  ManutencaoStatus,
} from "../data/imovel-types";
import { getProperty } from "../services/property-service";
import {
  changePropertyProspectStatus,
  changePropertyVisitStatus,
  createPropertyMaintenance,
  createPropertyNote,
  createPropertyProspect,
  createPropertyVisit,
  deletePropertyNote,
  listPropertyMaintenances,
  listPropertyNotes,
  listPropertyProspects,
  listPropertyVisits,
  updatePropertyMaintenance,
} from "../services/property-service";

export function useImovelDetalhe(idInicial: string) {
  const router = useRouter();
  const { pushFeedback } = useFeedback();

  const [imovel, setImovel] = useState<Imovel | null>(null);
  const [contratosDoImovel, setContratosDoImovel] = useState<Contrato[]>([]);
  const [anotacoes, setAnotacoes] = useState<AnotacaoImovel[]>([]);
  const [agendamentos, setAgendamentos] = useState<AgendamentoVisita[]>([]);
  const [interessados, setInteressados] = useState<InteressadoImovel[]>([]);
  const [manutencoes, setManutencoes] = useState<ManutencaoImovel[]>([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState("");
  const [reloadKey, setReloadKey] = useState(0);

  const [abrirAnotacao, setAbrirAnotacao] = useState(false);
  const [abrirAgendamento, setAbrirAgendamento] = useState(false);

  const [paginaContratos, setPaginaContratos] = useState(1);
  const [buscaContratos, setBuscaContratos] = useState("");
  const [filtroContratos, setFiltroContratos] = useState<string>("todos");


  const [paginaFinanceiro, setPaginaFinanceiro] = useState(1);
  const [buscaFinanceiro, setBuscaFinanceiro] = useState("");
  const [filtroFinanceiro, setFiltroFinanceiro] = useState<string>("todos");

  const retry = useCallback(() => {
    setReloadKey((current) => current + 1);
  }, []);

  useEffect(() => {
    let cancelado = false;

    async function carregar() {
      setLoading(true);
      setError("");
      try {
        const imovelCarregado = await getProperty(idInicial);
        if (cancelado) return;
        setImovel(imovelCarregado);

        const [contratos, anotacoesCarregadas, agendamentosCarregados, interessadosCarregados, manutencoesCarregadas] =
          await Promise.all([
            listContracts({ imovelId: Number(idInicial), tamanhoPagina: 50 }),
            listPropertyNotes(idInicial).catch(() => [] as AnotacaoImovel[]),
            listPropertyVisits(idInicial).catch(() => [] as AgendamentoVisita[]),
            listPropertyProspects(idInicial).catch(() => [] as InteressadoImovel[]),
            listPropertyMaintenances(idInicial).catch(
              () => [] as ManutencaoImovel[],
            ),
          ]);
        if (cancelado) return;
        setContratosDoImovel(contratos.itens);
        setAnotacoes(anotacoesCarregadas);
        setAgendamentos(agendamentosCarregados);
        setInteressados(interessadosCarregados);
        setManutencoes(manutencoesCarregadas);
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
  }, [idInicial, reloadKey]);

  const contratoAtivo = contratosDoImovel.find(
    (contrato) => contrato.status === "Vigente" || contrato.status === "Validado",
  );

  const statusCalculado = imovel?.status ?? "Disponível";
  const statusColor =
    statusCalculado === "Alugado"
      ? "bg-emerald-500 hover:bg-emerald-600 text-white"
      : statusCalculado === "Vendido"
        ? "bg-slate-500 hover:bg-slate-600 text-white"
        : "bg-orange-500 hover:bg-orange-600 text-white";

  const addAnotacao = useCallback(
    async (texto: string) => {
      try {
        const nova = await createPropertyNote(idInicial, texto);
        setAnotacoes((current) => [nova, ...current]);
        pushFeedback("success", "Anotação registrada com sucesso.");
      } catch (erro) {
        pushFeedback("error", getReadableErrorMessage(erro));
      }
    },
    [idInicial, pushFeedback],
  );

  const removeAnotacao = useCallback(
    async (anotacaoId: string) => {
      try {
        await deletePropertyNote(idInicial, anotacaoId);
        setAnotacoes((current) =>
          current.filter((anotacao) => anotacao.id !== anotacaoId),
        );
        pushFeedback("success", "Anotação excluída.");
      } catch (erro) {
        pushFeedback("error", getReadableErrorMessage(erro));
      }
    },
    [idInicial, pushFeedback],
  );

  const addAgendamento = useCallback(
    async (dados: {
      data: string;
      hora: string;
      interessado: string;
      telefone?: string;
    }) => {
      try {
        const nova = await createPropertyVisit(idInicial, dados);
        setAgendamentos((current) => [nova, ...current]);
        pushFeedback(
          "success",
          `Visita agendada para ${dados.interessado} em ${dados.data}.`,
        );
      } catch (erro) {
        pushFeedback("error", getReadableErrorMessage(erro));
      }
    },
    [idInicial, pushFeedback],
  );

  const mudarStatusAgendamento = useCallback(
    async (
      agendamentoId: string,
      status: AgendamentoVisita["status"],
    ) => {
      try {
        const atualizado = await changePropertyVisitStatus(
          idInicial,
          agendamentoId,
          status,
        );
        setAgendamentos((current) =>
          current.map((item) => (item.id === atualizado.id ? atualizado : item)),
        );
        pushFeedback("success", `Visita marcada como ${status}.`);
      } catch (erro) {
        pushFeedback("error", getReadableErrorMessage(erro));
      }
    },
    [idInicial, pushFeedback],
  );

  const addInteressado = useCallback(
    async (dados: {
      nome: string;
      telefone: string;
      email?: string;
      tipo: InteressadoTipo;
    }) => {
      try {
        const novo = await createPropertyProspect(idInicial, dados);
        setInteressados((current) => [novo, ...current]);
        pushFeedback("success", `Interessado ${dados.nome} registrado.`);
      } catch (erro) {
        pushFeedback("error", getReadableErrorMessage(erro));
      }
    },
    [idInicial, pushFeedback],
  );

  const mudarStatusInteressado = useCallback(
    async (interessadoId: string, status: InteressadoStatus) => {
      try {
        const atualizado = await changePropertyProspectStatus(
          idInicial,
          interessadoId,
          status,
        );
        setInteressados((current) =>
          current.map((item) => (item.id === atualizado.id ? atualizado : item)),
        );
        pushFeedback("success", `Interessado marcado como ${status}.`);
      } catch (erro) {
        pushFeedback("error", getReadableErrorMessage(erro));
      }
    },
    [idInicial, pushFeedback],
  );

  const addManutencao = useCallback(
    async (dados: {
      titulo: string;
      descricao?: string;
      responsavel?: string;
      custo?: number;
    }) => {
      try {
        const nova = await createPropertyMaintenance(idInicial, dados);
        setManutencoes((current) => [nova, ...current]);
        pushFeedback("success", `Manutenção "${dados.titulo}" registrada.`);
      } catch (erro) {
        pushFeedback("error", getReadableErrorMessage(erro));
      }
    },
    [idInicial, pushFeedback],
  );

  const mudarStatusManutencao = useCallback(
    async (manutencaoId: string, status: ManutencaoStatus) => {
      try {
        const atualizada = await updatePropertyMaintenance(
          idInicial,
          manutencaoId,
          { status },
        );
        setManutencoes((current) =>
          current.map((item) => (item.id === atualizada.id ? atualizada : item)),
        );
        pushFeedback("success", `Manutenção marcada como ${status}.`);
      } catch (erro) {
        pushFeedback("error", getReadableErrorMessage(erro));
      }
    },
    [idInicial, pushFeedback],
  );

  return {
    router,
    imovel,
    loading,
    error,
    retry,
    contratosDoImovel,
    contratoAtivo,
    anotacoes,
    agendamentos,
    interessados,
    manutencoes,
    statusCalculado,
    statusColor,
    abrirAnotacao,
    setAbrirAnotacao,
    abrirAgendamento,
    setAbrirAgendamento,
    addAnotacao,
    removeAnotacao,
    addAgendamento,
    mudarStatusAgendamento,
    addInteressado,
    mudarStatusInteressado,
    addManutencao,
    mudarStatusManutencao,
    paginaContratos, setPaginaContratos,
    buscaContratos, setBuscaContratos,
    filtroContratos, setFiltroContratos,
    paginaFinanceiro, setPaginaFinanceiro,
    buscaFinanceiro, setBuscaFinanceiro,
    filtroFinanceiro, setFiltroFinanceiro,
  };
}
