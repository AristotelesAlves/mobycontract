"use client";

import { Suspense } from "react";
import {
  ArrowLeft,
  FilePdf,
  FilePlus,
  FileText,
  ListChecks,
  Money,
  WarningCircle,
} from "@phosphor-icons/react";
import { Button } from "@/components/ui/button";
import {
  Card,
  CardContent,
} from "@/components/ui/card";
import { Skeleton } from "@/components/ui/skeleton";
import { Tabs, TabsContent } from "@/components/ui/tabs";
import { PageTabsList, PageTabsTrigger } from "@/components/ui/page-tabs";
import { useContractDetail } from "../../hooks/use-contract-detail";
import { ModalConfirmarBlockchain } from "../../components/modal-confirmar-blockchain";
import { ContratoStepper } from "./contrato-stepper";
import { ContratoGeralTab } from "./contrato-geral-tab";
import { ContratoVistoriasTab } from "./contrato-vistorias-tab";
import { ContratoFinanceiroTab } from "./contrato-financeiro-tab";
import { ContratoAditivosTab } from "./contrato-aditivos-tab";

export function ContratoDetalhe({ idInicial }: { idInicial: string }) {
  return (
    <Suspense fallback={null}>
      <ContratoDetalheConteudo idInicial={idInicial} />
    </Suspense>
  );
}

export function ContratoDetalheConteudo({ idInicial }: { idInicial: string }) {
  const detail = useContractDetail(idInicial);
  const contract = detail.contract;

  if (!contract) {
    return (
      <div className="flex flex-col gap-4">
        <Button variant="outline" onClick={detail.goToContracts}>
          <ArrowLeft size={16} />
          Voltar
        </Button>
        <Card>
          <CardContent className="py-10 text-center space-y-3">
            {detail.error ? (
              <>
                <WarningCircle size={28} className="text-destructive mx-auto" />
                <p className="text-sm text-destructive">{detail.error}</p>
                <Button
                  variant="outline"
                  size="sm"
                  className="gap-2"
                  onClick={detail.retry}
                >
                  Tentar novamente
                </Button>
              </>
            ) : (
              <div className="space-y-4">
                <Skeleton className="h-8 w-1/3 mx-auto" />
                <Skeleton className="h-6 w-2/3 mx-auto" />
                <Skeleton className="h-40 w-full" />
              </div>
            )}
          </CardContent>
        </Card>
      </div>
    );
  }

  return (
    <div className="flex flex-col gap-4">
      {(!detail.isBlockchainRegistered || detail.isStepperVisible) && (
        <ContratoStepper
          contrato={contract}
          arquivoUpload={detail.uploadFile}
          setArquivoUpload={detail.setUploadFile}
          confirmarDocumentoFisico={detail.confirmDocumentoFisico}
          registrandoDocumento={detail.registrandoDocumento}
          setAbrirBlockchain={detail.openBlockchainModal}
          registrarAssinatura={detail.registerSignature}
          alterarStatus={detail.changeStatus}
        />
      )}

      <Tabs value={detail.tab} onValueChange={detail.changeTab}>
        <PageTabsList
          actions={
            <>
              {detail.isBlockchainRegistered && (
                <Button
                  variant="outline"
                  size="sm"
                  onClick={detail.toggleStepper}
                  className="rounded-full h-8.5 px-3.5 text-xs font-medium border-border/60 bg-muted/20 hover:bg-muted/50 text-muted-foreground hover:text-foreground transition-all shadow-xs gap-1.5 cursor-pointer"
                  title={detail.isStepperVisible ? "Ocultar etapas do contrato" : "Visualizar etapas do contrato"}
                >
                  <span className="size-1.5 rounded-full bg-emerald-500 animate-pulse" />
                  <ListChecks size={15} className="text-emerald-500" />
                  {detail.isStepperVisible ? "Ocultar Etapas" : "Ver Etapas"}
                </Button>
              )}
              <Button
                variant="outline"
                size="sm"
                onClick={detail.downloadContract}
                disabled={Boolean(detail.working)}
                className="rounded-full h-8.5 px-4 text-xs font-medium border-border/60 bg-muted/20 hover:bg-muted text-foreground transition-all shadow-xs gap-1.5 cursor-pointer"
              >
                <FilePdf size={15} className="text-red-400" />
                {detail.baixandoDocumento ? "Baixando..." : "Baixar contrato"}
              </Button>
            </>
          }
        >
          <PageTabsTrigger value="geral">
            <FileText size={15} />
            <span>Geral</span>
          </PageTabsTrigger>
          <PageTabsTrigger value="vistorias">
            <FileText size={15} />
            <span>Vistorias</span>
          </PageTabsTrigger>
          <PageTabsTrigger value="financeiro">
            <Money size={15} />
            <span>Financeiro</span>
          </PageTabsTrigger>
          <PageTabsTrigger value="aditivos">
            <FilePlus size={15} />
            <span>Aditivos</span>
          </PageTabsTrigger>
        </PageTabsList>

        <TabsContent value="geral" className="mt-4">
          <ContratoGeralTab contrato={{ ...contract, parcelas: detail.parcelas }} />
        </TabsContent>

        <TabsContent value="vistorias" className="mt-4">
          <ContratoVistoriasTab contrato={contract} />
        </TabsContent>

        <TabsContent value="financeiro" className="mt-4">
          <ContratoFinanceiroTab
            contrato={contract}
            parcelas={detail.parcelas}
            dividas={detail.dividas}
            quitarDivida={detail.quitarDivida}
            quitandoDivida={detail.working.startsWith("divida-")}
            registrarPagamento={detail.registrarPagamento}
            lancarEncargo={detail.lancarEncargo}
            baixarRecibo={detail.baixarRecibo}
            baixandoRecibo={detail.baixandoRecibo}
          />
        </TabsContent>

        <TabsContent value="aditivos" className="mt-4">
          <ContratoAditivosTab
            contrato={contract}
            aditivos={detail.aditivos}
          />
        </TabsContent>
      </Tabs>

      <ModalConfirmarBlockchain
        aberto={detail.isBlockchainModalOpen}
        aoFechar={detail.closeBlockchainModal}
        aoConfirmar={() => {
          void detail.registrarNaBlockchain();
        }}
      />
    </div>
  );
}
