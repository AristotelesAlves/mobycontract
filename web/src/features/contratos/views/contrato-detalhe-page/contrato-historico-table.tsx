"use client";

import { useState } from "react";
import { Copy, DotsThree, MagnifyingGlass } from "@phosphor-icons/react";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuGroup,
  DropdownMenuItem,
  DropdownMenuLabel,
  DropdownMenuSeparator,
  DropdownMenuTrigger,
} from "@/components/ui/dropdown-menu";
import {
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from "@/components/ui/table";
import { TablePagination } from "@/components/ui/table-pagination";
import { formatDate } from "@/lib/formatters";
import type { ContratoHistorico } from "../../data/contrato-types";

interface IContratoHistoricoTableProps {
  eventos: ContratoHistorico[];
}

export function ContratoHistoricoTable({ eventos }: IContratoHistoricoTableProps) {
  const [filtroHistorico, setFiltroHistorico] = useState("");
  const [filtroTipo, setFiltroTipo] = useState("Todos");
  const [paginaHistorico, setPaginaHistorico] = useState(1);
  const ITENS_POR_PAGINA = 6;

  const historico = eventos;
  const tiposDeEventos = ["Todos", ...Array.from(new Set(historico.map((h) => {
    if (h.evento.startsWith("Assinatura")) return "Assinatura";
    if (h.evento.startsWith("Pagamento")) return "Pagamento";
    if (h.evento.startsWith("Encargo")) return "Encargo";
    if (h.evento.startsWith("Vistoria")) return "Vistoria";
    return h.evento;
  })))];

  function getCount(tipo: string) {
    if (tipo === "Todos") return historico.length;
    return historico.filter((h) => {
      const real = h.evento.startsWith("Assinatura") ? "Assinatura" :
                   h.evento.startsWith("Pagamento") ? "Pagamento" :
                   h.evento.startsWith("Encargo") ? "Encargo" :
                   h.evento.startsWith("Vistoria") ? "Vistoria" : h.evento;
      return real === tipo;
    }).length;
  }

  const historicoFiltrado = historico
    .slice()
    .reverse()
    .filter((item) => {
      const matchTexto = item.evento.toLowerCase().includes(filtroHistorico.toLowerCase()) ||
        (item.detalhe && item.detalhe.toLowerCase().includes(filtroHistorico.toLowerCase()));
      const tipoReal = item.evento.startsWith("Assinatura") ? "Assinatura" :
                       item.evento.startsWith("Pagamento") ? "Pagamento" :
                       item.evento.startsWith("Encargo") ? "Encargo" :
                       item.evento.startsWith("Vistoria") ? "Vistoria" : item.evento;
      const matchTipo = filtroTipo === "Todos" || tipoReal === filtroTipo;
      return matchTexto && matchTipo;
    });

  const historicoPaginado = historicoFiltrado.slice(
    (paginaHistorico - 1) * ITENS_POR_PAGINA,
    paginaHistorico * ITENS_POR_PAGINA
  );

  return (
    <div className="space-y-4">
      <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-2">
        <div className="relative w-full sm:max-w-md">
          <MagnifyingGlass className="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
          <Input
            placeholder="Buscar palavra-chave..."
            value={filtroHistorico}
            onChange={(e) => {
              setFiltroHistorico(e.target.value);
              setPaginaHistorico(1);
            }}
            className="pl-9"
          />
        </div>

        <div className="flex items-center gap-2 flex-wrap">
          {tiposDeEventos.map((tipo) => (
            <Button
              key={tipo}
              size="sm"
              variant={filtroTipo === tipo ? "default" : "outline"}
              onClick={() => {
                setFiltroTipo(tipo);
                setPaginaHistorico(1);
              }}
              className="text-xs h-8"
            >
              {tipo} ({getCount(tipo)})
            </Button>
          ))}
        </div>
      </div>

      <div className="rounded-xl border border-border bg-card shadow-sm overflow-hidden">
        <div className="w-full overflow-x-auto">

        <Table className="w-full">
          <TableHeader>
            <TableRow>
              <TableHead className="w-[180px]">Data</TableHead>
              <TableHead className="w-[220px]">Evento</TableHead>
              <TableHead>Detalhes</TableHead>
              <TableHead className="text-right w-[80px]">Ações</TableHead>
            </TableRow>
          </TableHeader>
          <TableBody>
            {historicoPaginado.length === 0 ? (
              <TableRow>
                <TableCell colSpan={4} className="text-center py-12 text-muted-foreground">
                  Nenhum registro encontrado para os filtros selecionados.
                </TableCell>
              </TableRow>
            ) : (
              historicoPaginado.map((item) => (
                <TableRow key={item.id} className="hover:bg-muted/50 transition-colors">
                  <TableCell className="font-medium text-xs whitespace-nowrap">{formatDate(item.data.slice(0, 10))}</TableCell>
                  <TableCell className="font-semibold text-foreground text-xs">{item.evento}</TableCell>
                  <TableCell className="text-xs text-muted-foreground">{item.detalhe || "—"}</TableCell>
                  <TableCell className="text-right" onClick={(e) => e.stopPropagation()}>
                    <DropdownMenu>
                      <DropdownMenuTrigger
                        render={
                          <Button variant="ghost" className="h-8 w-8 p-0">
                            <span className="sr-only">Abrir menu</span>
                            <DotsThree className="h-5 w-5" weight="bold" />
                          </Button>
                        }
                      />
                      <DropdownMenuContent align="end">
                        <DropdownMenuGroup>
                          <DropdownMenuLabel>Ações</DropdownMenuLabel>
                          <DropdownMenuSeparator />
                          <DropdownMenuItem
                            onClick={() => {
                              if (item.detalhe) {
                                navigator.clipboard.writeText(item.detalhe);
                              }
                            }}
                            className="cursor-pointer gap-2"
                          >
                            <Copy size={16} /> Copiar Detalhes
                          </DropdownMenuItem>
                        </DropdownMenuGroup>
                      </DropdownMenuContent>
                    </DropdownMenu>
                  </TableCell>
                </TableRow>
              ))
            )}
          </TableBody>
                </Table>
        </div>

        {historicoFiltrado.length > 0 && (
        <div className="flex justify-between items-center p-3 border-t border-border">
          <TablePagination
            currentPage={paginaHistorico}
            totalItems={historicoFiltrado.length}
            pageSize={ITENS_POR_PAGINA}
            onPageChange={setPaginaHistorico}
            itemLabel="atividades"
          />
        </div>
      )}
      </div>
    </div>
  );
}
