"use client";

import { useState } from "react";
import { Gear } from "@phosphor-icons/react/dist/ssr";
import { Button } from "@/components/ui/button";
import {
  Dialog,
  DialogContent,
  DialogDescription,
  DialogFooter,
  DialogHeader,
  DialogTitle,
  DialogTrigger,
} from "@/components/ui/dialog";
import { Input } from "@/components/ui/input";
import {
  normalizeDocumentTypography,
  type DocumentTypography,
} from "@/lib/tiptap/document-style";
import {
  DOCUMENT_MARGIN_SIDES,
  DOCUMENT_MARGIN_LIMITS_MILLIMETERS,
  DOCUMENT_MARGIN_LABELS,
  normalizeDocumentMargins,
  type DocumentMarginSide,
  type DocumentMargins,
} from "@/lib/tiptap/document-geometry";

interface DocumentSettingsDialogProps {
  margins: DocumentMargins;
  typography: DocumentTypography;
  onApplySettings: (
    margins: DocumentMargins,
    typography: DocumentTypography,
  ) => void;
}

export function DocumentSettingsDialog(
  props: DocumentSettingsDialogProps,
) {
  const [dialogoAberto, definirDialogoAberto] = useState(false);
  const [marginsDraft, setMarginsDraft] = useState(
    props.margins,
  );
  const [typographyDraft, definirTipografiaEmEdicao] = useState(
    props.typography,
  );

  function alternarDialogo(proximoEstado: boolean) {
    if (proximoEstado) {
      setMarginsDraft(props.margins);
      definirTipografiaEmEdicao(props.typography);
    }
    definirDialogoAberto(proximoEstado);
  }

  function updateMargin(side: DocumentMarginSide, value: number) {
    setMarginsDraft((margins) => ({ ...margins, [side]: value }));
  }

  function applySettings() {
    props.onApplySettings(
      normalizeDocumentMargins(marginsDraft),
      normalizeDocumentTypography(typographyDraft),
    );
    definirDialogoAberto(false);
  }

  return (
    <Dialog open={dialogoAberto} onOpenChange={alternarDialogo}>
      <DialogTrigger
        render={
          <Button variant="ghost" title="Configurações do documento">
            <Gear size={22} />
          </Button>
        }
      />
      <DialogContent className="w-[min(96vw,44rem)]">
        <DialogHeader>
          <DialogTitle>Configurações do documento</DialogTitle>
          <DialogDescription>
            Margens da folha A4, tipografia do corpo e formatação dos
            cabeçalhos. As mesmas regras valem para a tela e para o PDF.
          </DialogDescription>
        </DialogHeader>
        <div className="max-h-[60vh] space-y-6 overflow-y-auto pr-1">
          <section className="space-y-3">
            <h3 className="text-sm font-medium">Margens (mm)</h3>
            <div className="grid grid-cols-1 gap-3 sm:grid-cols-2">
              {DOCUMENT_MARGIN_SIDES.map((side) => (
                <label
                  key={side}
                  className="flex items-center justify-between gap-2 text-sm"
                >
                  {DOCUMENT_MARGIN_LABELS[side]}
                  <Input
                    type="number"
                    className="w-24"
                    min={DOCUMENT_MARGIN_LIMITS_MILLIMETERS.minimum}
                    max={DOCUMENT_MARGIN_LIMITS_MILLIMETERS.maximum}
                    value={marginsDraft[side]}
                    onChange={(evento) =>
                      updateMargin(side, Number(evento.target.value))
                    }
                  />
                </label>
              ))}
            </div>
          </section>
        </div>
        <DialogFooter>
          <Button variant="outline" onClick={() => definirDialogoAberto(false)}>
            Cancelar
          </Button>
          <Button onClick={applySettings}>Aplicar</Button>
        </DialogFooter>
      </DialogContent>
    </Dialog>
  );
}
