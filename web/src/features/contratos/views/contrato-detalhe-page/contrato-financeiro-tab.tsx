"use client";

import { MagnifyingGlass, DotsThree } from "@phosphor-icons/react";
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
import { formatDate, formatCurrency } from "@/lib/formatters";
import type {
  ContratoDivida,
  LancarEncargoPayload,
  RegistrarPagamentoPayload,
} from "../../services/contract-service";
import type { Contrato, ParcelaFinanceira } from "../../data/contrato-types";
import { CaucaoReceiptButton } from "../../components/caucao-receipt-button";
import { LancarEncargoDialog } from "../../components/dialogs/lancar-encargo-dialog";
import { RegistrarPagamentoDialog } from "../../components/dialogs/registrar-pagamento-dialog";
import { useContractInstallmentsTab } from "../../hooks/use-contract-installments-tab";
import { ContratoDividasCard } from "./contrato-dividas-card";
import { ContratoHistoricoTable } from "./contrato-historico-table";
import { historicoEncargos } from "../../use-cases/contract-life-events";

interface IContratoFinanceiroTabProps {
  contrato: Contrato;
  baixarRecibo: (parcelaId: string) => void;
  baixandoRecibo: string;
  parcelas: ParcelaFinanceira[];
  dividas: ContratoDivida[];
  quitarDivida: (dividaId: string) => void;
  quitandoDivida: boolean;
  registrarPagamento: (
    parcelaId: string,
    dados: RegistrarPagamentoPayload,
  ) => Promise<boolean>;
  lancarEncargo: (parcelaId: string, dados: LancarEncargoPayload) => void;
}

export function ContratoFinanceiroTab({
  contrato,
  baixarRecibo,
  baixandoRecibo,
  parcelas,
  dividas,
  quitarDivida,
  quitandoDivida,
  registrarPagamento,
  lancarEncargo,
}: IContratoFinanceiroTabProps) {
  const tab = useContractInstallmentsTab(
    parcelas,
    registrarPagamento,
    (parcelaId, dados) => void lancarEncargo(parcelaId, dados),
  );

  return (
    <div className="space-y-4">
      <CaucaoReceiptButton contrato={contrato} />
      <ContratoDividasCard
        dividas={dividas}
        quitarDivida={quitarDivida}
        quitandoDivida={quitandoDivida}
      />

      <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-2">
        <div className="relative w-full sm:max-w-md">
          <MagnifyingGlass className="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
          <Input
            placeholder="Buscar por referência ou valor..."
            className="pl-9"
            value={tab.searchTerm}
            onChange={(event) => tab.changeSearchTerm(event.target.value)}
          />
        </div>

        <div className="flex items-center gap-2 flex-wrap">
          <Button
            size="sm"
            variant={tab.statusFilter === "Todos" ? "default" : "outline"}
            onClick={() => tab.changeStatusFilter("Todos")}
            className="text-xs h-8"
          >
            Todos ({tab.counts.todos})
          </Button>
          <Button
            size="sm"
            variant={tab.statusFilter === "pendente" ? "default" : "outline"}
            onClick={() => tab.changeStatusFilter("pendente")}
            className="text-xs h-8"
          >
            Pendentes ({tab.counts.pendentes})
          </Button>
          <Button
            size="sm"
            variant={tab.statusFilter === "pago" ? "default" : "outline"}
            onClick={() => tab.changeStatusFilter("pago")}
            className="text-xs h-8"
          >
            Pagos ({tab.counts.pagos})
          </Button>
          {tab.counts.atrasados > 0 && (
            <Button
              size="sm"
              variant={tab.statusFilter === "atrasado" ? "default" : "outline"}
              onClick={() => tab.changeStatusFilter("atrasado")}
              className="text-xs h-8"
            >
              Atrasados ({tab.counts.atrasados})
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
              <TableHead>Vencimento</TableHead>
              <TableHead className="text-right">Valor Base</TableHead>
              <TableHead className="text-right">Encargos</TableHead>
              <TableHead className="text-right">Total devido</TableHead>
              <TableHead className="text-right">Valor pago</TableHead>
              <TableHead className="text-center">Status</TableHead>
              <TableHead className="text-right">Ações</TableHead>
            </TableRow>
          </TableHeader>
          <TableBody>
            {tab.filteredCount === 0 ? (
              <TableRow>
                <TableCell colSpan={8} className="text-center py-12 text-muted-foreground">
                  Nenhuma parcela encontrada para os filtros selecionados.
                </TableCell>
              </TableRow>
            ) : (
              tab.installments.map((parcela) => (
                <TableRow key={parcela.id} className="hover:bg-muted/50 transition-colors">
                    <TableCell className="font-semibold text-foreground">{parcela.referencia ?? parcela.numero}</TableCell>
                    <TableCell className="text-xs">{formatDate(parcela.dataVencimento)}</TableCell>
                    <TableCell className="text-right text-xs">{formatCurrency(parcela.valor)}</TableCell>
                    <TableCell className="text-right text-xs text-destructive">
                      {(parcela.juros ?? 0) + (parcela.taxasAdicionais ?? 0) > 0
                        ? formatCurrency((parcela.juros ?? 0) + (parcela.taxasAdicionais ?? 0)) : "—"}
                    </TableCell>
                    <TableCell className="text-right text-xs font-semibold">
                      {formatCurrency(parcela.valorTotal ?? parcela.valor + (parcela.juros ?? 0) + (parcela.taxasAdicionais ?? 0))}
                    </TableCell>
                    <TableCell className="text-right text-xs font-semibold">{parcela.status === "pago" ? formatCurrency(parcela.valorPago ?? 0) : "—"}</TableCell>
                    <TableCell className="text-center">
                      <Badge
                        variant={
                          parcela.status === "pago"
                            ? "default"
                            : parcela.status === "atrasado"
                              ? "destructive"
                              : "secondary"
                        }
                        className="text-xs"
                      >
                        {parcela.status}
                      </Badge>
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
                              onClick={() => alert("Cobrança enviada por email/WhatsApp!")}
                              className="cursor-pointer gap-2"
                            >
                              Cobrar cliente
                            </DropdownMenuItem>
                            {parcela.status !== "pago" && (
                              <>
                                <DropdownMenuItem
                                  onClick={() => tab.registerPayment(parcela.id)}
                                  className="cursor-pointer gap-2"
                                >
                                  Registrar pagamento
                                </DropdownMenuItem>
                                <DropdownMenuItem
                                  onClick={() => tab.openChargeDialog(parcela.id)}
                                  className="cursor-pointer gap-2"
                                >
                                  Lançar encargo
                                </DropdownMenuItem>
                              </>
                            )}
                            {parcela.status === "atrasado" && (
                              <DropdownMenuItem
                                onClick={() => alert("Abrindo opções de renegociação...")}
                                className="cursor-pointer gap-2"
                              >
                                Renegociar dívida
                              </DropdownMenuItem>
                            )}
                            {parcela.status === "pago" && (
                              <DropdownMenuItem onClick={() => tab.registerPayment(parcela.id)} className="cursor-pointer gap-2">Corrigir pagamento</DropdownMenuItem>
                            )}
                            {parcela.status === "pago" && (
                              <DropdownMenuItem
                                onClick={() => baixarRecibo(parcela.id)}
                                disabled={Boolean(baixandoRecibo)}
                                className="cursor-pointer gap-2"
                              >
                                {baixandoRecibo === parcela.id ? "Gerando recibo..." : "Baixar recibo em PDF"}
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

        {tab.filteredCount > 0 && (
        <div className="flex justify-between items-center p-3 border-t border-border">
          <TablePagination
            currentPage={tab.page}
            totalItems={tab.filteredCount}
            pageSize={tab.pageSize}
            onPageChange={tab.changePage}
            itemLabel="parcelas"
          />
        </div>
      )}
      </div>

      <section className="space-y-4">
        <h3 className="text-lg font-semibold">Histórico de encargos</h3>
        <ContratoHistoricoTable eventos={historicoEncargos(parcelas)} />
      </section>

      {tab.paymentInstallment && <RegistrarPagamentoDialog key={tab.paymentInstallment.id} parcela={tab.paymentInstallment} salvando={tab.paymentSaving} fechar={tab.closePayment} confirmar={tab.confirmPayment} />}

      <LancarEncargoDialog
        aberto={tab.chargeDialogOpen}
        onOpenChange={tab.setChargeDialogOpen}
        onConfirmar={(encargo) => tab.launchCharge(encargo)}
      />
    </div>
  );
}
