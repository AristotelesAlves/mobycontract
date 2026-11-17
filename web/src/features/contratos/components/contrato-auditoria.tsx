"use client";


import { Copy, DotsThree, MagnifyingGlass } from "@phosphor-icons/react";
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
import { Skeleton } from "@/components/ui/skeleton";
import { formatDate } from "@/lib/formatters";
import { useContractAudit } from "../hooks/use-contract-audit";

export function ContratoAuditoria({ idInicial }: { idInicial: string }) {
  const audit = useContractAudit(idInicial);
  const { contract: contrato } = audit;

  if (!contrato) {
    return (
      <div className="flex flex-col gap-4">
        <div className="space-y-4 py-8">
          <Skeleton className="h-8 w-1/3" />
          <Skeleton className="h-6 w-2/3" />
        </div>
      </div>
    );
  }


  return (
    <div className="space-y-4">
      <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-2">
        <div className="relative w-full sm:max-w-md">
          <MagnifyingGlass className="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
          <Input
            placeholder="Buscar por usuário, campo ou alteração..."
            value={audit.searchTerm}
            onChange={(event) => audit.changeSearchTerm(event.target.value)}
            className="pl-9"
          />
        </div>

        <div className="flex items-center gap-2 flex-wrap">
          {audit.actions.map((acao) => (
            <Button
              key={acao}
              size="sm"
              variant={audit.action === acao ? "default" : "outline"}
              onClick={() => audit.changeAction(acao)}
              className="text-xs h-8"
            >
              {acao} (
              {acao === "Todos"
                ? audit.totalItems
                : audit.allActionCounts[acao] ?? 0}
              )
            </Button>
          ))}
          <Badge variant="outline" className="ml-1 text-xs">
            #{contrato.numero}
          </Badge>
        </div>
      </div>

      <div className="rounded-xl border border-border bg-card shadow-sm overflow-hidden">
        <div className="w-full overflow-x-auto">

        <Table className="w-full">
          <TableHeader>
            <TableRow>
              <TableHead>Data</TableHead>
              <TableHead>Usuário</TableHead>
              <TableHead>Ação</TableHead>
              <TableHead>Campo</TableHead>
              <TableHead>De</TableHead>
              <TableHead>Para</TableHead>
              <TableHead className="text-right">Ações</TableHead>
            </TableRow>
          </TableHeader>
          <TableBody>
            {audit.changes.length === 0 ? (
              <TableRow>
                <TableCell colSpan={7} className="text-center py-12 text-muted-foreground">
                  Nenhuma alteração encontrada para os filtros selecionados.
                </TableCell>
              </TableRow>
            ) : (
              audit.changes.map((alteracao) => (
                <TableRow key={alteracao.id} className="hover:bg-muted/50 transition-colors">
                  <TableCell className="whitespace-nowrap text-xs">
                    {formatDate(alteracao.data)}
                  </TableCell>
                  <TableCell className="font-medium text-xs">{alteracao.usuario}</TableCell>
                  <TableCell>
                    <Badge variant="secondary" className="text-xs">{alteracao.acao}</Badge>
                  </TableCell>
                  <TableCell className="text-xs">{alteracao.campo}</TableCell>
                  <TableCell className="text-muted-foreground text-xs">
                    {alteracao.de}
                  </TableCell>
                  <TableCell className="font-medium text-xs">{alteracao.para}</TableCell>
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
                              navigator.clipboard.writeText(
                                `${alteracao.campo}: de "${alteracao.de}" para "${alteracao.para}" por ${alteracao.usuario}`
                              );
                            }}
                            className="cursor-pointer gap-2"
                          >
                            <Copy size={16} /> Copiar Registro
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

        {audit.totalItems > 0 && (
        <div className="flex justify-between items-center p-3 border-t border-border">
          <TablePagination
            currentPage={audit.page}
            totalItems={audit.totalItems}
            pageSize={audit.pageSize}
            onPageChange={audit.goToPage}
            itemLabel="alterações"
          />
        </div>
      )}
      </div>
    </div>
  );
}
