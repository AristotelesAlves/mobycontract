"use client";

import { useEffect, useRef, useState } from "react";
import { ArrowLeft, FilePdf } from "@phosphor-icons/react";
import { Button } from "@/components/ui/button";
import { useRouter } from "next/navigation";
import { downloadDocumentPdf } from "@/lib/download-document-pdf";
import { downloadContractDocument, getContractDocument } from "../services/contract-service";
import { getReadableErrorMessage } from "@/services/api/get-readable-error-message";
import { useVisualizarContrato } from "../hooks/use-view-contract";

export function VisualizarContratoView({ idInicial }: { idInicial: string }) {
  const router = useRouter();
  const iframeRef = useRef<HTMLIFrameElement>(null);

  const { contrato, htmlContrato, loading, error, reload } = useVisualizarContrato(idInicial);

  const [downloading, setDownloading] = useState(false);
  const [downloadError, setDownloadError] = useState("");
  const [signedUrl, setSignedUrl] = useState("");
  const [signedError, setSignedError] = useState("");

  useEffect(() => {
    let cancelled = false;
    let url = "";
    setSignedUrl("");
    setSignedError("");
    const documento = contrato?.anexoContratoNome
      ? getContractDocument(contrato.id)
      : Promise.resolve(null);
    documento
      .then((blob) => {
        if (blob === null || cancelled) return;
        url = URL.createObjectURL(blob);
        setSignedUrl(url);
      })
      .catch((error) => {
        if (!cancelled) setSignedError(getReadableErrorMessage(error));
      });
    return () => {
      cancelled = true;
      if (url) URL.revokeObjectURL(url);
    };
  }, [contrato?.id, contrato?.anexoContratoNome]);

  const handleDownload = async (signed = false) => {
    if (!contrato || downloading) return;
    setDownloading(true);
    setDownloadError("");
    try {
      if (signed) await downloadContractDocument(contrato.id);
      else if (htmlContrato) await downloadDocumentPdf(htmlContrato, contrato.id, `contrato-${contrato.numero}`);
    } catch (error) {
      setDownloadError(getReadableErrorMessage(error));
    } finally {
      setDownloading(false);
    }
  };

  if (!contrato) {
    return (
      <div className="p-6">
        <Button variant="outline" onClick={() => router.push("/contratos")}>
          <ArrowLeft size={16} />
          Voltar
        </Button>
        <p className="text-muted-foreground mt-4 text-sm">
          {loading ? "Carregando contrato..." : error || "Contrato não encontrado."}
        </p>
        {error && <Button onClick={reload}>Tentar novamente</Button>}
      </div>
    );
  }

  return (
    <div className="flex flex-col gap-4">
      <div className="flex flex-wrap items-center justify-between gap-3">
        <div className="flex flex-wrap gap-2">
          <Button variant="outline" onClick={() => handleDownload()} disabled={!htmlContrato || downloading}>
            <FilePdf size={16} /> {downloading ? "Baixando..." : "Baixar minuta em PDF"}
          </Button>
          {contrato.anexoContratoNome && (
            <Button variant="outline" onClick={() => handleDownload(true)} disabled={downloading}>
              Baixar contrato assinado
            </Button>
          )}
        </div>
        <div className="flex items-center gap-2 text-sm text-muted-foreground">
          <FilePdf size={14} />
          Documento do contrato #{contrato.numero}
        </div>
      </div>

      {downloadError && <p role="alert" className="text-sm text-destructive">{downloadError}</p>}
      {signedError && <p role="alert" className="text-sm text-destructive">Não foi possível abrir o contrato assinado: {signedError}</p>}
      {signedUrl && <p role="status" className="text-sm text-muted-foreground">Exibindo o arquivo assinado anexado ao contrato.</p>}
      {!htmlContrato && !signedUrl && <p role="status">O modelo não está disponível para gerar a minuta deste contrato.</p>}
      <div className="overflow-hidden rounded-md border">
        <iframe
          ref={iframeRef}
          src={signedUrl || undefined}
          srcDoc={signedUrl ? undefined : htmlContrato ?? undefined}
          title={`Contrato ${contrato.numero}`}
          className="h-[calc(100vh-140px)] w-full border-0"
          sandbox={signedUrl ? undefined : "allow-same-origin"}
        />
      </div>
    </div>
  );
}
