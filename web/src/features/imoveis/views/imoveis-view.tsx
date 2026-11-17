"use client";

import { useRouter } from "next/navigation";
import { DotsThree, Eye, FileText, MagnifyingGlass, PencilSimple, Plus } from "@phosphor-icons/react";
import { Badge } from "@/components/ui/badge";
import { Button } from "@/components/ui/button";
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
  Card,
  CardDescription,
  CardTitle,
} from "@/components/ui/card";
import { Input } from "@/components/ui/input";
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from "@/components/ui/table";
import { TablePagination } from "@/components/ui/table-pagination";
import {
  ListEmptyState,
  ListErrorState,
  TableLoadingRows,
} from "@/components/ui/table-feedback";
import { formatCurrency } from "@/lib/formatters";
import type { ImovelStatus } from "../data/imovel-types";
import { usePropertyList } from "../hooks/use-property-list";

const STATUS_VARIANTS: Record<ImovelStatus, "default" | "secondary" | "destructive" | "outline"> = {
  "Disponível": "default",
  "Alugado": "secondary",
  "Em negociação": "outline",
  "Vendido": "destructive",
};

export function ImoveisView() {
  const router = useRouter();
  const list = usePropertyList();

  return (
    <div className="flex flex-col gap-4">
      <Card className="p-4">
        <div className="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
          <div>
            <CardDescription>Imóveis</CardDescription>
            <CardTitle>
              Gerencie os imóveis, anotações, visitas e interessados.
            </CardTitle>
          </div>
          <Button
            className="gap-2 whitespace-nowrap"
            onClick={() => list.goToNewProperty()}
          >
            <Plus className="h-4 w-4" />
            Cadastrar Imóvel
          </Button>
        </div>
      </Card>

      <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-2">
        <div className="relative w-full sm:max-w-md">
          <MagnifyingGlass className="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
          <Input
            placeholder="Buscar por matrícula, endereço ou cidade..."
            className="pl-9"
            value={list.searchTerm}
            onChange={(event) => list.changeSearchTerm(event.target.value)}
          />
        </div>

        <div className="flex items-center gap-2 flex-wrap">
          <Button
            size="sm"
            variant={list.status === "todos" ? "default" : "outline"}
            onClick={() => list.changeStatus("todos")}
            className="text-xs h-8"
          >
            Todos ({list.filteredCount})
          </Button>
          <Button
            size="sm"
            variant={list.status === "Disponível" ? "default" : "outline"}
            onClick={() => list.changeStatus("Disponível")}
            className="text-xs h-8"
          >
            Disponíveis ({list.availableCount})
          </Button>
          <Button
            size="sm"
            variant={list.status === "Alugado" ? "default" : "outline"}
            onClick={() => list.changeStatus("Alugado")}
            className="text-xs h-8"
          >
            Alugados ({list.rentedCount})
          </Button>
          <Button
            size="sm"
            variant={list.status === "Em negociação" ? "default" : "outline"}
            onClick={() => list.changeStatus("Em negociação")}
            className="text-xs h-8"
          >
            Em negociação ({list.underNegotiationCount})
          </Button>
          <Button
            size="sm"
            variant={list.status === "Vendido" ? "default" : "outline"}
            onClick={() => list.changeStatus("Vendido")}
            className="text-xs h-8"
          >
            Vendidos ({list.soldCount})
          </Button>
        </div>
      </div>

      <div className="rounded-xl border border-border bg-card shadow-sm overflow-hidden">
        <div className="w-full overflow-x-auto">

        <Table className="w-full">
            <TableHeader>
              <TableRow>
                <TableHead>Imóvel</TableHead>
                <TableHead>Tipo</TableHead>
                <TableHead className="text-center">Quartos</TableHead>
                <TableHead className="text-center">Vagas</TableHead>
                <TableHead className="text-right">Aluguel</TableHead>
                <TableHead className="text-center">Status</TableHead>
                <TableHead className="text-right">Ações</TableHead>
              </TableRow>
            </TableHeader>
            <TableBody>
              {list.loading ? (
                <TableLoadingRows rows={5} />
              ) : list.error ? (
                <ListErrorState message={list.error} onRetry={list.retry} />
              ) : list.properties.length === 0 ? (
                <ListEmptyState message="Nenhum imóvel encontrado para os filtros selecionados." />
              ) : (
                list.properties.map((imovel) => (
                  <TableRow
                    key={imovel.id}
                    className="cursor-pointer hover:bg-muted/50 transition-colors"
                    onClick={() => list.goToProperty(imovel.id)}
                  >
                    <TableCell>
                      <div className="font-semibold text-foreground hover:underline">
                        {imovel.titulo}
                      </div>
                      <div className="text-muted-foreground text-xs">
                        {imovel.endereco.logradouro}, {imovel.endereco.numero} - {imovel.endereco.bairro}
                      </div>
                    </TableCell>
                    <TableCell className="text-xs">{imovel.tipo}</TableCell>
                    <TableCell className="text-center text-xs">{imovel.quartos}</TableCell>
                    <TableCell className="text-center text-xs">
                      {imovel.vagas}
                    </TableCell>
                    <TableCell className="text-right font-medium text-xs">
                      {formatCurrency(imovel.valorAluguel)}
                    </TableCell>
                    <TableCell className="text-center">
                      <Badge variant={STATUS_VARIANTS[imovel.status]} className="text-xs">
                        {imovel.status}
                      </Badge>
                    </TableCell>
                    <TableCell className="text-right" onClick={(e) => e.stopPropagation()}>
                      <DropdownMenu>
                        <DropdownMenuTrigger
                          render={
                            <Button variant="ghost" className="h-8 w-8 p-0">
                              <span className="sr-only">Abrir menu de ações</span>
                              <DotsThree className="h-5 w-5" weight="bold" />
                            </Button>
                          }
                        />
                        <DropdownMenuContent align="end">
                          <DropdownMenuGroup>
                            <DropdownMenuLabel>Ações</DropdownMenuLabel>
                            <DropdownMenuSeparator />
                            <DropdownMenuItem
                              onClick={() => list.goToProperty(imovel.id)}
                              className="cursor-pointer gap-2"
                            >
                              <Eye size={16} /> Visualizar Imóvel
                            </DropdownMenuItem>
                            <DropdownMenuItem
                              onClick={() => list.goToEditProperty(imovel.id)}
                              className="cursor-pointer gap-2"
                            >
                              <PencilSimple size={16} /> Editar Imóvel
                            </DropdownMenuItem>
                            <DropdownMenuItem
                              onClick={() => router.push(`/contratos/novo?imovelId=${imovel.id}`)}
                              className="cursor-pointer gap-2"
                            >
                              <FileText size={16} /> Novo Contrato
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

        {list.filteredCount > 0 && (
        <div className="flex justify-between items-center p-3 border-t border-border">
          <TablePagination
            currentPage={list.page}
            totalItems={list.filteredCount}
            pageSize={list.pageSize}
            onPageChange={list.goToPage}
            itemLabel="imóveis"
          />
        </div>
      )}
      </div>
    </div>
  );
}
