"use client";

import { useCallback, useState } from "react";
import { formatTaxId } from "@/lib/formatters";
import type { FiadorContrato } from "../data/contrato-types";

const MAX_GUARANTORS = 2;

export type AttachmentStatus = "idle" | "uploading" | "done";

export function useContractGuarantees() {
  const [guaranteeType, setGuaranteeType] = useState("Sem garantia");
  const [depositAmount, setDepositAmount] = useState("");
  const [guarantors, setGuarantors] = useState<FiadorContrato[]>([]);
  const [attachmentStatus, setAttachmentStatus] =
    useState<AttachmentStatus>("idle");
  const [attachmentError, setAttachmentError] = useState("");
  const [attachment, setAttachment] = useState<File | undefined>();

  const addGuarantor = useCallback(() => {
    setGuarantors((current) =>
      current.length >= MAX_GUARANTORS
        ? current
        : [...current, { 
            id: "", 
            nome: "", 
            cpf: "", 
            email: "", 
            telefone: "", 
            renda: 0, 
            criadoEm: new Date().toISOString() 
          }],
    );
  }, []);

  const removeGuarantor = useCallback((index: number) => {
    setGuarantors((current) => current.filter((_, i) => i !== index));
  }, []);

  const updateGuarantor = useCallback(
    (index: number, field: keyof FiadorContrato, value: string) => {
      setGuarantors((current) => {
        const next = [...current];
        const target = next[index];
        if (!target) return current;
        next[index] =
          field === "cpf" ? { ...target, cpf: formatTaxId(value) } : { ...target, [field]: value };
        return next;
      });
    },
    [],
  );

  const handleAttachment = useCallback((file: File | undefined) => {
    if (!file) return;
    if (!["application/pdf", "image/jpeg", "image/png", "image/webp"].includes(file.type) || file.size > 5 * 1024 * 1024) {
      setAttachmentError("Envie PDF, JPG, PNG ou WebP de até 5 MB.");
      setAttachment(undefined);
      setAttachmentStatus("idle");
      return;
    }
    setAttachmentError("");
    setAttachment(file);
    setAttachmentStatus("done");
  }, []);

  const clearAttachment = useCallback(() => {
    setAttachment(undefined);
    setAttachmentStatus("idle");
  }, []);

  return {
    guaranteeType,
    depositAmount,
    guarantors,
    attachmentStatus,
    attachment,
    attachmentName: attachment?.name,
    attachmentError,
    setGuaranteeType,
    setDepositAmount,
    addGuarantor,
    removeGuarantor,
    updateGuarantor,
    handleAttachment,
    clearAttachment,
  };
}