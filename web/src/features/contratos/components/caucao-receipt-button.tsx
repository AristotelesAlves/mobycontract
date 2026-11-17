"use client";

import { useState } from "react";
import { FilePdf, SpinnerGap } from "@phosphor-icons/react";
import { Button } from "@/components/ui/button";
import { useFeedback } from "@/components/ui/feedback-provider";
import { downloadDocumentPdf } from "@/lib/download-document-pdf";
import { getReadableErrorMessage } from "@/services/api/get-readable-error-message";
import { getContract } from "../services/contract-service";
import { depositReceiptHtml } from "../use-cases/payment-receipt";
import type { Contrato } from "../data/contrato-types";

export function CaucaoReceiptButton({ contrato }: { contrato: Contrato }) {
  const [baixando, setBaixando] = useState(false);
  const { pushFeedback } = useFeedback();

  async function baixar() {
    if (baixando) return;
    setBaixando(true);
    try {
      const atualizado = await getContract(contrato.id);
      await downloadDocumentPdf(depositReceiptHtml(atualizado), atualizado.id, `recibo-caucao-${atualizado.numero}`);
    } catch (error) {
      pushFeedback("error", getReadableErrorMessage(error));
    } finally {
      setBaixando(false);
    }
  }

  if (contrato.garantia !== "Caução") return null;
  return (
    <Button variant="outline" size="sm" className="mt-3 w-fit max-w-full gap-2" disabled={baixando} onClick={baixar}>
      {baixando ? <SpinnerGap size={15} className="animate-spin" /> : <FilePdf size={15} />}
      {baixando ? "Gerando recibo..." : "Baixar recibo da caução"}
    </Button>
  );
}
