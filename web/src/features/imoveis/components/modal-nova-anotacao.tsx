"use client";

import { useState } from "react";
import { Notebook } from "@phosphor-icons/react";
import { Button } from "@/components/ui/button";
import {
  Dialog,
  DialogContent,
  DialogDescription,
  DialogFooter,
  DialogHeader,
  DialogTitle,
} from "@/components/ui/dialog";
import { Textarea } from "@/components/ui/textarea";

interface IModalNovaAnotacaoProps {
  aberto: boolean;
  aoFechar: () => void;
  aoSalvar: (texto: string) => void;
}

export function ModalNovaAnotacao({ aberto, aoFechar, aoSalvar }: IModalNovaAnotacaoProps) {
  const [texto, setTexto] = useState("");
  const [erro, setErro] = useState("");

  function salvar() {
    if (!texto.trim()) {
      setErro("Escreva a anotação.");
      return;
    }
    aoSalvar(texto.trim());
    setTexto("");
    setErro("");
  }

  return (
    <Dialog open={aberto} onOpenChange={(open) => { if (!open) aoFechar(); }}>
      <DialogContent className="sm:max-w-md">
        <DialogHeader>
          <DialogTitle className="flex items-center gap-2">
            <Notebook size={18} className="text-primary" />
            Nova Anotação
          </DialogTitle>
          <DialogDescription>
            Registre uma observação sobre este imóvel para a equipe.
          </DialogDescription>
        </DialogHeader>

        <Textarea
          className="min-h-[110px] resize-none"
          placeholder="Ex.: proprietário pediu visita com rodízio de chaves..."
          value={texto}
          onChange={(event) => {
            setTexto(event.target.value);
            setErro("");
          }}
        />
        {erro && <p className="text-destructive text-xs">{erro}</p>}

        <DialogFooter>
          <Button variant="outline" onClick={aoFechar}>
            Cancelar
          </Button>
          <Button onClick={salvar}>Salvar anotação</Button>
        </DialogFooter>
      </DialogContent>
    </Dialog>
  );
}
