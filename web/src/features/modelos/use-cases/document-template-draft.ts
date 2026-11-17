import { DomainError } from "@/core/errors/domain-error";

export const DOCUMENT_TEMPLATE_TYPES = [
  "Contrato",
  "Aditivo",
  "Distrato",
] as const;

export type DocumentTemplateType = (typeof DOCUMENT_TEMPLATE_TYPES)[number];

export interface DocumentTemplateDraft {
  title: string;
  type: DocumentTemplateType | "";
  guarantee: string;
  version: number;
}

export interface DocumentSaveData {
  title: string;
  tipo: DocumentTemplateType;
  versao: number;
  htmlContent: string;
  garantia?: string;
}

export type DocumentTemplateFieldName = "title" | "tipo" | "versao";

export type DocumentTemplateFieldErrors = Partial<
  Record<DocumentTemplateFieldName, string>
>;

export function supportsRelatedGuarantee(
  type: DocumentTemplateType | "",
): boolean {
  return type === "Contrato" || type === "Aditivo";
}

export function validateDocumentTemplateDraft(
  draft: DocumentTemplateDraft,
): void {
  const errors: DocumentTemplateFieldErrors = {};

  if (!draft.title.trim()) errors.title = "O título é obrigatório";
  if (!draft.type) errors.tipo = "Selecione o tipo do documento";
  if (!draft.version || draft.version < 1) {
    errors.versao = "A versão deve ser maior ou igual a 1";
  }

  if (Object.keys(errors).length > 0) {
    throw new DomainError(
      "Confira os campos obrigatórios do modelo.",
      "INVALID_DOCUMENT_TEMPLATE_DRAFT",
      errors,
    );
  }
}

export function buildDocumentSaveData(
  draft: DocumentTemplateDraft,
  htmlContent: string,
): DocumentSaveData {
  const type = draft.type as DocumentTemplateType;
  return {
    title: draft.title.trim(),
    tipo: type,
    versao: draft.version,
    htmlContent,
    garantia: supportsRelatedGuarantee(type) ? draft.guarantee : undefined,
  };
}