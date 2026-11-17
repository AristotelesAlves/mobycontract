"use client";

import { ArrowLeft, ArrowClockwise, Briefcase, CheckCircle, CurrencyDollar, DotsThree, EnvelopeSimple, Eye, FileText, HouseLine, IdentificationCard, MagnifyingGlass, MapPin, PencilSimple, Phone, Plus, User, WarningCircle } from "@phosphor-icons/react";
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
  Card,
  CardContent,
  CardDescription,
  CardHeader,
  CardTitle,
} from "@/components/ui/card";
import {
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from "@/components/ui/table";
import { Tabs, TabsContent } from "@/components/ui/tabs";
import { PageTabsList, PageTabsTrigger } from "@/components/ui/page-tabs";
import { TablePagination } from "@/components/ui/table-pagination";
import { Badge } from "@/components/ui/badge";
import { Skeleton } from "@/components/ui/skeleton";
import { formatCurrency, formatDate } from "@/lib/formatters";
import { useRouter } from "next/navigation";
import { useTenantDetail } from "../hooks/use-tenant-detail";

export function InquilinoDetalhe({ idInicial }: { idInicial: string }) {
  const router = useRouter();
  const detail = useTenantDetail(idInicial);
  const { tenant: inquilino } = detail;
  const tenantContracts = detail.allContracts;
  const tenantInstallments = detail.allInstallments;
  const activeContractCount =
    detail.indicators?.contratosVigentes ??
    tenantContracts.filter(
      (contract) => contract.status === "Validado" || contract.status === "Vigente",
    ).length;
  const closedContractCount =
    detail.indicators?.contratosEncerrados ??
    tenantContracts.filter(
      (contract) => contract.status === "Cancelado" || contract.status === "Encerrado",
    ).length;
  const totalInstallments = tenantInstallments.length;
  const idade = detail.age;

  if (!inquilino) {
    return (
      <div className="flex flex-col gap-4">
        <div className="flex items-center gap-2">
          <Button variant="ghost" size="sm" onClick={() => router.push("/inquilinos")}>
            <ArrowLeft size={16} /> Voltar para Inquilinos
          </Button>
        </div>
        <Card>
          <CardContent className="py-12 flex flex-col items-center gap-3 text-center">
            {detail.error ? (
              <>
                <p className="text-sm text-destructive">{detail.error}</p>
                <Button variant="outline" size="sm" className="gap-2" onClick={detail.retry}>
                  <ArrowClockwise size={14} /> Tentar novamente
                </Button>
              </>
            ) : (
              <Skeleton className="h-5 w-64" />
            )}
          </CardContent>
        </Card>
      </div>
    );
  }

  return (
    <div className="flex flex-col gap-6">
      <div className="flex flex-col gap-3">
        <div className="flex items-center gap-2 text-xs text-muted-foreground">
          <span
            className="cursor-pointer hover:underline text-foreground/80"
            onClick={() => router.push("/inquilinos")}
          >
            Inquilinos
          </span>
          <span>/</span>
          <span className="text-foreground font-medium">{inquilino.nome}</span>
        </div>

        <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
          <div className="flex items-center gap-4">
            <div className="w-14 h-14 rounded-2xl bg-primary/10 border border-primary/20 flex items-center justify-center text-primary font-bold text-xl shrink-0">
              {inquilino.nome.split(" ").slice(0, 2).map((n) => n[0]).join("")}
            </div>
            <div>
              <div className="flex flex-wrap items-center gap-2">
                <h1 className="text-2xl font-bold tracking-tight">{inquilino.nome}</h1>
                {detail.hasActiveContract ? (
                  <Badge variant="default" className="bg-emerald-600 hover:bg-emerald-600 gap-1">
                    <CheckCircle size={13} weight="bold" /> Contrato Ativo
                  </Badge>
                ) : (
                  <Badge variant="secondary">Sem Contrato Ativo</Badge>
                )}
              </div>
              <p className="text-sm text-muted-foreground mt-0.5">
                Cadastrado em {formatDate(inquilino.dataCadastro ?? "")} • CPF {inquilino.cpf}
              </p>
            </div>
          </div>

          <div className="flex items-center gap-2">
            <Button
              variant="outline"
              className="gap-2"
              onClick={() => router.push(`/inquilinos/${inquilino.id}/editar`)}
            >
              <PencilSimple size={16} />
              Editar
            </Button>
            <Button
              className="gap-2"
              onClick={() => router.push(`/contratos/novo?inquilinoId=${inquilino.id}`)}
            >
              <Plus size={16} />
              Novo Contrato
            </Button>
          </div>
        </div>
      </div>
      <Tabs defaultValue="geral" className="w-full">
        <PageTabsList>
          <PageTabsTrigger value="geral"><User size={15} /> Visão Geral</PageTabsTrigger>
          <PageTabsTrigger value="contratos"><FileText size={15} /> Contratos</PageTabsTrigger>
          <PageTabsTrigger value="financeiro"><CurrencyDollar size={15} /> Financeiro</PageTabsTrigger>
        </PageTabsList>

        <TabsContent value="geral" className="space-y-6">
          <div className="grid grid-cols-2 md:grid-cols-4 gap-4">
            <Card className="p-4 shadow-sm">
              <div className="flex items-center justify-between">
                <p className="text-xs font-semibold text-muted-foreground uppercase">Contratos</p>
                <FileText size={18} className="text-primary" />
              </div>
              <p className="text-2xl font-bold mt-2">{tenantContracts.length}</p>
              <p className="text-xs text-muted-foreground mt-1">
                {detail.activeContract ? "1 locação vigente" : "Nenhum contrato ativo"}
              </p>
            </Card>

            <Card className="p-4 shadow-sm">
              <div className="flex items-center justify-between">
                <p className="text-xs font-semibold text-muted-foreground uppercase">Imóvel Atual</p>
                <HouseLine size={18} className="text-primary" />
              </div>
              <p className="text-base font-bold mt-2 truncate">
                {detail.activeContract?.imovelTitulo ?? "Nenhum"}
              </p>
              <p className="text-xs text-muted-foreground mt-1 truncate">
                {detail.activeContract?.imovelEndereco || "Disponível para locação"}
              </p>
            </Card>

            <Card className="p-4 shadow-sm">
              <div className="flex items-center justify-between">
                <p className="text-xs font-semibold text-muted-foreground uppercase">Renda Comprovada</p>
                <CurrencyDollar size={18} className="text-emerald-600" />
              </div>
              <p className="text-2xl font-bold mt-2 text-emerald-600">
                {formatCurrency(inquilino.renda ?? 0)}
              </p>
              <p className="text-xs text-muted-foreground mt-1">
                {inquilino.profissao || "Renda declarada"}
              </p>
            </Card>

            <Card className="p-4 shadow-sm">
              <div className="flex items-center justify-between">
                <p className="text-xs font-semibold text-muted-foreground uppercase">Comprometimento</p>
                {detail.commitmentAlert ? (
                  <WarningCircle size={18} className="text-amber-500" />
                ) : (
                  <CheckCircle size={18} className="text-emerald-600" />
                )}
              </div>
              <p className="text-2xl font-bold mt-2">
                {detail.commitmentRate != null ? `${detail.commitmentRate.toFixed(1)}%` : "0%"}
              </p>
              <p className="text-xs text-muted-foreground mt-1">
                {detail.activeContract
                  ? `${formatCurrency(detail.activeContract.valorMensal ?? detail.activeContract.valorAluguel)} / mês`
                  : "Sem encargos ativos"}
              </p>
            </Card>
          </div>

          <div className="grid gap-4 md:grid-cols-3">
            <Card className="shadow-sm">
              <CardHeader className="pb-3 border-b">
                <CardTitle className="text-base flex items-center gap-2">
                  <IdentificationCard size={18} className="text-primary" />
                  Dados Pessoais
                </CardTitle>
                <CardDescription>Informações civis e documentais</CardDescription>
              </CardHeader>
              <CardContent className="space-y-3 pt-4 text-sm">
                <div>
                  <p className="text-xs text-muted-foreground uppercase font-semibold">Nome Completo</p>
                  <p className="font-medium mt-0.5">{inquilino.nome}</p>
                </div>
                <div className="grid grid-cols-2 gap-2">
                  <div>
                    <p className="text-xs text-muted-foreground uppercase font-semibold">CPF</p>
                    <p className="font-medium mt-0.5">{inquilino.cpf}</p>
                  </div>
                  <div>
                    <p className="text-xs text-muted-foreground uppercase font-semibold">RG</p>
                    <p className="font-medium mt-0.5">{inquilino.rg || "Não informado"}</p>
                  </div>
                </div>
                <div className="grid grid-cols-2 gap-2">
                  <div>
                    <p className="text-xs text-muted-foreground uppercase font-semibold">Nascimento</p>
                    <p className="font-medium mt-0.5">
                      {formatDate(inquilino.dataNascimento ?? "")}
                      {idade !== null && ` (${idade} anos)`}
                    </p>
                  </div>
                  <div>
                    <p className="text-xs text-muted-foreground uppercase font-semibold">Estado Civil</p>
                    <p className="font-medium mt-0.5">{inquilino.estadoCivil || "Não informado"}</p>
                  </div>
                </div>
                <div>
                  <p className="text-xs text-muted-foreground uppercase font-semibold">Data de Cadastro</p>
                  <p className="font-medium mt-0.5">{formatDate(inquilino.dataCadastro ?? "")}</p>
                </div>
              </CardContent>
            </Card>

            <Card className="shadow-sm">
              <CardHeader className="pb-3 border-b">
                <CardTitle className="text-base flex items-center gap-2">
                  <Phone size={18} className="text-primary" />
                  Contato & Residência
                </CardTitle>
                <CardDescription>Canais de comunicação e endereço</CardDescription>
              </CardHeader>
              <CardContent className="space-y-3 pt-4 text-sm">
                <div>
                  <p className="text-xs text-muted-foreground uppercase font-semibold">Telefone / WhatsApp</p>
                  <p className="font-medium mt-0.5 flex items-center gap-1.5">
                    <Phone size={14} className="text-emerald-600" />
                    {inquilino.telefone}
                  </p>
                </div>
                <div>
                  <p className="text-xs text-muted-foreground uppercase font-semibold">E-mail</p>
                  <p className="font-medium mt-0.5 flex items-center gap-1.5 break-all">
                    <EnvelopeSimple size={14} className="text-blue-500" />
                    {inquilino.email}
                  </p>
                </div>
                <div>
                  <p className="text-xs text-muted-foreground uppercase font-semibold">Endereço Residencial</p>
                  <p className="font-medium mt-0.5 flex items-start gap-1.5">
                    <MapPin size={16} className="text-red-500 shrink-0 mt-0.5" />
                    <span>{inquilino.endereco || "Não informado"}</span>
                  </p>
                </div>
              </CardContent>
            </Card>

            <Card className="shadow-sm">
              <CardHeader className="pb-3 border-b">
                <CardTitle className="text-base flex items-center gap-2">
                  <Briefcase size={18} className="text-primary" />
                  Profissional & Renda
                </CardTitle>
                <CardDescription>Capacidade financeira comprovada</CardDescription>
              </CardHeader>
              <CardContent className="space-y-3 pt-4 text-sm">
                <div>
                  <p className="text-xs text-muted-foreground uppercase font-semibold">Profissão / Ocupação</p>
                  <p className="font-medium mt-0.5">{inquilino.profissao || "Não informada"}</p>
                </div>
                <div>
                  <p className="text-xs text-muted-foreground uppercase font-semibold">Renda Mensal Declarada</p>
                  <p className="font-bold text-base text-emerald-600 mt-0.5">
                    {formatCurrency(inquilino.renda ?? 0)}
                  </p>
                </div>
                <div>
                  <p className="text-xs text-muted-foreground uppercase font-semibold">Limite Seguro Sugerido (30%)</p>
                  <p className="font-medium mt-0.5 text-muted-foreground">
                    {formatCurrency((inquilino.renda ?? 0) * 0.3)} / mês
                  </p>
                </div>
              </CardContent>
            </Card>
          </div>
        </TabsContent>

        <TabsContent value="contratos" className="space-y-4">
          <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-2">
            <div className="relative w-full sm:max-w-md">
              <MagnifyingGlass className="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
              <Input
                placeholder="Buscar contratos por número ou imóvel..."
                className="pl-9"
                value={detail.contractSearch}
                onChange={(event) => detail.changeContractSearch(event.target.value)}
              />
            </div>

            <div className="flex items-center gap-2 flex-wrap">
              <Button
                size="sm"
                variant={detail.contractFilter === "todos" ? "default" : "outline"}
                onClick={() => detail.changeContractFilter("todos")}
                className="text-xs h-8"
              >
                Todos ({tenantContracts.length})
              </Button>
              <Button
                size="sm"
                variant={detail.contractFilter === "vigentes" ? "default" : "outline"}
                onClick={() => detail.changeContractFilter("vigentes")}
                className="text-xs h-8"
              >
                Vigentes ({activeContractCount})
              </Button>
              <Button
                size="sm"
                variant={detail.contractFilter === "encerrados" ? "default" : "outline"}
                onClick={() => detail.changeContractFilter("encerrados")}
                className="text-xs h-8"
              >
                Encerrados ({closedContractCount})
              </Button>
            </div>
          </div>

          <div className="rounded-xl border border-border bg-card shadow-sm overflow-hidden">
          <div className="w-full overflow-x-auto">

            <Table className="w-full">
              <TableHeader>
                <TableRow>
                  <TableHead>Contrato</TableHead>
                  <TableHead>Imóvel</TableHead>
                  <TableHead>Vigência</TableHead>
                  <TableHead className="text-right">Aluguel Base</TableHead>
                  <TableHead>Garantia</TableHead>
                  <TableHead className="text-center">Status</TableHead>
                  <TableHead className="text-right">Ações</TableHead>
                </TableRow>
              </TableHeader>
              <TableBody>
                {detail.contracts.length === 0 ? (
                  <TableRow>
                    <TableCell colSpan={7} className="text-center py-8 text-muted-foreground">
                      Nenhum contrato encontrado para os filtros selecionados.
                    </TableCell>
                  </TableRow>
                ) : (
                  detail.contracts.map((contrato) => (
                    <TableRow key={contrato.id}>
                      <TableCell className="font-semibold whitespace-nowrap">
                        #{contrato.numero}
                      </TableCell>
                      <TableCell>
                        <div className="flex flex-col">
                          <span className="font-medium text-foreground">{contrato.imovelTitulo}</span>
                          <span className="text-xs text-muted-foreground">{contrato.imovelEndereco}</span>
                        </div>
                      </TableCell>
                      <TableCell className="whitespace-nowrap text-xs">
                        {formatDate(contrato.dataInicio)} → {formatDate(contrato.dataFim)}
                      </TableCell>
                      <TableCell className="text-right font-medium whitespace-nowrap">
                        {formatCurrency(contrato.valorMensal ?? contrato.valorAluguel)}
                      </TableCell>
                      <TableCell className="text-xs text-muted-foreground">
                        {contrato.garantia || "Sem garantia"}
                      </TableCell>
                      <TableCell className="text-center">
                        <Badge
                          variant={
                            contrato.status === "Validado" || contrato.status === "Vigente"
                              ? "default"
                              : contrato.status === "Cancelado"
                              ? "destructive"
                              : "secondary"
                          }
                        >
                          {contrato.status}
                        </Badge>
                      </TableCell>
                      <TableCell className="text-right">
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
                                <Eye size={16} /> Ver Contrato
                              </DropdownMenuItem>
                              <DropdownMenuItem
                                onClick={() => router.push(`/imoveis/${contrato.imovelId}`)}
                                className="cursor-pointer gap-2"
                              >
                                <HouseLine size={16} /> Ver Imóvel
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

          {detail.filteredContractCount > 0 && (
            <div className="flex justify-between items-center p-3 border-t border-border">
              <TablePagination
                currentPage={detail.contractPage}
                totalItems={detail.filteredContractCount}
                pageSize={detail.pageSize}
                onPageChange={detail.goToContractPage}
                itemLabel="contratos"
              />
            </div>
          )}
        </div>
        </TabsContent>

        <TabsContent value="financeiro" className="space-y-4">
          {tenantInstallments.length === 0 ? (
            <Card>
              <CardContent className="py-12 text-center text-muted-foreground">
                Não há histórico financeiro para este inquilino.
              </CardContent>
            </Card>
          ) : (
            <>
              <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-2">
                <div className="relative w-full sm:max-w-md">
                  <MagnifyingGlass className="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
                  <Input
                    placeholder="Buscar parcelas por referência ou imóvel..."
                    className="pl-9"
                    value={detail.installmentSearch}
                    onChange={(event) => detail.changeInstallmentSearch(event.target.value)}
                  />
                </div>

                <div className="flex items-center gap-2 flex-wrap">
                  <Button
                    size="sm"
                    variant={detail.installmentFilter === "todos" ? "default" : "outline"}
                    onClick={() => detail.changeInstallmentFilter("todos")}
                    className="text-xs h-8"
                  >
                    Todos ({totalInstallments})
                  </Button>
                  <Button
                    size="sm"
                    variant={detail.installmentFilter === "pago" ? "default" : "outline"}
                    onClick={() => detail.changeInstallmentFilter("pago")}
                    className="text-xs h-8"
                  >
                    Pagos ({tenantInstallments.filter(p => p.status === "pago").length})
                  </Button>
                  <Button
                    size="sm"
                    variant={detail.installmentFilter === "pendente" ? "default" : "outline"}
                    onClick={() => detail.changeInstallmentFilter("pendente")}
                    className="text-xs h-8"
                  >
                    Pendentes ({tenantInstallments.filter(p => p.status === "pendente").length})
                  </Button>
                  {tenantInstallments.filter(p => p.status === "atrasado").length > 0 && (
                    <Button
                      size="sm"
                      variant={detail.installmentFilter === "atrasado" ? "default" : "outline"}
                      onClick={() => detail.changeInstallmentFilter("atrasado")}
                      className="text-xs h-8"
                    >
                      Atrasados ({tenantInstallments.filter(p => p.status === "atrasado").length})
                    </Button>
                  )}
                </div>
              </div>

              <div className="rounded-xl border border-border bg-card shadow-sm overflow-hidden">
            <div className="w-full overflow-x-auto">

                <Table className="w-full">
                  <TableHeader>
                    <TableRow>
                      <TableHead>Ref.</TableHead>
                      <TableHead>Contrato</TableHead>
                      <TableHead>Imóvel</TableHead>
                      <TableHead>Vencimento</TableHead>
                      <TableHead className="text-right">Valor</TableHead>
                      <TableHead className="text-center">Status</TableHead>
                    </TableRow>
                  </TableHeader>
                  <TableBody>
                    {detail.installments.map((parcela) => {
                      const total =
                        parcela.valorTotal ??
                        parcela.valor + (parcela.juros ?? 0) + (parcela.taxasAdicionais ?? 0);
                      return (
                        <TableRow key={parcela.id}>
                          <TableCell className="font-semibold">{parcela.numero}</TableCell>
                          <TableCell className="text-xs">#{parcela.contractNumber}</TableCell>
                          <TableCell className="text-xs truncate max-w-[200px]">{parcela.propertyTitle}</TableCell>
                          <TableCell className="text-xs">{formatDate(parcela.dataVencimento)}</TableCell>
                          <TableCell className="text-right font-medium">
                            {formatCurrency(parcela.status === "pago" ? parcela.valorPago ?? total : total)}
                          </TableCell>
                          <TableCell className="text-center">
                            <Badge
                              variant={
                                parcela.status === "pago"
                                  ? "default"
                                  : parcela.status === "atrasado"
                                  ? "destructive"
                                  : "secondary"
                              }
                            >
                              {parcela.status}
                            </Badge>
                          </TableCell>
                        </TableRow>
                      );
                    })}
                  </TableBody>
                        </Table>
            </div>

            {detail.filteredInstallmentCount > 0 && (
                <div className="flex justify-between items-center p-3 border-t border-border">
                  <TablePagination
                    currentPage={detail.installmentPage}
                    totalItems={detail.filteredInstallmentCount}
                    pageSize={detail.pageSize}
                    onPageChange={detail.goToInstallmentPage}
                    itemLabel="parcelas"
                  />
                </div>
              )}
          </div>
            </>
          )}
        </TabsContent>
      </Tabs>
    </div>
  );
}
