"use client";

import { Suspense, useRef, useState } from "react";
import { ArrowLeft, FilePdf, CheckCircle, ListChecks, PencilSimple } from "@phosphor-icons/react";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { downloadDocumentPdf } from "@/lib/download-document-pdf";
import { downloadContractDocument } from "../services/contract-service";
import { getReadableErrorMessage } from "@/services/api/get-readable-error-message";
import { normalizeAddendumExtraData } from "../use-cases/addendum-draft";
import { formatDate } from "@/lib/formatters";
import { Badge } from "@/components/ui/badge";
import {
  Card,
  CardContent,
  CardDescription,
  CardHeader,
  CardTitle,
} from "@/components/ui/card";
import {
  type ContratoAditivo,
  type MotivoAditivo,
  type StatusAditivo,
} from "../data/contrato-types";
import { useAddendumPreview } from "../hooks/use-addendum-preview";

const STATUS_VARIANTS: Record<
  StatusAditivo,
  "default" | "secondary" | "destructive" | "outline" | "positive"
> = {
  Rascunho: "outline",
  "Pendente de assinatura": "secondary",
  Assinado: "secondary",
  Cancelado: "destructive",
  Validado: "positive",
};

interface IAditivoStepperProps {
  aditivo: ContratoAditivo;
  alterarStatus: (novoStatus: StatusAditivo, motivo?: string) => void;
  assinar: (assinaturaId: string) => void;
  arquivoUpload: File | null;
  setArquivoUpload: (file: File | null) => void;
  confirmarUpload: () => void;
  registrarBlockchain: () => void;
  ocupado: boolean;
}

function AditivoStepper({
  aditivo,
  alterarStatus,
  assinar,
  arquivoUpload,
  setArquivoUpload,
  confirmarUpload,
  registrarBlockchain,
  ocupado,
}: IAditivoStepperProps) {
  const isCancelado = aditivo.status === "Cancelado";
  const isRascunho = aditivo.status === "Rascunho";
  const isAguardandoAssinaturas = aditivo.status === "Pendente de assinatura";
  const aditivoValidado =
    aditivo.status === "Validado" || Boolean(aditivo.hashBlockchain);
  const isAssinado = aditivo.status === "Assinado" || aditivoValidado;
  const isAguardandoUpload =
    isAssinado &&
    !aditivo.uploadConfirmado &&
    !aditivo.hashBlockchain;
  const isAguardandoBlockchain =
    isAssinado &&
    Boolean(aditivo.uploadConfirmado) &&
    !aditivo.hashBlockchain;

  const assinaturasPendentes = (aditivo.assinaturas ?? []).filter(
    (assinatura) => !assinatura.assinado,
  );

  const etapasFluxo = [
    { label: "Minuta", concluida: !isRascunho && !isCancelado, ativa: isRascunho },
    { label: "Assinaturas", concluida: isAssinado, ativa: isAguardandoAssinaturas },
    { label: "Documento assinado", concluida: Boolean(aditivo.uploadConfirmado || aditivo.hashBlockchain), ativa: isAguardandoUpload },
    { label: "Registro na blockchain", concluida: Boolean(aditivo.hashBlockchain), ativa: isAguardandoBlockchain },
    { label: "Validado", concluida: aditivoValidado, ativa: false }
  ];

  function pedirMotivoECancelar() {
    const motivo = window.prompt(
      "Informe o motivo do cancelamento do aditivo (mínimo de 10 caracteres):",
    );
    if (!motivo || motivo.trim().length < 10) return;
    alterarStatus("Cancelado", motivo.trim());
  }

  return (
    <Card>
      <CardContent>
        <div className="mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4 mt-6">
          <div>
            <h3 className="text-xl font-semibold text-foreground tracking-tight">Etapas do Aditivo</h3>
            <p className="text-sm text-muted-foreground mt-1">Acompanhe o progresso da formalização deste aditivo.</p>
          </div>
        </div>

        <div className="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3">
          {etapasFluxo.map((etapa, idx) => (
            <div key={etapa.label} className={[
              "relative p-4 rounded-2xl border transition-all duration-300 flex flex-col justify-between min-h-[100px]",
              etapa.concluida ? "bg-primary/[0.04] border-primary/20" :
                etapa.ativa ? "bg-card border-primary shadow-md ring-4 ring-primary/5 scale-[1.03] z-10" :
                  "bg-muted/30 border-transparent opacity-60"
            ].join(" ")}>
              <div className="flex items-start justify-between">
                <div className={[
                  "w-8 h-8 rounded-full flex items-center justify-center transition-colors",
                  etapa.concluida ? "bg-primary text-primary-foreground shadow-sm" :
                    etapa.ativa ? "bg-primary/10 text-primary" :
                      "bg-muted-foreground/20 text-muted-foreground"
                ].join(" ")}>
                  {etapa.concluida ? <CheckCircle weight="bold" size={16} /> : <span className="text-[13px] font-bold">{idx + 1}</span>}
                </div>

                {etapa.ativa && (
                  <span className="flex h-2.5 w-2.5 mt-1 mr-1">
                    <span className="animate-ping absolute inline-flex h-2.5 w-2.5 rounded-full bg-primary opacity-60"></span>
                    <span className="relative inline-flex rounded-full h-2.5 w-2.5 bg-primary"></span>
                  </span>
                )}
              </div>

              <span className={[
                "text-xs font-bold tracking-tight mt-4",
                etapa.concluida ? "text-foreground" :
                  etapa.ativa ? "text-primary" : "text-muted-foreground"
              ].join(" ")}>
                {etapa.label}
              </span>
            </div>
          ))}
        </div>

        {isCancelado && <p className="mt-4 text-sm text-muted-foreground">Aditivo cancelado. A formalização foi interrompida.</p>}
        {!aditivoValidado && !isCancelado && (
          <div className="flex flex-col gap-6 pt-6 border-t border-border/60 mt-8">
            <div>
              <h2 className="text-xl font-semibold tracking-tight text-foreground">
                Visão Geral
              </h2>
              <p className="text-muted-foreground mt-2 text-sm">
                {aditivo.status === "Rascunho"
                  ? "Próxima pendência: Enviar minuta para assinatura de todas as partes."
                  : isAguardandoAssinaturas
                  ? "Próxima pendência: Aguardando assinatura das partes no aditivo."
                  : !aditivo.uploadConfirmado
                  ? "Próxima pendência: Fazer o upload do aditivo físico assinado."
                  : !aditivo.hashBlockchain
                  ? "Próxima pendência: Registrar na Blockchain."
                  : "Todas as etapas concluídas. Aditivo validado e formalizado."}
              </p>
            </div>
            <div className="flex flex-wrap items-center gap-3">
              {isRascunho && (
                <Button
                  className="rounded-full shadow-sm px-6"
                  disabled={ocupado}
                  onClick={() => alterarStatus("Pendente de assinatura")}
                >
                  Enviar para assinatura
                </Button>
              )}
              {isAguardandoAssinaturas &&
                assinaturasPendentes.map((assinatura) => (
                  <Button
                    key={assinatura.id}
                    className="rounded-full shadow-sm px-6"
                    disabled={ocupado}
                    onClick={() => assinar(assinatura.id)}
                  >
                    Assinar ({assinatura.nome})
                  </Button>
                ))}

              {isAguardandoAssinaturas && Boolean(aditivo.assinaturas?.length) && assinaturasPendentes.length === 0 && (
                <Button disabled={ocupado} onClick={() => alterarStatus("Assinado")}>
                  Confirmar assinaturas e continuar
                </Button>
              )}
              {isAguardandoAssinaturas && !aditivo.assinaturas?.length && (
                <p role="alert" className="text-sm text-destructive">Este aditivo não possui signatários cadastrados. Confira as partes do contrato.</p>
              )}
              {isAguardandoUpload && (
                <div className="flex items-center gap-2 p-1 bg-muted/40 border rounded-full shadow-sm">
                  <label className="flex items-center gap-2 px-1.5 py-1 rounded-full cursor-pointer hover:bg-muted/50 transition-colors w-[260px]">
                    <span className="bg-primary text-primary-foreground px-4 py-1.5 rounded-full text-xs font-semibold shadow-sm shrink-0">
                      Procurar
                    </span>
                    <span className="text-xs text-muted-foreground truncate w-full pr-2">
                      {arquivoUpload ? arquivoUpload.name : "Nenhum arquivo selecionado"}
                    </span>
                    <Input
                      type="file"
                      accept=".pdf,image/*"
                      className="hidden"
                      onChange={(e) => setArquivoUpload(e.target.files?.[0] || null)}
                    />
                  </label>
                  <Button
                    size="sm"
                    className="rounded-full px-5 h-9"
                    disabled={!arquivoUpload || ocupado}
                    onClick={confirmarUpload}
                  >
                    {ocupado ? "Enviando..." : "Enviar arquivo!"}
                  </Button>
                </div>
              )}

              {isAguardandoBlockchain && (
                <Button
                  variant="outline"
                  className="rounded-full px-6 border-purple-500 text-purple-600 hover:bg-purple-50"
                  disabled={ocupado}
                  onClick={registrarBlockchain}
                >
                  Registrar na Blockchain
                </Button>
              )}

              {!["Assinado", "Validado"].includes(aditivo.status) && (
                <Button
                  variant="ghost"
                  className="rounded-full text-destructive hover:bg-destructive/10 px-6"
                  onClick={pedirMotivoECancelar}
                >
                  Cancelar Aditivo
                </Button>
              )}
            </div>
          </div>
        )}
      </CardContent>
    </Card>
  );
}

export default function VisualizarAditivoPage({
  id,
  aditivoId,
}: {
  id: string;
  aditivoId: string;
}) {
  return (
    <Suspense fallback={null}>
      <VisualizarAditivo contratoId={id} aditivoId={aditivoId} />
    </Suspense>
  );
}

function VisualizarAditivo({
  contratoId,
  aditivoId,
}: {
  contratoId: string;
  aditivoId: string;
}) {
  const preview = useAddendumPreview(contratoId, aditivoId);
  const {
    contract: contrato,
    addendum: aditivo,
    htmlContent,
    isBlockchainRegistered,
    isStepperVisible,
    toggleStepper,
    changeStatus,
    assinar,
    confirmUpload,
    registerOnBlockchain,
    working,
    loading: carregando,
    error,
    retry,
    goToContracts,
    goToEdit,
  } = preview;
  const iframeRef = useRef<HTMLIFrameElement>(null);

  const [downloading, setDownloading] = useState(false);
  const [downloadError, setDownloadError] = useState("");
  async function download(signed = false) {
    if (!aditivo || downloading) return;
    setDownloading(true);
    setDownloadError("");
    try {
      if (signed) await downloadContractDocument(contratoId, aditivoId);
      else if (htmlContent) await downloadDocumentPdf(htmlContent, contratoId, `aditivo-${aditivo.numero}`);
    } catch (error) {
      setDownloadError(getReadableErrorMessage(error));
    } finally {
      setDownloading(false);
    }
  }

  if (!contrato || !aditivo) {
    return (
      <div className="p-6">
        <Button
          variant="outline"
          onClick={goToContracts}
        >
          <ArrowLeft size={16} />
          Voltar
        </Button>
        {error ? (
          <p className="text-destructive mt-4 text-sm">
            {error}{" "}
            <Button variant="link" size="sm" onClick={retry}>
              Tentar novamente
            </Button>
          </p>
        ) : (
          <p className="text-muted-foreground mt-4 text-sm">
            {carregando ? "Carregando aditivo..." : "Aditivo não encontrado."}
          </p>
        )}
      </div>
    );
  }

  return (
    <div className="flex flex-col gap-4 pb-10">
      <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <Button variant="outline" onClick={goToContracts}>
          <ArrowLeft size={16} />
         Voltar
        </Button>
        <div className="flex items-center gap-2">
          <Badge variant={STATUS_VARIANTS[aditivo.status]} className="text-xs">
            {aditivo.status}
          </Badge>
          {isBlockchainRegistered && (
            <Button
              variant="outline"
              size="sm"
              onClick={toggleStepper}
              className="rounded-full h-8.5 px-3.5 text-xs font-medium border-border/60 bg-muted/20 hover:bg-muted/50 text-muted-foreground hover:text-foreground transition-all shadow-xs gap-1.5 cursor-pointer"
              title={isStepperVisible ? "Ocultar etapas do aditivo" : "Visualizar etapas do aditivo"}
            >
              <span className="size-1.5 rounded-full bg-emerald-500 animate-pulse" />
              <ListChecks size={15} className="text-emerald-500" />
              {isStepperVisible ? "Ocultar Etapas" : "Ver Etapas"}
            </Button>
          )}
          {aditivo.status === "Rascunho" && (
            <Button
              variant="outline"
              size="sm"
              onClick={goToEdit}
              className="rounded-full h-8.5 px-4 text-xs font-medium gap-1.5 cursor-pointer"
            >
              <PencilSimple size={15} />
              Editar Aditivo
            </Button>
          )}
          <Button
            variant="outline"
            size="sm"
            onClick={() => download()}
            disabled={!htmlContent || downloading}
            className="rounded-full h-8.5 px-4 text-xs font-medium gap-1.5 cursor-pointer"
          >
            <FilePdf size={15} className="text-red-400" />
            {downloading ? "Baixando..." : "Baixar minuta em PDF"}
          </Button>
          {aditivo.anexoNome && (
            <Button variant="outline" size="sm" disabled={downloading} onClick={() => download(true)}>
              Baixar aditivo assinado
            </Button>
          )}
        </div>
      </div>
      {downloadError && <p role="alert" className="text-sm text-destructive">{downloadError}</p>}

      {(!isBlockchainRegistered || isStepperVisible) && (
        <AditivoStepper
          aditivo={aditivo}
          alterarStatus={(status, motivo) => changeStatus(status, motivo)}
          assinar={(assinaturaId) => assinar(assinaturaId)}
          arquivoUpload={preview.uploadFile}
          setArquivoUpload={preview.setUploadFile}
          confirmarUpload={confirmUpload}
          registrarBlockchain={registerOnBlockchain}
          ocupado={working}
        />
      )}

      <Card>
        <CardHeader>
          <div className="flex flex-col gap-1">
            <CardTitle className="text-xl">
              {aditivo.titulo} — nº {aditivo.numero}
            </CardTitle>
            <CardDescription>
              Contrato #{contrato.numero} • {formatDate(aditivo.data)} • Criado por{" "}
              {aditivo.criadoPor || "—"}
            </CardDescription>
          </div>
        </CardHeader>
        <CardContent>
          {aditivo.motivos.length > 0 && (
            <div className="flex flex-wrap gap-2 mb-4">
              {(aditivo.motivos as MotivoAditivo[]).map((motivo) => (
                <Badge key={motivo} variant="secondary" className="text-xs">
                  {motivo}
                </Badge>
              ))}
            </div>
          )}
          <div className="mb-6 space-y-3 rounded-xl border border-border bg-muted/20 p-4">
            <h3 className="font-semibold">O que este aditivo altera</h3>
            <p className="text-sm text-muted-foreground">
              {aditivo.status === "Validado" ? "Alterações formalizadas neste aditivo." : aditivo.status === "Cancelado" ? "Proposta cancelada; este aditivo não foi formalizado." : "Alterações propostas, aguardando a conclusão das etapas de formalização."}
            </p>
            {aditivo.descricao && <p className="whitespace-pre-wrap text-sm">{aditivo.descricao}</p>}
            {normalizeAddendumExtraData(aditivo.dadosExtras).length > 0 ? (
              <dl className="grid gap-3 sm:grid-cols-2">
                {normalizeAddendumExtraData(aditivo.dadosExtras).map(({ label, valor }, index) => (
                  <div key={`${label}-${index}`}>
                    <dt className="text-xs text-muted-foreground">{label}</dt>
                    <dd className="whitespace-pre-wrap text-sm font-medium">{label === "Nova Data de Término" ? formatDate(valor) : valor}</dd>
                  </div>
                ))}
              </dl>
            ) : <p className="text-sm text-muted-foreground">Não há valores ou cláusulas detalhados cadastrados neste aditivo.</p>}
          </div>
          <iframe
            sandbox=""
            ref={iframeRef}
            title="Documento do aditivo"
            srcDoc={htmlContent ?? ""}
            className="w-full h-[720px] rounded-lg border border-border bg-white"
          />
        </CardContent>
      </Card>
    </div>
  );
}
