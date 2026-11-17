"use client";

import { useRouter } from "next/navigation";
import {
  ClockCounterClockwise,
  CurrencyDollar,
  DotsThree,
  Eye,
  FileText,
  MagnifyingGlass,
  Plus,
} from "@phosphor-icons/react";
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
import { SearchableSelect } from "@/components/ui/searchable-select";
import { Input } from "@/components/ui/input";
import {
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from "@/components/ui/table";
import { TablePagination } from "@/components/ui/table-pagination";
import {
  ListEmptyState,
  ListErrorState,
  TableLoadingRows,
} from "@/components/ui/table-feedback";
import { formatDate, formatCurrency } from "@/lib/formatters";
import type { ContratoStatus } from "../data/contrato-types";
import { useContractsList, type ContractsStatusFilter } from "../hooks/use-contracts-list";

const STATUS_VARIANTS: Record<
  ContratoStatus,
  "default" | "secondary" | "destructive" | "outline"
> = {
  Validado: "default",
  Vigente: "secondary",
  Encerrado: "outline",
  Pendente: "secondary",
  Minuta: "outline",
  Cancelado: "destructive",
  Rascunho: "outline",
};

const STATUS_CHIPS: Array<{ valor: ContractsStatusFilter; label: string }> = [
  { valor: "Rascunho", label: "Rascunho" },
  { valor: "Minuta", label: "Minuta" },
  { valor: "Pendente", label: "Pendente" },
  { valor: "Validado", label: "Validado" },
  { valor: "Vigente", label: "Vigente" },
  { valor: "Encerrado", label: "Encerrado" },
  { valor: "Cancelado", label: "Cancelado" },
];

export function ContractsView() {
  const router = useRouter();
  const {
    contracts,
    propertyFilter, tenantFilter, propertyOptions, tenantOptions,
    filterError, filtersLoading, handlePropertyFilter, handleTenantFilter,
    searchTerm,
    statusFilter,
    currentPage,
    itemsPerPage,
    filteredCount,
    counts,
    loading,
    error,
    retry,
    handleSearch,
    handleStatusFilter,
    setCurrentPage,
  } = useContractsList();

  return (
    <div className="flex flex-col gap-4">
      <Card className="p-4">
        <div className="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
          <div>
            <CardDescription>Contratos</CardDescription>
            <CardTitle>
              Acompanhe o status, assinaturas e o financeiro de cada contrato.
            </CardTitle>
          </div>
          <div className="flex flex-wrap gap-2">
          <Button variant="outline" onClick={() => router.push("/contratos/verificar")}>Verificar contrato</Button>
          <Button
            className="gap-2 whitespace-nowrap"
            onClick={() => router.push("/contratos/novo")}
          >
            <Plus className="h-4 w-4" />
            Criar Contrato
          </Button>
          </div>
        </div>
      </Card>

      <div className="grid gap-3 sm:grid-cols-2">
        <div className="space-y-2">
          <label className="text-sm font-medium">Imóvel</label>
          <SearchableSelect label="Filtrar por imóvel" placeholder="Buscar imóvel..." options={[{ value: "", label: "Todos os imóveis" }, ...propertyOptions]} value={propertyFilter} onValueChange={handlePropertyFilter} disabled={filtersLoading} />
        </div>
        <div className="space-y-2">
          <label className="text-sm font-medium">Locatário</label>
          <SearchableSelect label="Filtrar por locatário" placeholder="Buscar locatário..." options={[{ value: "", label: "Todos os locatários" }, ...tenantOptions]} value={tenantFilter} onValueChange={handleTenantFilter} disabled={filtersLoading} />
        </div>
      </div>
      {filterError && <div role="alert" className="text-sm text-destructive">{filterError} <Button variant="link" onClick={retry}>Tentar novamente</Button></div>}

      <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-2">
        <div className="relative w-full sm:max-w-md">
          <MagnifyingGlass className="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
          <Input
            placeholder="Buscar por número, imóvel ou inquilino..."
            className="pl-9"
            value={searchTerm}
            onChange={(e) => handleSearch(e.target.value)}
          />
        </div>

        <div className="flex items-center gap-2 flex-wrap">
          <Button
            size="sm"
            variant={statusFilter === "todos" ? "default" : "outline"}
            onClick={() => handleStatusFilter("todos")}
            className="text-xs h-8"
          >
            Todos ({filteredCount})
          </Button>
          {STATUS_CHIPS.map((chip) => (
            <Button
              key={chip.valor}
              size="sm"
              variant={statusFilter === chip.valor ? "default" : "outline"}
              onClick={() => handleStatusFilter(chip.valor)}
              className="text-xs h-8"
            >
              {chip.label} ({counts[chip.valor as ContratoStatus] ?? 0})
            </Button>
          ))}
        </div>
      </div>

      <div className="rounded-xl border border-border bg-card shadow-sm overflow-hidden">
        <div className="w-full overflow-x-auto">
          <Table className="w-full">
            <TableHeader>
              <TableRow>
                <TableHead>Número</TableHead>
                <TableHead>Imóvel</TableHead>
                <TableHead>Inquilino</TableHead>
                <TableHead className="text-right">Aluguel</TableHead>
                <TableHead className="text-center">Vigência Início</TableHead>
                <TableHead className="text-center">Vigência Fim</TableHead>
                <TableHead className="text-center">Status</TableHead>
                <TableHead className="text-right">Ações</TableHead>
              </TableRow>
            </TableHeader>
            <TableBody>
              {loading ? (
                <TableLoadingRows rows={5} />
              ) : error ? (
                <ListErrorState message={error} onRetry={retry} />
              ) : contracts.length === 0 ? (
                <ListEmptyState message="Nenhum contrato encontrado para os filtros selecionados." />
              ) : (
                contracts.map((contrato) => (
                  <TableRow
                    key={contrato.id}
                    className="cursor-pointer hover:bg-muted/50 transition-colors"
                    onClick={() => router.push(`/contratos/${contrato.id}`)}
                  >
                    <TableCell className="font-semibold text-foreground">#{contrato.numero}</TableCell>
                    <TableCell className="text-xs">{contrato.imovelTitulo ?? "—"}</TableCell>
                    <TableCell className="text-xs">{contrato.inquilinoNome ?? "—"}</TableCell>
                    <TableCell className="text-right font-medium text-xs">
                      {formatCurrency(contrato.valorMensal ?? 0)}
                    </TableCell>
                    <TableCell className="text-center text-xs">
                      {formatDate(contrato.dataInicio)}
                    </TableCell>
                    <TableCell className="text-center text-xs">
                      {formatDate(contrato.dataFim) ?? "-"}
                    </TableCell>
                    <TableCell className="text-center">
                      <Badge variant={STATUS_VARIANTS[contrato.status]} className="text-xs">
                        {contrato.status}
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
                              onClick={() => router.push(`/contratos/${contrato.id}`)}
                              className="cursor-pointer gap-2"
                            >
                              <Eye size={16} /> Visualizar Contrato
                            </DropdownMenuItem>
                            <DropdownMenuItem
                              onClick={() => router.push(`/contratos/${contrato.id}/aditivos/novo`)}
                              className="cursor-pointer gap-2"
                            >
                              <FileText size={16} /> Novo Aditivo
                            </DropdownMenuItem>
                            <DropdownMenuItem
                              onClick={() => router.push(`/contratos/${contrato.id}?aba=financeiro`)}
                              className="cursor-pointer gap-2"
                            >
                              <CurrencyDollar size={16} /> Ver Financeiro
                            </DropdownMenuItem>
                            <DropdownMenuItem
                              onClick={() => router.push(`/contratos/${contrato.id}?aba=auditoria`)}
                              className="cursor-pointer gap-2"
                            >
                              <ClockCounterClockwise size={16} /> Histórico e Auditoria
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

        {filteredCount > 0 && (
          <div className="flex justify-between items-center p-3 border-t border-border">
            <TablePagination
              currentPage={currentPage}
              totalItems={filteredCount}
              pageSize={itemsPerPage}
              onPageChange={setCurrentPage}
              itemLabel="contratos"
            />
          </div>
        )}
      </div>
    </div>
  );
}
