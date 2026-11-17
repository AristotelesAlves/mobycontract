"use client";

import { useRouter } from "next/navigation";
import {
  DotsThree,
  Eye,
  FileText,
  HouseLine,
  MagnifyingGlass,
  PencilSimple,
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
import { formatCurrency } from "@/lib/formatters";
import { useTenantList } from "../hooks/use-tenant-list";

export function InquilinosView() {
  const router = useRouter();
  const list = useTenantList();

  return (
    <div className="flex flex-col gap-5">
      <Card className="p-4 shadow-sm">
        <div className="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
          <div>
            <CardDescription>Gestão de Inquilinos</CardDescription>
            <CardTitle className="text-xl">
              Base de inquilinos, locações ativas e histórico contratual.
            </CardTitle>
          </div>
          <Button
            className="gap-2 whitespace-nowrap"
            onClick={() => router.push("/inquilinos/novo")}
          >
            <Plus className="h-4 w-4" />
            Cadastrar Inquilino
          </Button>
        </div>
      </Card>

      <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-2">
        <div className="relative w-full sm:max-w-md">
          <MagnifyingGlass className="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
          <Input
            placeholder="Buscar por nome, CPF, e-mail..."
            className="pl-9"
            value={list.searchTerm}
            onChange={(event) => list.changeSearchTerm(event.target.value)}
          />
        </div>

        <div className="flex items-center gap-2">
          <Button
            size="sm"
            variant={list.contractFilter === "todos" ? "default" : "outline"}
            onClick={() => list.changeContractFilter("todos")}
            className="text-xs h-8"
          >
            Todos ({list.totalTenants})
          </Button>
          <Button
            size="sm"
            variant={list.contractFilter === "com_contrato" ? "default" : "outline"}
            onClick={() => list.changeContractFilter("com_contrato")}
            className="text-xs h-8"
          >
            Com Locação ({list.tenantsWithContract})
          </Button>
          <Button
            size="sm"
            variant={list.contractFilter === "sem_contrato" ? "default" : "outline"}
            onClick={() => list.changeContractFilter("sem_contrato")}
            className="text-xs h-8"
          >
            Disponíveis ({list.tenantsWithoutContract})
          </Button>
        </div>
      </div>

      <div className="rounded-xl border border-border bg-card shadow-sm overflow-hidden">
        <div className="w-full overflow-x-auto">

        <Table className="w-full">
            <TableHeader>
              <TableRow>
                <TableHead>Inquilino</TableHead>
                <TableHead>Documentos</TableHead>
                <TableHead>Contato</TableHead>
                <TableHead>Profissão & Renda</TableHead>
                <TableHead>Locação Vigente</TableHead>
                <TableHead className="text-right">Ações</TableHead>
              </TableRow>
            </TableHeader>
            <TableBody>
              {list.loading ? (
                <TableLoadingRows rows={5} />
              ) : list.error ? (
                <ListErrorState message={list.error} onRetry={list.retry} />
              ) : list.tenants.length === 0 ? (
                <ListEmptyState message="Nenhum inquilino encontrado para os filtros selecionados." />
              ) : (
                list.tenants.map((tenant) => {
                  const iniciais = tenant.nome
                    .split(" ")
                    .slice(0, 2)
                    .map((n) => n[0])
                    .join("");

                  return (
                    <TableRow
                      key={tenant.id}
                      className="cursor-pointer hover:bg-muted/50 transition-colors"
                      onClick={() => router.push(`/inquilinos/${tenant.id}`)}
                    >
                      <TableCell>
                        <div className="flex items-center gap-3">
                          <div className="w-9 h-9 rounded-full bg-primary/10 text-primary font-semibold text-xs flex items-center justify-center shrink-0 border border-primary/20">
                            {iniciais}
                          </div>
                          <div className="flex flex-col">
                            <span className="font-semibold text-foreground hover:underline">
                              {tenant.nome}
                            </span>
                            <span className="text-xs text-muted-foreground truncate max-w-[200px]">
                              {tenant.email}
                            </span>
                          </div>
                        </div>
                      </TableCell>

                      <TableCell className="text-xs">
                        <div className="flex flex-col">
                          <span className="font-medium text-foreground">{tenant.cpf}</span>
                          <span className="text-muted-foreground">RG: {tenant.rg || "—"}</span>
                        </div>
                      </TableCell>

                      <TableCell className="text-xs">
                        <span className="font-medium">{tenant.telefone}</span>
                      </TableCell>

                      <TableCell className="text-xs">
                        <div className="flex flex-col">
                          <span className="font-medium">{tenant.profissao || "Não informada"}</span>
                          <span className="text-emerald-600 font-semibold">
                            {formatCurrency(tenant.renda ?? 0)}
                          </span>
                        </div>
                      </TableCell>

                      <TableCell>
                        {tenant.contratoAtivo?.imovelTitulo ? (
                          <div className="flex flex-col">
                            <div className="flex items-center gap-1 text-xs font-semibold text-foreground">
                              <HouseLine size={14} className="text-primary shrink-0" />
                              <span className="truncate max-w-[180px]">
                                {tenant.contratoAtivo.imovelTitulo}
                              </span>
                            </div>
                            {tenant.contratoAtivo.valorMensal != null && (
                              <span className="text-xs text-muted-foreground">
                                {formatCurrency(tenant.contratoAtivo.valorMensal)} / mês
                              </span>
                            )}
                          </div>
                        ) : (
                          <Badge variant="outline" className="text-xs text-muted-foreground font-normal">
                            Sem locação ativa
                          </Badge>
                        )}
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
                                onClick={() => router.push(`/inquilinos/${tenant.id}`)}
                                className="cursor-pointer gap-2"
                              >
                                <Eye size={16} /> Ver Detalhes
                              </DropdownMenuItem>
                              <DropdownMenuItem
                                onClick={() => router.push(`/inquilinos/${tenant.id}/editar`)}
                                className="cursor-pointer gap-2"
                              >
                                <PencilSimple size={16} /> Editar
                              </DropdownMenuItem>
                              <DropdownMenuItem
                                onClick={() => router.push(`/contratos/novo?inquilinoId=${tenant.id}`)}
                                className="cursor-pointer gap-2"
                              >
                                <FileText size={16} /> Novo Contrato
                              </DropdownMenuItem>
                            </DropdownMenuGroup>
                          </DropdownMenuContent>
                        </DropdownMenu>
                      </TableCell>
                    </TableRow>
                  );
                })
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
            itemLabel="inquilinos"
          />
        </div>
      )}
      </div>
    </div>
  );
}
