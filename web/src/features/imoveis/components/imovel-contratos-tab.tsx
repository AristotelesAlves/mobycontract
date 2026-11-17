import { Input } from "@/components/ui/input";
import { Button } from "@/components/ui/button";
import { Badge } from "@/components/ui/badge";
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from "@/components/ui/table";
import { TablePagination } from "@/components/ui/table-pagination";
import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuGroup,
  DropdownMenuItem,
  DropdownMenuLabel,
  DropdownMenuSeparator,
  DropdownMenuTrigger,
} from "@/components/ui/dropdown-menu";
import { MagnifyingGlass, DotsThree, Eye, FileText } from "@phosphor-icons/react";
import { formatDate, formatCurrency } from "@/lib/formatters";
import { type Contrato } from "@/features/contratos/data/contrato-types";
import type { Imovel } from "../data/imovel-types";
import type {
  PageNumberSetter,
  PropertyTabRouter,
  SearchFieldSetter,
  StatusFilterSetter,
} from "./imovel-tab-prop-types";

interface ImovelContratosTabProps {
  imovel: Imovel;
  contratosDoImovel: Contrato[];
  buscaContratos: string;
  setBuscaContratos: SearchFieldSetter;
  paginaContratos: number;
  setPaginaContratos: PageNumberSetter;
  filtroContratos: string;
  setFiltroContratos: StatusFilterSetter;
  router: PropertyTabRouter;
}

export function ImovelContratosTab({
  imovel,
  contratosDoImovel,
  buscaContratos,
  setBuscaContratos,
  paginaContratos,
  setPaginaContratos,
  filtroContratos,
  setFiltroContratos,
  router
}: ImovelContratosTabProps) {
  const filtrados = contratosDoImovel.filter((c: Contrato) => {
    if (filtroContratos === "vigentes" && c.status !== "Validado" && c.status !== "Vigente") return false;
    if (filtroContratos === "encerrados" && c.status !== "Cancelado" && c.status !== "Encerrado") return false;
    const termo = buscaContratos.trim().toLowerCase();
    if (!termo) return true;
    return c.numero.toLowerCase().includes(termo) || (c.destinacao || "").toLowerCase().includes(termo);
  });
  const paginados = filtrados.slice((paginaContratos - 1) * 5, paginaContratos * 5);

  return (
    <div className="space-y-4 mt-4 animate-in fade-in zoom-in-95 duration-200">
      <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-2">
        <div className="relative w-full sm:max-w-md">
          <MagnifyingGlass className="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
          <Input
            placeholder="Buscar contrato por número ou destinação..."
            className="pl-9"
            value={buscaContratos}
            onChange={(e) => {
              setBuscaContratos(e.target.value);
              setPaginaContratos(1);
            }}
          />
        </div>

        <div className="flex items-center gap-2 flex-wrap">
          <Button
            size="sm"
            variant={filtroContratos === "todos" ? "default" : "outline"}
            onClick={() => {
              setFiltroContratos("todos");
              setPaginaContratos(1);
            }}
            className="text-xs h-8"
          >
            Todos ({contratosDoImovel.length})
          </Button>
          <Button
            size="sm"
            variant={filtroContratos === "vigentes" ? "default" : "outline"}
            onClick={() => {
              setFiltroContratos("vigentes");
              setPaginaContratos(1);
            }}
            className="text-xs h-8"
          >
            Vigentes ({contratosDoImovel.filter((c: Contrato) => c.status === "Validado" || c.status === "Vigente").length})
          </Button>
          <Button
            size="sm"
            variant={filtroContratos === "encerrados" ? "default" : "outline"}
            onClick={() => {
              setFiltroContratos("encerrados");
              setPaginaContratos(1);
            }}
            className="text-xs h-8"
          >
            Encerrados ({contratosDoImovel.filter((c: Contrato) => c.status === "Cancelado" || c.status === "Encerrado").length})
          </Button>
        </div>
      </div>

      <div className="rounded-xl border border-border bg-card shadow-sm overflow-hidden">
        <div className="w-full overflow-x-auto">
          <Table className="w-full">
            <TableHeader>
              <TableRow>
                <TableHead>Número</TableHead>
                <TableHead>Início</TableHead>
                <TableHead>Fim</TableHead>
                <TableHead>Tipo</TableHead>
                <TableHead className="text-center">Aditivos</TableHead>
                <TableHead className="text-right">Valor Mensal</TableHead>
                <TableHead className="text-center">Status</TableHead>
                <TableHead className="text-right">Ações</TableHead>
              </TableRow>
            </TableHeader>
            <TableBody>
              {filtrados.length === 0 ? (
                <TableRow>
                  <TableCell colSpan={8} className="text-center py-12 text-muted-foreground">
                    Nenhum contrato encontrado para os filtros selecionados.
                  </TableCell>
                </TableRow>
              ) : (
                paginados.map((contrato: Contrato) => (
                  <TableRow key={contrato.id} className="hover:bg-muted/50 transition-colors">
                    <TableCell className="font-semibold text-foreground">#{contrato.numero}</TableCell>
                    <TableCell className="text-xs">{formatDate(contrato.dataInicio)}</TableCell>
                    <TableCell className="text-xs">{formatDate(contrato.dataFim)}</TableCell>
                    <TableCell>
                      <Badge variant="outline" className="font-normal text-xs">
                        {contrato.destinacao || (imovel.tipo === "Comercial" ? "Comercial" : "Residencial")}
                      </Badge>
                    </TableCell>
                    <TableCell className="text-center text-xs">{contrato.aditivos?.length || 0}</TableCell>
                    <TableCell className="text-right font-medium text-xs">{formatCurrency(contrato.valorMensal ?? 0)}</TableCell>
                    <TableCell className="text-center">
                      <Badge variant={contrato.status === "Validado" ? "default" : contrato.status === "Cancelado" ? "destructive" : "secondary"} className="text-xs">
                        {contrato.status}
                      </Badge>
                    </TableCell>
                    <TableCell className="text-right" onClick={(e) => e.stopPropagation()}>
                      <DropdownMenu>
                        <DropdownMenuTrigger
                          render={
                            <Button variant="ghost" className="h-8 w-8 p-0">
                              <span className="sr-only">Abrir menu</span>
                              <DotsThree size={20} weight="bold" />
                            </Button>
                          }
                        />
                        <DropdownMenuContent align="end">
                          <DropdownMenuGroup>
                            <DropdownMenuLabel>Ações</DropdownMenuLabel>
                            <DropdownMenuSeparator />
                            <DropdownMenuItem
                              onClick={() => router.push(`/contratos/${contrato.id}`)}
                              className="cursor-pointer gap-2"
                            >
                              <Eye size={16} /> Ver Contrato
                            </DropdownMenuItem>
                            <DropdownMenuItem
                              onClick={() => router.push(`/contratos/${contrato.id}?aba=aditivos`)}
                              className="cursor-pointer gap-2"
                            >
                              <FileText size={16} /> Ver Aditivos
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

        {filtrados.length > 0 && (
          <div className="flex justify-between items-center p-3 border-t border-border">
            <TablePagination
              currentPage={paginaContratos}
              totalItems={filtrados.length}
              pageSize={5}
              onPageChange={setPaginaContratos}
              itemLabel="contratos"
            />
          </div>
        )}
      </div>
    </div>
  );
}
