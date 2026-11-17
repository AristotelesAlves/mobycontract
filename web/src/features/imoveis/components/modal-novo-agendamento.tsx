"use client";

import { useState } from "react";
import { CalendarPlus } from "@phosphor-icons/react";
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

interface IModalNovoAgendamentoProps {
  aberto: boolean;
  aoFechar: () => void;
  aoSalvar: (dados: {
    data: string;
    hora: string;
    interessado: string;
    telefone?: string;
  }) => void;
}

export function ModalNovoAgendamento({ aberto, aoFechar, aoSalvar }: IModalNovoAgendamentoProps) {
  const [data, setData] = useState("");
  const [hora, setHora] = useState("");
  const [interessado, setInteressado] = useState("");
  const [telefone, setTelefone] = useState("");
  const [erro, setErro] = useState("");

  function salvar() {
    if (!data.trim()) {
      setErro("Selecione a data da visita.");
      return;
    }
    if (!hora.trim()) {
      setErro("Informe a hora da visita.");
      return;
    }
    if (!interessado.trim()) {
      setErro("Informe o nome do interessado.");
      return;
    }
    aoSalvar({
      data,
      hora,
      interessado: interessado.trim(),
      telefone: telefone.trim() || undefined,
    });
    setData("");
    setHora("");
    setInteressado("");
    setTelefone("");
    setErro("");
  }

  return (
    <Dialog open={aberto} onOpenChange={(abertoNova) => { if (!abertoNova) aoFechar(); }}>
      <DialogContent className="sm:max-w-md">
        <DialogHeader>
          <DialogTitle className="flex items-center gap-2">
            <CalendarPlus size={18} className="text-primary" />
            Agendar visita
          </DialogTitle>
          <DialogDescription>
            Registre a visita de um interessado para conhecer o imóvel.
          </DialogDescription>
        </DialogHeader>

        <div className="space-y-4">
          <div className="grid grid-cols-2 gap-3">
            <div className="space-y-2">
              <label className="text-sm font-medium">Data *</label>
              <Input
                type="date"
                value={data}
                onChange={(event) => {
                  setData(event.target.value);
                  setErro("");
                }}
              />
            </div>
            <div className="space-y-2">
              <label className="text-sm font-medium">Hora *</label>
              <Input
                type="time"
                value={hora}
                onChange={(event) => {
                  setHora(event.target.value);
                  setErro("");
                }}
              />
            </div>
          </div>

          <div className="space-y-2">
            <label className="text-sm font-medium">Nome do interessado *</label>
            <Input
              placeholder="Nome completo"
              value={interessado}
              onChange={(event) => {
                setInteressado(event.target.value);
                setErro("");
              }}
            />
          </div>

          <div className="space-y-2">
            <label className="text-sm font-medium">Telefone</label>
            <Input
              placeholder="(00) 00000-0000"
              value={telefone}
              onChange={(event) => setTelefone(event.target.value)}
            />
          </div>

          {erro && <p className="text-destructive text-xs">{erro}</p>}
        </div>

        <DialogFooter>
          <Button variant="outline" onClick={aoFechar}>
            Cancelar
          </Button>
          <Button onClick={salvar}>Agendar</Button>
        </DialogFooter>
      </DialogContent>
    </Dialog>
  );
}
