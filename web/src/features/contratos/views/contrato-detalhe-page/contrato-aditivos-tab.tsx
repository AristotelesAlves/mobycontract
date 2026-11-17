"use client";

import { useState } from "react";
import { useRouter } from "next/navigation";
import { DotsThree, FilePdf, FilePlus, MagnifyingGlass, PencilSimple } from "@phosphor-icons/react";
import { Badge } from "@/components/ui/badge";
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
import type {
  Contrato,
  ContratoAditivo,
} from "../../data/contrato-types";

interface IContratoAditivosTabProps {
  contrato: Contrato;
  aditivos: ContratoAditivo[];
}

const STATUS_VARIANTS: Record<
  string,
  "default" | "secondary" | "outline" | "destructive"
> = {
  Rascunho: "outline",
  "Pendente de assinatura": "secondary",
  Assinado: "secondary",
  Validado: "default",
  Cancelado: "destructive",
};

const ADITIVOS_POR_PAGINA = 5;

export function ContratoAditivosTab({
  contrato,
  aditivos,
}: IContratoAditivosTabProps) {
  const router = useRouter();
  const [busca, setBusca] = useState("");
  const [filtroStatus, setFiltroStatus] = useState<string>("todos");

  const totalValidados = aditivos.filter((a) => a.status === "Validado").length;
  const totalAssinados = aditivos.filter((a) => a.status === "Assinado").length;
  const totalRascunhos = aditivos.filter((a) => a.status === "Rascunho").length;

  const aditivosFiltrados = aditivos.filter((aditivo) => {
    if (filtroStatus === "Validado" && aditivo.status !== "Validado") return false;
    if (filtroStatus === "Assinado" && aditivo.status !== "Assinado") return false;
    if (filtroStatus === "Rascunho" && aditivo.status !== "Rascunho") return false;

    const termo = busca.trim().toLowerCase();
    if (!termo) return true;
    return (
      aditivo.numero.toLowerCase().includes(termo) ||
      aditivo.criadoPor.toLowerCase().includes(termo) ||
      aditivo.motivos.some((m) => m.toLowerCase().includes(termo))
    );
  });

  const [pagina, setPagina] = useState(1);
  const totalPaginas = Math.max(
    1,
    Math.ceil(aditivosFiltrados.length / ADITIVOS_POR_PAGINA),
  );
  const paginaSegura = Math.min(pagina, totalPaginas);
  const inicio = (paginaSegura - 1) * ADITIVOS_POR_PAGINA;
  const aditivosDaPagina = aditivosFiltrados.slice(
    inicio,
    inicio + ADITIVOS_POR_PAGINA,
  );

  function alterarPagina(proximaPagina: number) {
    setPagina(Math.min(Math.max(1, proximaPagina), totalPaginas));
  }

  return (
    <div className="space-y-4">
      <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-2">
        <div className="relative w-full sm:max-w-md">
          <MagnifyingGlass className="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
          <Input
            placeholder="Buscar por código ou motivo..."
            className="pl-9"
            value={busca}
            onChange={(e) => {
              setBusca(e.target.value);
              alterarPagina(1);
            }}
          />
        </div>

        <div className="flex items-center gap-2 flex-wrap">
          <Button
            size="sm"
            variant={filtroStatus === "todos" ? "default" : "outline"}
            onClick={() => {
              setFiltroStatus("todos");
              alterarPagina(1);
            }}
            className="text-xs h-8"
          >
            Todos ({aditivos.length})
          </Button>
          <Button
            size="sm"
            variant={filtroStatus === "Validado" ? "default" : "outline"}
            onClick={() => {
              setFiltroStatus("Validado");
              alterarPagina(1);
            }}
            className="text-xs h-8"
          >
            Validados ({totalValidados})
          </Button>
          {totalAssinados > 0 && (
            <Button
              size="sm"
              variant={filtroStatus === "Assinado" ? "default" : "outline"}
              onClick={() => {
                setFiltroStatus("Assinado");
                alterarPagina(1);
              }}
              className="text-xs h-8"
            >
              Assinados ({totalAssinados})
            </Button>
          )}
          {totalRascunhos > 0 && (
            <Button
              size="sm"
              variant={filtroStatus === "Rascunho" ? "default" : "outline"}
              onClick={() => {
                setFiltroStatus("Rascunho");
                alterarPagina(1);
              }}
              className="text-xs h-8"
            >
              Rascunho ({totalRascunhos})
            </Button>
          )}
          <Button
            size="sm"
            onClick={() => router.push(`/contratos/${contrato.id}/aditivos/novo`)}
            className="gap-1.5 h-8 text-xs ml-1"
          >
            <FilePlus size={15} />
            Adicionar aditivo
          </Button>
        </div>
      </div>

      <div className="rounded-xl border border-border bg-card shadow-sm overflow-hidden">
        <div className="w-full overflow-x-auto">

        <Table className="w-full">
          <TableHeader>
            <TableRow>
              <TableHead>Código</TableHead>
              <TableHead>Data</TableHead>
              <TableHead>Criado por</TableHead>
              <TableHead>Status</TableHead>
              <TableHead>Motivos</TableHead>
              <TableHead className="text-right">Ações</TableHead>
            </TableRow>
          </TableHeader>
          <TableBody>
            {aditivosFiltrados.length === 0 ? (
              <TableRow>
                <TableCell colSpan={6} className="text-center py-12 text-muted-foreground">
                  Nenhum aditivo encontrado para os filtros selecionados.
                </TableCell>
              </TableRow>
            ) : (
              aditivosDaPagina.map((aditivo) => (
                <TableRow key={aditivo.id} className="hover:bg-muted/50 transition-colors">
                  <TableCell className="font-semibold text-foreground text-xs">{aditivo.numero}</TableCell>
                  <TableCell className="text-xs">{formatDate(aditivo.data)}</TableCell>
                  <TableCell className="text-xs">{aditivo.criadoPor}</TableCell>
                  <TableCell>
                    <Badge
                      variant={STATUS_VARIANTS[aditivo.status] ?? "outline"}
                      className="text-xs"
                    >
                      {aditivo.status}
                    </Badge>
                  </TableCell>
                  <TableCell>
                    <div className="flex flex-wrap gap-1">
                      {aditivo.motivos.map((motivo) => (
                        <Badge key={motivo} variant="outline" className="text-xs">
                          {motivo}
                        </Badge>
                      ))}
                    </div>
                  </TableCell>
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
                            onClick={() =>
                              window.open(
                                `/contratos/${contrato.id}/aditivos/${aditivo.id}/visualizar`,
                                "_blank",
                              )
                            }
                            className="cursor-pointer gap-2"
                          >
                            <FilePdf size={16} /> Visualizar
                          </DropdownMenuItem>
                          {aditivo.status === "Rascunho" && (
                            <DropdownMenuItem
                              onClick={() =>
                                router.push(
                                  `/contratos/${contrato.id}/aditivos/${aditivo.id}/editar`,
                                )
                              }
                              className="cursor-pointer gap-2"
                            >
                              <PencilSimple size={16} /> Editar
                            </DropdownMenuItem>
                          )}
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

        {aditivosFiltrados.length > 0 && (
        <div className="flex justify-between items-center p-3 border-t border-border">
          <TablePagination
            currentPage={paginaSegura}
            totalItems={aditivosFiltrados.length}
            pageSize={ADITIVOS_POR_PAGINA}
            onPageChange={alterarPagina}
            itemLabel="aditivos"
          />
        </div>
      )}
      </div>
    </div>
  );
}
