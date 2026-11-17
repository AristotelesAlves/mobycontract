"use client";

import { useCallback, useEffect, useState } from "react";
import { usePathname, useRouter, useSearchParams } from "next/navigation";
import { getReadableErrorMessage } from "@/services/api/get-readable-error-message";
import { useFeedback } from "@/components/ui/feedback-provider";
import { downloadDocumentPdf } from "@/lib/download-document-pdf";
import { listModelTemplates } from "@/features/modelos/services/modelos-service";
import { gerarHtmlContrato } from "../use-cases/generate-contract-html";
import { paymentReceiptHtml } from "../use-cases/payment-receipt";
import {
  addInstallmentCharge,
  changeContractStatus,
  confirmContractPhysicalDocument,
  downloadContractDocument,
  getContract,
  listContractAddenda,
  listContractDebts,
  listContractInstallments,
  listContractStages,
  quitContractDebt,
  registerContractOnBlockchain,
  registerInstallmentPayment,
  signContract,
} from "../services/contract-service";
import type {
  Contrato,
  ContratoStatus,
  ContratoAditivo,
} from "../data/contrato-types";
import type { ParcelaFinanceira } from "../data/contrato-types";
import type { ContratoDivida } from "../services/contract-service";
import type {
  EtapaResourceDTO,
  LancarEncargoPayload,
  RegistrarPagamentoPayload,
} from "../services/contract-service";

export const CONTRACT_DETAIL_TABS = [
  "geral",
  "vistorias",
  "financeiro",
  "aditivos",
] as const;

export type ContractDetailTab = (typeof CONTRACT_DETAIL_TABS)[number];

const PAGE_QUERY_KEYS = ["vistoriasPage", "financeiroPage", "aditivosPage"];

function isContractDetailTab(value: string | null): value is ContractDetailTab {
  return CONTRACT_DETAIL_TABS.includes(value as ContractDetailTab);
}

export function useContractDetail(contractId: string) {
  const router = useRouter();
  const pathname = usePathname();
  const searchParams = useSearchParams();
  const { pushFeedback } = useFeedback();

  const [contract, setContract] = useState<Contrato | null>(null);
  const [parcelas, setParcelas] = useState<ParcelaFinanceira[]>([]);
  const [dividas, setDividas] = useState<ContratoDivida[]>([]);
  const [aditivos, setAditivos] = useState<ContratoAditivo[]>([]);
  const [etapas, setEtapas] = useState<EtapaResourceDTO[]>([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState("");
  const [reloadKey, setReloadKey] = useState(0);
  const [working, setWorking] = useState("");

  const [tab, setTab] = useState<ContractDetailTab>(() => {
    const requested = searchParams.get("aba");
    return isContractDetailTab(requested) ? requested : "geral";
  });
  const [isBlockchainModalOpen, setBlockchainModalOpen] = useState(false);
  const [uploadFile, setUploadFile] = useState<File | null>(null);
  const [isStepperVisible, setStepperVisible] = useState(false);

  const retry = useCallback(() => {
    setReloadKey((current) => current + 1);
  }, []);

  useEffect(() => {
    let cancelado = false;

    async function carregar() {
      setLoading(true);
      setError("");
      try {
        const contratoCarregado = await getContract(contractId);
        if (cancelado) return;
        setContract(contratoCarregado);

        const [parcelasCarregadas, dividasCarregadas, aditivosCarregados, etapasCarregadas] =
          await Promise.all([
            listContractInstallments(contractId).catch(
              () => [] as ParcelaFinanceira[],
            ),
            listContractDebts(contractId).catch(() => [] as ContratoDivida[]),
            listContractAddenda(contractId).catch(
              () => [] as ContratoAditivo[],
            ),
            listContractStages(contractId).catch(
              () => [] as EtapaResourceDTO[],
            ),
          ]);
        if (cancelado) return;
        setParcelas(parcelasCarregadas);
        setDividas(dividasCarregadas);
        setAditivos(aditivosCarregados);
        setEtapas(etapasCarregadas);
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
  }, [contractId, reloadKey]);

  const changeTab = useCallback(
    (value: string) => {
      const params = new URLSearchParams(searchParams.toString());

      PAGE_QUERY_KEYS.forEach((key) => {
        if (key !== `${value}Page`) params.delete(key);
      });

      params.set("aba", value);
      setTab(value as ContractDetailTab);
      router.replace(`${pathname}?${params.toString()}`, { scroll: false });
    },
    [pathname, router, searchParams],
  );

  const comTrabalho = useCallback(
    async (rotulo: string, acao: () => Promise<void>) => {
      if (working) return false;
      setWorking(rotulo);
      try {
        await acao();
        return true;
      } catch (erro) {
        pushFeedback("error", getReadableErrorMessage(erro));
        return false;
      } finally {
        setWorking("");
      }
    },
    [working, pushFeedback],
  );

  const aplicar = useCallback((atualizado: Contrato) => {
    setContract(atualizado);
  }, []);

  const registerSignature = useCallback(
    (assinaturaId: string) => {
      void comTrabalho(`assinatura-${assinaturaId}`, async () => {
        const atualizado = await signContract(contractId, assinaturaId);
        aplicar(atualizado);
        pushFeedback("success", "Assinatura registrada com sucesso.");
      });
    },
    [comTrabalho, contractId, aplicar, pushFeedback],
  );

  const changeStatus = useCallback(
    (status: ContratoStatus, motivo?: string) => {
      void comTrabalho(`status-${status}`, async () => {
        const atualizado = await changeContractStatus(
          contractId,
          status,
          motivo?.trim() || undefined,
        );
        aplicar(atualizado);
        pushFeedback("success", `Contrato movido para ${atualizado.status}.`);
      });
    },
    [comTrabalho, contractId, aplicar, pushFeedback],
  );

  const registrarNaBlockchain = useCallback(() => {
    void comTrabalho("blockchain", async () => {
      const atualizado = await registerContractOnBlockchain(contractId);
      aplicar(atualizado);
      setBlockchainModalOpen(false);
      pushFeedback(
        "success",
        "Contrato registrado na blockchain com sucesso.",
      );
    });
  }, [comTrabalho, contractId, aplicar, pushFeedback]);

  const confirmDocumentoFisico = useCallback(() => {
    void comTrabalho("documento", async () => {
      const atualizado = await confirmContractPhysicalDocument(
        contractId,
        uploadFile,
      );
      aplicar(atualizado);
      setUploadFile(null);
      pushFeedback(
        "success",
        "Documento físico confirmado com sucesso.",
      );
    });
  }, [comTrabalho, contractId, uploadFile, aplicar, pushFeedback]);

  const recarregarParcelas = useCallback(() => {
    return listContractInstallments(contractId)
      .then((lista) => setParcelas(lista))
      .catch(() => pushFeedback("error", "Não foi possível atualizar as parcelas. Atualize a página para consultar os lançamentos salvos."));
  }, [contractId, pushFeedback]);

  const registrarPagamento = useCallback(
    (parcelaId: string, dados: RegistrarPagamentoPayload) => {
      return comTrabalho(`pagamento-${parcelaId}`, async () => {
        await registerInstallmentPayment(
          contractId,
          parcelaId,
          dados,
        );
        await getContract(contractId).then(aplicar).catch(() => pushFeedback("error", "Pagamento salvo, mas não foi possível atualizar o histórico. Atualize a página."));
        await recarregarParcelas();
        pushFeedback("success", dados.corrigir ? "Pagamento corrigido com sucesso." : "Pagamento registrado com sucesso.");
      });
    },
    [comTrabalho, contractId, aplicar, recarregarParcelas, pushFeedback],
  );

  const lancarEncargo = useCallback(
    (parcelaId: string, dados: LancarEncargoPayload) => {
      void comTrabalho(`encargo-${parcelaId}`, async () => {
        await addInstallmentCharge(
          contractId,
          parcelaId,
          dados,
        );
        await recarregarParcelas();
        pushFeedback("success", "Encargo lançado com sucesso.");
      });
    },
    [comTrabalho, contractId, recarregarParcelas, pushFeedback],
  );

  const recarregarDividas = useCallback(() => {
    void listContractDebts(contractId)
      .then((lista) => setDividas(lista))
      .catch(() => undefined);
  }, [contractId]);

  const quitarDivida = useCallback(
    (dividaId: string) => {
      void comTrabalho(`divida-${dividaId}`, async () => {
        await quitContractDebt(contractId, dividaId);
        recarregarDividas();
        pushFeedback("success", "Dívida quitada com sucesso.");
      });
    },
    [comTrabalho, contractId, recarregarDividas, pushFeedback],
  );

  const recarregarAditivos = useCallback(() => {
    void listContractAddenda(contractId)
      .then((lista) => setAditivos(lista))
      .catch(() => undefined);
  }, [contractId]);

  const isBlockchainRegistered =
    Boolean(contract?.hashBlockchain) ||
    contract?.statusBlockchain === "Registrado";

  return {
    contract,
    parcelas,
    dividas,
    aditivos,
    etapas,
    loading,
    error,
    retry,
    working,
    tab,
    changeTab,
    isBlockchainRegistered,
    isBlockchainModalOpen,
    openBlockchainModal: () => setBlockchainModalOpen(true),
    closeBlockchainModal: () => setBlockchainModalOpen(false),
    registrarNaBlockchain,
    registerSignature,
    changeStatus,
    uploadFile,
    setUploadFile,
    confirmDocumentoFisico,
    registrandoDocumento: working === "documento",
    isStepperVisible,
    toggleStepper: () => setStepperVisible((current) => !current),
    goToContracts: () => router.push("/contratos"),
    baixandoDocumento: working === "download-documento",
    downloadContract: () => {
      if (!contract) return;
      void comTrabalho("download-documento", async () => {
        if (contract.anexoContratoNome) {
          await downloadContractDocument(contract.id);
          return;
        }
        const modelo = contract.snapshot ? undefined : (await listModelTemplates())
          .find((item) => item.uuid === contract.modeloId);
        const html = gerarHtmlContrato(contract, modelo);
        if (!html) throw new Error("O modelo não está disponível para gerar a minuta deste contrato.");
        await downloadDocumentPdf(html, contract.id, `contrato-${contract.numero}`);
      });
    },
    registrarPagamento,
    lancarEncargo,
    baixandoRecibo: working.startsWith("recibo-") ? working.slice(7) : "",
    baixarRecibo: (parcelaId: string) => {
      void comTrabalho(`recibo-${parcelaId}`, async () => {
        const [atualizado, parcelasAtualizadas] = await Promise.all([getContract(contractId), listContractInstallments(contractId)]);
        const parcela = parcelasAtualizadas.find((item) => item.id === parcelaId);
        if (!parcela) throw new Error("Parcela não encontrada para emitir o recibo.");
        const html = paymentReceiptHtml(atualizado, parcela);
        await downloadDocumentPdf(html, contractId, `recibo-${atualizado.numero}-${parcela.referencia ?? parcela.id}`);
      });
    },
    quitarDivida,
    recarregarAditivos,
    recarregarParcelas,
  };
}
