"use client";

import { useState } from "react";
import { SpinnerGap } from "@phosphor-icons/react";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from "@/components/ui/dialog";
import { formatCurrency } from "@/lib/formatters";
import { parseValorPtBr } from "@/lib/numeros";
import { dataIsoValida } from "@/lib/documentos";
import { installmentTotal } from "../../use-cases/installment-list-query";
import type { ParcelaFinanceira } from "../../data/contrato-types";
import type { RegistrarPagamentoPayload } from "../../services/contract-service";

export function RegistrarPagamentoDialog({ parcela, salvando, fechar, confirmar }: {
  parcela: ParcelaFinanceira;
  salvando: boolean;
  fechar: () => void;
  confirmar: (dados: RegistrarPagamentoPayload) => Promise<void>;
}) {
  const total = installmentTotal(parcela);
  const corrigindo = parcela.status === "pago";
  const [data, setData] = useState(() => (corrigindo ? parcela.dataPagamento || parcela.pagoEm?.slice(0, 10) : "") || new Intl.DateTimeFormat("en-CA", { timeZone: "America/Fortaleza" }).format(new Date()));
  const [valor, setValor] = useState(() => (corrigindo ? parcela.valorPago ?? 0 : total).toLocaleString("pt-BR", { minimumFractionDigits: 2, useGrouping: false }));
  const recebido = parseValorPtBr(valor);
  const valido = recebido !== null && recebido >= 0 && dataIsoValida(data);

  return (
    <Dialog open onOpenChange={(open) => { if (!open && !salvando) fechar(); }}>
      <DialogContent>
        <DialogHeader>
          <DialogTitle>{corrigindo ? "Corrigir pagamento" : "Registrar pagamento"}</DialogTitle>
          <DialogDescription>Parcela {parcela.referencia ?? parcela.numero}. {corrigindo ? "Informe a data e o valor realmente recebidos. A correção ficará registrada no histórico." : "Confirme a data e o valor recebido antes de marcar a parcela como paga."}</DialogDescription>
        </DialogHeader>
        <dl className="space-y-2 rounded-lg border bg-muted/20 p-4 text-sm">
          <div className="flex justify-between gap-4"><dt>Valor base</dt><dd>{formatCurrency(parcela.valor)}</dd></div>
          <div className="flex justify-between gap-4"><dt>Juros e multas</dt><dd>{formatCurrency(parcela.juros ?? 0)}</dd></div>
          <div className="flex justify-between gap-4"><dt>Taxas</dt><dd>{formatCurrency(parcela.taxasAdicionais ?? 0)}</dd></div>
          <div className="flex justify-between gap-4 border-t pt-2 font-semibold"><dt>Total da parcela</dt><dd>{formatCurrency(total)}</dd></div>
        </dl>
        <div className="space-y-2"><label htmlFor="pagamento-data" className="text-sm font-medium">Data do pagamento *</label><Input id="pagamento-data" type="date" value={data} disabled={salvando} onChange={(e) => setData(e.target.value)} /></div>
        <div className="space-y-2"><label htmlFor="pagamento-valor" className="text-sm font-medium">Valor recebido (R$) *</label><Input id="pagamento-valor" inputMode="decimal" value={valor} disabled={salvando} onChange={(e) => setValor(e.target.value)} /></div>
        {recebido !== null && Math.round(recebido * 100) !== Math.round(total * 100) && <p role="status" className="text-sm text-muted-foreground">O valor recebido difere do total da parcela. Será registrado {formatCurrency(recebido)}, e a parcela será marcada como paga.</p>}
        <DialogFooter>
          <Button variant="outline" onClick={fechar} disabled={salvando}>Cancelar</Button>
          <Button disabled={!valido || salvando} onClick={() => { if (valido && recebido !== null) void confirmar({ dataPagamento: data, valorPago: recebido, ...(corrigindo ? { corrigir: true } : {}) }); }}>
            {salvando && <SpinnerGap size={15} className="animate-spin" />}{salvando ? "Salvando..." : corrigindo ? "Salvar correção" : "Confirmar pagamento"}
          </Button>
        </DialogFooter>
      </DialogContent>
    </Dialog>
  );
}
