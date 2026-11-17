"use client";

import { Money } from "@phosphor-icons/react";
import { Badge } from "@/components/ui/badge";
import { Button } from "@/components/ui/button";
import {
  Card,
  CardContent,
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
import { formatDate, formatCurrency } from "@/lib/formatters";
import type { ContratoDivida } from "../../services/contract-service";

interface IContratoDividasCardProps {
  dividas: ContratoDivida[];
  quitarDivida: (dividaId: string) => void;
  quitandoDivida: boolean;
}

export function ContratoDividasCard({
  dividas,
  quitarDivida,
  quitandoDivida,
}: IContratoDividasCardProps) {
  const temDividaEmAberto = dividas.some(
    (divida) => divida.status === "Em aberto",
  );

  return (
    <Card>
      <CardHeader className="pb-3">
        <CardTitle className="flex items-center gap-2 text-base">
          <Money size={17} className="text-amber-500" />
          Dívidas
        </CardTitle>
        <p className="text-xs text-muted-foreground">
          Débitos do contrato registrados pela garantia do contrato. A caução é
          lançada automaticamente como dívida em aberto do locatário.
        </p>
      </CardHeader>
      <CardContent className="pt-0">
        <div className="rounded-xl border border-border overflow-hidden">
          <Table>
            <TableHeader>
              <TableRow>
                <TableHead>Descrição</TableHead>
                <TableHead className="text-right">Valor</TableHead>
                <TableHead className="text-center">Status</TableHead>
                <TableHead className="text-right">Quitada em</TableHead>
                <TableHead className="text-right">Ação</TableHead>
              </TableRow>
            </TableHeader>
            <TableBody>
              {dividas.length === 0 ? (
                <TableRow>
                  <TableCell
                    colSpan={5}
                    className="py-8 text-center text-sm text-muted-foreground"
                  >
                    Nenhuma dívida registrada para este contrato.
                  </TableCell>
                </TableRow>
              ) : (
                dividas.map((divida) => (
                  <TableRow key={divida.id}>
                    <TableCell className="text-sm">
                      {divida.descricao}
                    </TableCell>
                    <TableCell className="text-right text-sm font-medium">
                      {formatCurrency(divida.valor)}
                    </TableCell>
                    <TableCell className="text-center">
                      <Badge
                        variant={
                          divida.status === "Quitada"
                            ? "default"
                            : "destructive"
                        }
                        className="text-xs"
                      >
                        {divida.status}
                      </Badge>
                    </TableCell>
                    <TableCell className="text-right text-xs text-muted-foreground">
                      {divida.dataQuitacao ? formatDate(divida.dataQuitacao) : "—"}
                    </TableCell>
                    <TableCell className="text-right">
                      {divida.status === "Em aberto" && (
                        <Button
                          variant="outline"
                          size="sm"
                          className="h-7 text-xs gap-1.5"
                          disabled={!temDividaEmAberto || quitandoDivida}
                          onClick={() => quitarDivida(divida.id)}
                        >
                          {quitandoDivida ? "Quitando..." : "Quitar"}
                        </Button>
                      )}
                    </TableCell>
                  </TableRow>
                ))
              )}
            </TableBody>
          </Table>
        </div>
      </CardContent>
    </Card>
  );
}
