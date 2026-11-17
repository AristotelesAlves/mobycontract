"use client";

import { useEffect, useState } from "react";
import Link from "next/link";
import { api } from "@/services/api/base-api";
import { getReadableErrorMessage } from "@/services/api/get-readable-error-message";
import { MagnifyingGlass } from "@phosphor-icons/react";
import { Badge } from "@/components/ui/badge";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from "@/components/ui/table";
import { TablePagination } from "@/components/ui/table-pagination";
import { formatCurrency, formatDate } from "@/lib/formatters";
import type { Contrato } from "@/features/contratos/data/contrato-types";
import type { Imovel } from "../data/imovel-types";
import type { PageNumberSetter, SearchFieldSetter, StatusFilterSetter } from "./imovel-tab-prop-types";

interface ImovelFinanceiroTabProps {
  imovel: Imovel;
  contratoAtivo: Contrato | undefined;
  buscaFinanceiro: string;
  setBuscaFinanceiro: SearchFieldSetter;
  paginaFinanceiro: number;
  setPaginaFinanceiro: PageNumberSetter;
  filtroFinanceiro: string;
  setFiltroFinanceiro: StatusFilterSetter;
}

type Parcela = { id: number; referencia: string; vencimento: string; totalDaParcela: number; status: string; valorPago: number | null };

export function ImovelFinanceiroTab({ contratoAtivo, buscaFinanceiro, setBuscaFinanceiro, paginaFinanceiro, setPaginaFinanceiro, filtroFinanceiro, setFiltroFinanceiro }: ImovelFinanceiroTabProps) {
  const [parcelas, setParcelas] = useState<Parcela[]>([]);
  const [total, setTotal] = useState(0);
  const [erro, setErro] = useState("");
  const [carregando, setCarregando] = useState(false);
  const contratoId = contratoAtivo?.id;
  useEffect(() => {
    if (!contratoId) return;
    const abort = new AbortController();
    setCarregando(true);
    setErro("");
    api.get<{ dados: { itens: Parcela[]; total: number } }>(`/contratos/${contratoId}/parcelas`, {
      query: { pagina: paginaFinanceiro, tamanhoPagina: 5, texto: buscaFinanceiro || undefined, status: filtroFinanceiro === "todos" ? undefined : filtroFinanceiro },
      signal: abort.signal,
    }).then(resposta => {
      if (!abort.signal.aborted) { setParcelas(resposta.dados.itens); setTotal(resposta.dados.total); }
    }).catch(error => { if (!abort.signal.aborted) setErro(getReadableErrorMessage(error)); })
      .finally(() => { if (!abort.signal.aborted) setCarregando(false); });
    return () => abort.abort();
  }, [contratoId, paginaFinanceiro, buscaFinanceiro, filtroFinanceiro]);
  if (!contratoAtivo) return <p className="py-8 text-muted-foreground">Nenhum contrato vigente ou validado encontrado. Consulte o histórico de contratos para acessar períodos anteriores.</p>;

  return (
    <div className="space-y-4 mt-4 animate-in fade-in zoom-in-95 duration-200">
      <div className="space-y-2 text-sm">
        <p className="text-muted-foreground">
          Condições do contrato {contratoAtivo.numero}: aluguel {formatCurrency(contratoAtivo.valorMensal ?? 0)}, desconto mensal {formatCurrency(contratoAtivo.descontoMensal ?? 0)}. Garantia: {contratoAtivo.garantia} {contratoAtivo.garantia === "Caução" ? formatCurrency(contratoAtivo.valorCaucao ?? 0) : ""}.
        </p>
        <Link className="text-primary underline underline-offset-4" href={`/contratos/${contratoAtivo.id}`}>
          Abrir contrato para pagamentos, encargos e caução
        </Link>
      </div>

      <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-2">
        <div className="relative w-full sm:max-w-md">
          <MagnifyingGlass className="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
          <Input
            aria-label="Buscar referência da parcela"
            placeholder="Buscar referência..."
            className="pl-9"
            value={buscaFinanceiro}
            onChange={(event) => {
              setBuscaFinanceiro(event.target.value);
              setPaginaFinanceiro(1);
            }}
          />
        </div>
        <div className="flex items-center gap-2 flex-wrap" role="group" aria-label="Situação da parcela">
          {[
            { valor: "todos", rotulo: "Todas" },
            { valor: "Pago", rotulo: "Pago" },
            { valor: "Pendente", rotulo: "Pendente" },
            { valor: "Atrasado", rotulo: "Atrasado" },
          ].map(({ valor, rotulo }) => (
            <Button
              key={valor}
              size="sm"
              variant={filtroFinanceiro === valor ? "default" : "outline"}
              className="text-xs h-8"
              aria-pressed={filtroFinanceiro === valor}
              onClick={() => {
                setFiltroFinanceiro(valor);
                setPaginaFinanceiro(1);
              }}
            >
              {rotulo}
            </Button>
          ))}
        </div>
      </div>

      <div className="rounded-xl border border-border bg-card shadow-sm overflow-hidden">
        <div className="w-full overflow-x-auto">
          <Table className="w-full">
            <TableHeader>
              <TableRow>
                <TableHead>Referência</TableHead>
                <TableHead>Vencimento</TableHead>
                <TableHead className="text-right">Total</TableHead>
                <TableHead className="text-right">Valor pago</TableHead>
                <TableHead className="text-center">Situação registrada</TableHead>
              </TableRow>
            </TableHeader>
            <TableBody>
              {erro || carregando ? (
                <TableRow>
                  <TableCell colSpan={5} className={`text-center py-12 ${erro ? "text-destructive" : "text-muted-foreground"}`}>
                    <p role={erro ? "alert" : "status"}>{erro || "Carregando parcelas..."}</p>
                  </TableCell>
                </TableRow>
              ) : parcelas.length === 0 ? (
                <TableRow>
                  <TableCell colSpan={5} className="text-center py-12 text-muted-foreground">
                    Nenhuma parcela encontrada.
                  </TableCell>
                </TableRow>
              ) : parcelas.map((parcela) => (
                <TableRow key={parcela.id} className="hover:bg-muted/50 transition-colors">
                  <TableCell className="font-semibold text-foreground">{parcela.referencia}</TableCell>
                  <TableCell className="text-xs">{formatDate(parcela.vencimento)}</TableCell>
                  <TableCell className="text-right font-medium text-xs">{formatCurrency(parcela.totalDaParcela)}</TableCell>
                  <TableCell className="text-right font-medium text-xs">
                    {parcela.valorPago == null ? "—" : formatCurrency(parcela.valorPago)}
                  </TableCell>
                  <TableCell className="text-center">
                    <Badge variant={parcela.status === "Pago" ? "positive" : parcela.status === "Atrasado" ? "destructive" : "secondary"} className="text-xs">
                      {parcela.status}
                    </Badge>
                  </TableCell>
                </TableRow>
              ))}
            </TableBody>
          </Table>
        </div>
        {!erro && !carregando && total > 0 && (
          <div className="flex justify-between items-center p-3 border-t border-border">
            <TablePagination
              currentPage={paginaFinanceiro}
              totalItems={total}
              pageSize={5}
              onPageChange={setPaginaFinanceiro}
              itemLabel="parcelas"
            />
          </div>
        )}
      </div>
    </div>
  );
}
