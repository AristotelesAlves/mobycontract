import { useState, useEffect } from "react";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { parseValorPtBr } from "@/lib/numeros";
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from "@/components/ui/select";
import {
  Dialog,
  DialogContent,
  DialogDescription,
  DialogFooter,
  DialogHeader,
  DialogTitle,
} from "@/components/ui/dialog";
import type { ChargeEntry } from "../../use-cases/installment-list-query";

interface LancarEncargoDialogProps {
  aberto: boolean;
  onOpenChange: (open: boolean) => void;
  onConfirmar: (encargo: ChargeEntry) => void;
}

const TIPOS_ENCARGO: Array<{ value: ChargeEntry["tipo"]; label: string }> = [
  { value: "multa", label: "Multa" },
  { value: "juros", label: "Juros" },
  { value: "taxa", label: "Taxa" },
];

export function LancarEncargoDialog({
  aberto,
  onOpenChange,
  onConfirmar,
}: LancarEncargoDialogProps) {
  const [tipo, setTipo] = useState<ChargeEntry["tipo"]>("multa");
  const [valorEncargo, setValorEncargo] = useState("");
  const [motivo, setMotivo] = useState("");

  useEffect(() => {
    if (aberto) {
      setTipo("multa");
      setValorEncargo("");
      setMotivo("");
    }
  }, [aberto]);

  function handleConfirmar() {
    const valor = parseValorPtBr(valorEncargo);
    if (valor === null || valor <= 0) return;
    if (!motivo.trim()) return;

    onConfirmar({ tipo, valor, motivo: motivo.trim() });
    onOpenChange(false);
  }

  const invalido = !motivo.trim() || ((parseValorPtBr(valorEncargo) ?? 0) <= 0);

  return (
    <Dialog open={aberto} onOpenChange={onOpenChange}>
      <DialogContent>
        <DialogHeader>
          <DialogTitle>Lançar Encargo</DialogTitle>
          <DialogDescription>
            Adicione multa, juros ou taxa à parcela pendente. O motivo é
            obrigatório para fins de auditoria.
          </DialogDescription>
        </DialogHeader>
        <div className="py-4 space-y-4">
          <div className="space-y-2">
            <label className="text-sm font-medium">Tipo do encargo</label>
            <Select
              value={tipo}
              onValueChange={(v: string | null) => {
                if (v === "multa" || v === "juros" || v === "taxa") setTipo(v);
              }}
            >
              <SelectTrigger className="w-full">
                <SelectValue placeholder="Selecione o tipo" />
              </SelectTrigger>
              <SelectContent>
                {TIPOS_ENCARGO.map((opcao) => (
                  <SelectItem key={opcao.value} value={opcao.value}>
                    {opcao.label}
                  </SelectItem>
                ))}
              </SelectContent>
            </Select>
          </div>
          <div className="space-y-2">
            <label className="text-sm font-medium">Valor (R$)</label>
            <Input
              type="text"
              inputMode="decimal"
              min="0"
              placeholder="Ex.: 50,00"
              value={valorEncargo}
              onChange={(e) => setValorEncargo(e.target.value)}
            />
          </div>
          <div className="space-y-2">
            <label className="text-sm font-medium">Motivo *</label>
            <Input
              placeholder="Ex: Multa por atraso da parcela 3"
              value={motivo}
              onChange={(e) => setMotivo(e.target.value)}
            />
          </div>
        </div>
        <DialogFooter>
          <Button variant="outline" onClick={() => onOpenChange(false)}>
            Cancelar
          </Button>
          <Button onClick={handleConfirmar} disabled={invalido}>
            Adicionar
          </Button>
        </DialogFooter>
      </DialogContent>
    </Dialog>
  );
}
