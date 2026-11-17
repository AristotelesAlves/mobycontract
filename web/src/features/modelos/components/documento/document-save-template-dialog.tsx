"use client";

import { useState } from "react";
import { Check } from "@phosphor-icons/react/dist/ssr";
import { Button } from "@/components/ui/button";
import {
  Dialog,
  DialogContent,
  DialogDescription,
  DialogFooter,
  DialogHeader,
  DialogTitle,
} from "@/components/ui/dialog";
import { Input } from "@/components/ui/input";
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from "@/components/ui/select";
import { useScaleToFit } from "@/hooks/use-scale-to-fit";
import type { DocumentTypography } from "@/lib/tiptap/document-style";
import {
  documentTotalHeightInPixels,
  type DocumentPageGeometry,
} from "@/lib/tiptap/document-geometry";
import { documentHtmlForExport } from "@/lib/tiptap/html";
import type { DocumentVariableValues } from "@/lib/tiptap/document-variables";
import {
  useDocumentTemplateDraft,
  type DocumentSaveData,
  type DocumentTemplateSeed,
  type DocumentTemplateType,
} from "../../hooks/use-document-template-draft";

const DOCUMENT_PREVIEW_GAP_PIXELS = 32;

export type { DocumentSaveData, DocumentTemplateType };

interface DocumentSaveTemplateDialogProps {
  open: boolean;
  onOpenChange: (open: boolean) => void;
  seed: DocumentTemplateSeed;
  geometry: DocumentPageGeometry;
  typography: DocumentTypography;
  pageCount: number;
  sampleValues: DocumentVariableValues;
  getHtmlContent: () => string;
  onSave: (data: DocumentSaveData) => boolean | Promise<boolean>;
}

export function DocumentSaveTemplateDialog(
  props: DocumentSaveTemplateDialogProps,
) {
  const draftForm = useDocumentTemplateDraft(props.seed);
  const [salvando, setSalvando] = useState(false);

  const { referenciaDaArea, escala } = useScaleToFit(
    props.geometry.pageWidthPixels,
  );

  const alturaDoDocumentoPixels =
    documentTotalHeightInPixels(
      props.geometry,
      props.pageCount,
    ) + DOCUMENT_PREVIEW_GAP_PIXELS;

  const htmlDaVisualizacao = documentHtmlForExport({
    title: draftForm.draft.title || props.seed.title,
    htmlContent: props.getHtmlContent(),
    margins: props.geometry.margins,
    typography: props.typography,
    pageCount: props.pageCount,
    values: props.sampleValues,
  });

  function alternarDialogo(proximoEstado: boolean) {
    if (proximoEstado) draftForm.reset();
    props.onOpenChange(proximoEstado);
  }

  async function salvarModelo() {
    setSalvando(true);
    try {
      const saved = await draftForm.submit(
        props.getHtmlContent(),
        props.onSave,
      );
      if (saved) alternarDialogo(false);
    } finally {
      setSalvando(false);
    }
  }

  return (
    <Dialog open={props.open} onOpenChange={alternarDialogo}>
      <DialogContent className="w-[min(96vw,60rem)]">
        <DialogHeader className="flex flex-row items-end justify-between py-4">
          <div>
            <DialogTitle>Salvar modelo</DialogTitle>
            <DialogDescription>
              Confira o modelo final e defina o título, o tipo e a versão do
              documento.
            </DialogDescription>
          </div>
          <span className="text-muted-foreground text-sm">
            {props.pageCount}{" "}
            {props.pageCount === 1 ? "página" : "páginas"}
          </span>
        </DialogHeader>
        <div className="flex flex-col gap-6">
          <div className="flex flex-col gap-3">
            <div
              ref={referenciaDaArea}
              className="bg-muted flex max-h-[50vh] justify-center overflow-auto rounded-md"
            >
              <div
                style={{
                  width:
                    props.geometry.pageWidthPixels * escala,
                  height: alturaDoDocumentoPixels * escala,
                }}
              >
                <iframe
            sandbox=""
                  title="Template final do documento"
                  srcDoc={htmlDaVisualizacao}
                  style={{
                    width: props.geometry.pageWidthPixels,
                    height: alturaDoDocumentoPixels,
                    transform: `scale(${escala})`,
                    transformOrigin: "top left",
                    border: 0,
                  }}
                />
              </div>
            </div>
          </div>

          <div className="flex flex-col gap-4 w-full">
            <div className="space-y-2 w-full">
              <label className="text-sm font-medium">Título do documento</label>
              <Input
                autoFocus
                className="w-full"
                placeholder="Digite o título do documento"
                value={draftForm.draft.title}
                onChange={(evento) => draftForm.changeTitle(evento.target.value)}
              />
              {draftForm.fieldErrors.title && (
                <p className="text-destructive text-xs">
                  {draftForm.fieldErrors.title}
                </p>
              )}
            </div>
            <div className="space-y-2 flex flex-col w-full">
              <label className="text-sm font-medium">Tipo do documento</label>
              <Select
                value={draftForm.draft.type}
                onValueChange={(value) => {
                  if (!value) return;
                  draftForm.changeType(value as DocumentTemplateType);
                }}
              >
                <SelectTrigger className="w-full">
                  <SelectValue placeholder="Selecione o tipo" />
                </SelectTrigger>
                <SelectContent>
                  {draftForm.templateTypes.map((tipo) => (
                    <SelectItem key={tipo} value={tipo}>
                      {tipo}
                    </SelectItem>
                  ))}
                </SelectContent>
              </Select>
              {draftForm.fieldErrors.tipo && (
                <p className="text-destructive text-xs">
                  {draftForm.fieldErrors.tipo}
                </p>
              )}
            </div>

            {draftForm.guaranteeVisible && (
              <div className="space-y-2 flex flex-col w-full animate-in fade-in duration-200">
                <label className="text-sm font-medium">Garantia Relacionada</label>
                <Select
                  value={draftForm.draft.guarantee}
                  onValueChange={(value) => {
                    if (value) draftForm.changeGuarantee(value);
                  }}
                >
                  <SelectTrigger className="w-full">
                    <SelectValue placeholder="Opcional" />
                  </SelectTrigger>
                  <SelectContent>
                    <SelectItem value="Caução">Caução em dinheiro ou imóvel</SelectItem>
                    <SelectItem value="Fiador">Fiador</SelectItem>
                    <SelectItem value="Seguro-fiança">Seguro-fiança locatícia</SelectItem>
                    <SelectItem value="Título de capitalização">Título de capitalização</SelectItem>
                    <SelectItem value="Sem garantia">Sem garantia (Art. 42)</SelectItem>
                  </SelectContent>
                </Select>
              </div>
            )}

            <div className="space-y-2 flex flex-col w-full">
              <label className="text-sm font-medium">Versão</label>
              <span>{draftForm.draft.version}V</span>
              {draftForm.fieldErrors.versao && (
                <p className="text-destructive text-xs">
                  {draftForm.fieldErrors.versao}
                </p>
              )}
            </div>
          </div>
        </div>
        <DialogFooter className="flex-col items-stretch gap-2 sm:flex-row sm:items-center sm:justify-end">
          {draftForm.submitError && (
            <p className="text-destructive text-sm sm:mr-auto">
              {draftForm.submitError}
            </p>
          )}
          <Button
            variant="outline"
            onClick={() => alternarDialogo(false)}
            disabled={salvando}
          >
            Cancelar
          </Button>
          <Button onClick={salvarModelo} disabled={salvando}>
            <Check size={20} />
            {salvando ? "Salvando..." : "Confirmar salvamento"}
          </Button>
        </DialogFooter>
      </DialogContent>
    </Dialog>
  );
}