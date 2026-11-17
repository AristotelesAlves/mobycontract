"use client";

import { CheckCircle } from "@phosphor-icons/react";
import { Badge } from "@/components/ui/badge";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Card, CardContent } from "@/components/ui/card";
import type { Contrato, ContratoStatus } from "../../data/contrato-types";

const STATUS_VARIANTS: Record<
  ContratoStatus,
  "default" | "secondary" | "destructive" | "outline" | "positive"
> = {
  rascunho: "outline",
  Rascunho: "outline",
  ativo: "positive",
  vencido: "destructive",
  rescindido: "destructive",
  finalizado: "outline",
  Validado: "positive",
  Vigente: "positive",
  Pendente: "secondary",
  Minuta: "secondary",
  Encerrado: "outline",
  Cancelado: "destructive",
};

interface IContratoStepperProps {
  contrato: Contrato;
  arquivoUpload: File | null;
  setArquivoUpload: (arquivo: File | null) => void;
  confirmarDocumentoFisico: () => void;
  registrandoDocumento: boolean;
  setAbrirBlockchain: () => void;
  registrarAssinatura: (assinaturaId: string) => void;
  alterarStatus: (statusNovo: ContratoStatus, motivo?: string) => void;
}

export function ContratoStepper({
  contrato,
  arquivoUpload,
  setArquivoUpload,
  confirmarDocumentoFisico,
  registrandoDocumento,
  setAbrirBlockchain,
  registrarAssinatura,
  alterarStatus,
}: IContratoStepperProps) {
  const todasAssinaturasOk = (contrato.assinaturas ?? []).every((a) => a.assinado);
  const temFiador = contrato.fiador !== undefined;

  const isValidado =
    contrato.status === "Validado" ||
    contrato.status === "Vigente" ||
    Boolean(contrato.hashBlockchain);

  const documentoFisicoOk = Boolean(contrato.anexoContratoNome);

  const isRascunho = contrato.status === "Rascunho";
  const isAguardandoAssinaturas =
    (contrato.status === "Pendente" ||
      contrato.status === "Minuta" ||
      (contrato.status === "Validado" && !contrato.hashBlockchain)) &&
    !todasAssinaturasOk;

  const isAguardandoUpload =
    (contrato.status === "Pendente" ||
      contrato.status === "Minuta" ||
      (contrato.status === "Validado" && !contrato.hashBlockchain)) &&
    todasAssinaturasOk &&
    !documentoFisicoOk &&
    !contrato.hashBlockchain;

  const isAguardandoBlockchain =
    (contrato.status === "Pendente" ||
      contrato.status === "Minuta" ||
      (contrato.status === "Validado" && !contrato.hashBlockchain)) &&
    todasAssinaturasOk &&
    documentoFisicoOk &&
    !contrato.hashBlockchain;

  const etapasFluxo = [
    { label: "Minuta", concluida: !isRascunho, ativa: isRascunho },
    {
      label: "Assinaturas",
      concluida: todasAssinaturasOk && !isRascunho,
      ativa: isAguardandoAssinaturas,
    },
    {
      label: "Upload Físico",
      concluida:
        (documentoFisicoOk || Boolean(contrato.hashBlockchain)) &&
        !isRascunho &&
        todasAssinaturasOk,
      ativa: isAguardandoUpload,
    },
    {
      label: "Smart Contract",
      concluida: Boolean(contrato.hashBlockchain),
      ativa: isAguardandoBlockchain,
    },
    {
      label: "Validado",
      concluida: isValidado || contrato.status === "Encerrado",
      ativa: false,
    },
  ];

  const etapaPorPapel = (papel: string, rotulo: string) => {
    const assinatura = (contrato.assinaturas ?? []).find((a) => a.papel === papel);
    return {
      rotulo,
      concluida: assinatura?.assinado ?? false,
      assinaturaId: assinatura?.id,
      nomeAssinatura: assinatura?.nome,
    };
  };

  const etapasVerificacao: {
    rotulo: string;
    concluida: boolean;
    detalhe?: string;
    assinaturaId?: string;
    nomeAssinatura?: string;
  }[] = [
    { rotulo: "Contrato criado", concluida: true, detalhe: "Minuta gerada" },
    {
      rotulo: "Dados conferidos",
      concluida: contrato.status !== "Rascunho",
      detalhe:
        contrato.status === "Rascunho" ? "Validar a minuta" : "Minuta conferida",
    },
    etapaPorPapel("Imobiliária", "Assinatura da imobiliária"),
    etapaPorPapel("Locador", "Assinatura do locador"),
    etapaPorPapel("Locatário", "Assinatura do locatário"),
    ...(temFiador ? [etapaPorPapel("Fiador", "Assinatura do fiador")] : []),
    {
      rotulo: "Upload do Documento Físico",
      concluida: documentoFisicoOk || Boolean(contrato.hashBlockchain),
    },
    {
      rotulo: "Registro na Blockchain",
      concluida: Boolean(contrato.hashBlockchain),
    },
    {
      rotulo: "Contrato Validado",
      concluida: isValidado || contrato.status === "Encerrado",
    },
  ];

  const etapasPendentes = etapasVerificacao.filter((etapa) => !etapa.concluida);

  function pedirMotivoECancelar() {
    const motivo = window.prompt(
      "Informe o motivo do cancelamento do contrato (mínimo de 10 caracteres):",
    );
    if (!motivo || motivo.trim().length < 10) return;
    alterarStatus("Cancelado", motivo.trim());
  }

  return (
    <Card>
      <CardContent>
        <div className="mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4 mt-6">
          <div>
            <div className="flex items-center gap-3">
              <h3 className="text-xl font-semibold text-foreground tracking-tight">
                Etapas do Contrato
              </h3>
              <Badge 
                variant={STATUS_VARIANTS[contrato.status] ?? "outline"} 
                className="rounded-full px-3 py-0.5 text-xs font-medium shadow-sm"
              >
                Status Contrato: {contrato.status.toLocaleUpperCase()}
              </Badge>
            </div>
            <p className="text-sm text-muted-foreground mt-1">
              Acompanhe o progresso até o registro final na blockchain.
            </p>
          </div>
        </div>

        <div className="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3">
          {etapasFluxo.map((etapa, idx) => (
            <div
              key={etapa.label}
              className={[
                "relative p-4 rounded-2xl border transition-all duration-300 flex flex-col justify-between min-h-[100px]",
                etapa.concluida
                  ? "bg-primary/[0.04] border-primary/20"
                  : etapa.ativa
                  ? "bg-card border-primary shadow-md ring-4 ring-primary/5 scale-[1.03] z-10"
                  : "bg-muted/30 border-transparent opacity-60",
              ].join(" ")}
            >
              <div className="flex items-start justify-between">
                <div
                  className={[
                    "w-8 h-8 rounded-full flex items-center justify-center transition-colors",
                    etapa.concluida
                      ? "bg-primary text-primary-foreground shadow-sm"
                      : etapa.ativa
                      ? "bg-primary/10 text-primary"
                      : "bg-muted-foreground/20 text-muted-foreground",
                  ].join(" ")}
                >
                  {etapa.concluida ? (
                    <CheckCircle weight="bold" size={16} />
                  ) : (
                    <span className="text-[13px] font-bold">{idx + 1}</span>
                  )}
                </div>

                {etapa.ativa && (
                  <span className="flex h-2.5 w-2.5 mt-1 mr-1">
                    <span className="animate-ping absolute inline-flex h-2.5 w-2.5 rounded-full bg-primary opacity-60"></span>
                    <span className="relative inline-flex rounded-full h-2.5 w-2.5 bg-primary"></span>
                  </span>
                )}
              </div>

              <span
                className={[
                  "text-xs font-bold tracking-tight mt-4",
                  etapa.concluida
                    ? "text-foreground"
                    : etapa.ativa
                    ? "text-primary"
                    : "text-muted-foreground",
                ].join(" ")}
              >
                {etapa.label}
              </span>
            </div>
          ))}
        </div>

        {!isValidado && (
          <div className="flex flex-col gap-6 pt-6 border-t border-border/60 mt-8">
            <div>
              <h2 className="text-xl font-semibold tracking-tight text-foreground">
                Visão Geral
              </h2>
              <p className="text-muted-foreground mt-2 text-sm">
                {etapasPendentes.length > 0
                  ? isAguardandoUpload
                    ? `Próxima pendência: Fazer o upload do contrato físico assinado por todas as partes (Locador, Inquilino, Imobiliária${
                        temFiador ? " e Fiador" : ""
                      }).`
                    : `Próxima pendência: ${etapasPendentes[0].rotulo}`
                  : "Todas as etapas concluídas. Contrato validado e em conformidade."}
              </p>
            </div>

            <div className="flex flex-wrap items-center gap-3">
              {isRascunho && (
                <Button
                  className="rounded-full shadow-sm px-6"
                  disabled={registrandoDocumento}
                  onClick={() => alterarStatus("Pendente")}
                >
                  Liberar para Assinaturas
                </Button>
              )}

              {isAguardandoAssinaturas &&
                etapasPendentes.map(
                  (etapa) =>
                    etapa.assinaturaId && (
                      <Button
                        key={etapa.assinaturaId}
                        className="rounded-full shadow-sm px-6"
                        disabled={registrandoDocumento}
                        onClick={() =>
                          registrarAssinatura(etapa.assinaturaId!)
                        }
                      >
                        Assinar ({etapa.nomeAssinatura})
                      </Button>
                    )
                )}

              {isAguardandoUpload && (
                <div className="flex items-center gap-2 p-1 bg-muted/40 border rounded-full shadow-sm">
                  <label className="flex items-center gap-2 px-1.5 py-1 rounded-full cursor-pointer hover:bg-muted/50 transition-colors w-[260px]">
                    <span className="bg-primary text-primary-foreground px-4 py-1.5 rounded-full text-xs font-semibold shadow-sm shrink-0">
                      Procurar
                    </span>
                    <span className="text-xs text-muted-foreground truncate w-full pr-2">
                      {arquivoUpload
                        ? arquivoUpload.name
                        : "Nenhum arquivo selecionado"}
                    </span>
                    <Input
                      type="file"
                      accept=".pdf,image/*"
                      className="hidden"
                      onChange={(e) =>
                        setArquivoUpload(e.target.files?.[0] || null)
                      }
                    />
                  </label>
                  <Button
                    size="sm"
                    className="rounded-full px-5 h-9"
                    disabled={!arquivoUpload || registrandoDocumento}
                    onClick={confirmarDocumentoFisico}
                  >
                    {registrandoDocumento ? "Enviando..." : "Enviar arquivo!"}
                  </Button>
                </div>
              )}

              {isAguardandoBlockchain && (
                <Button
                  variant="outline"
                  className="rounded-full px-6 border-purple-500 text-purple-600 hover:bg-purple-50"
                  onClick={() => setAbrirBlockchain()}
                >
                  Registrar na Blockchain
                </Button>
              )}

              {!["Validado", "Vigente", "Encerrado", "Cancelado"].includes(
                contrato.status
              ) && (
                <Button
                  variant="ghost"
                  className="rounded-full text-destructive hover:bg-destructive/10 px-6"
                  onClick={pedirMotivoECancelar}
                >
                  Cancelar Contrato
                </Button>
              )}
            </div>
          </div>
        )}
      </CardContent>
    </Card>
  );
}
