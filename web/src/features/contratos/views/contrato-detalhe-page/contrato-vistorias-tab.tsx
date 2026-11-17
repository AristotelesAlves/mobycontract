"use client";

import { Camera, DotsThree, Eye, FilePdf, PencilSimple, Plus } from "@phosphor-icons/react";
import { Badge } from "@/components/ui/badge";
import { Button } from "@/components/ui/button";
import {
  Card,
  CardContent,
  CardHeader,
  CardTitle,
} from "@/components/ui/card";
import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuGroup,
  DropdownMenuItem,
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
import {
  ListEmptyState,
  ListErrorState,
  TableLoadingRows,
} from "@/components/ui/table-feedback";
import { formatDate } from "@/lib/formatters";
import type { Contrato } from "../../data/contrato-types";
import { useVistorias } from "@/features/vistorias/hooks/use-vistoria-list";
import {
  VISTORIA_STATUS,
  type VistoriaStatus,
} from "@/features/vistorias/data/vistoria-types";

const COR_STATUS: Record<VistoriaStatus, "default" | "secondary" | "destructive" | "outline"> = {
  Agendada: "secondary",
  "Em andamento": "outline",
  "Concluída": "default",
  Cancelada: "destructive",
};

interface IContratoVistoriasTabProps {
  contrato: Contrato;
}

export function ContratoVistoriasTab({ contrato }: IContratoVistoriasTabProps) {
  const st = useVistorias({
    contratoId: contrato.id,
    tamanhoPagina: 5,
    contagens: true,
  });

  return (
    <Card className="mt-2">
      <CardHeader className="pb-3">
        <div className="flex items-center justify-between gap-2">
          <CardTitle className="text-base flex items-center gap-2">
            <Camera size={17} className="text-primary" />
            Vistorias do contrato {contrato.numero}
          </CardTitle>
          <Button
            size="sm"
            className="h-8 gap-2 text-xs"
            onClick={st.goToNovaVistoria}
          >
            <Plus size={14} />
            Nova vistoria
          </Button>
        </div>
        <div className="flex flex-wrap items-center gap-2 pt-1">
          <Button
            size="sm"
            variant={st.status === "todos" ? "default" : "outline"}
            className="text-xs h-7"
            onClick={() => st.changeStatus("todos")}
          >
            Todas ({st.filteredCount})
          </Button>
          {VISTORIA_STATUS.map((situacao) => {
            const quantidade =
              situacao === "Agendada"
                ? (st.counts?.agendadas ?? 0)
                : situacao === "Em andamento"
                  ? (st.counts?.emAndamento ?? 0)
                  : situacao === "Concluída"
                    ? (st.counts?.concluidas ?? 0)
                    : (st.counts?.canceladas ?? 0);
            return (
              <Button
                key={situacao}
                size="sm"
                variant={st.status === situacao ? "default" : "outline"}
                className="text-xs h-7"
                onClick={() => st.changeStatus(situacao)}
              >
                {situacao} ({quantidade})
              </Button>
            );
          })}
        </div>
      </CardHeader>
      <CardContent className="pt-0">
        <div className="rounded-xl border border-border overflow-hidden">
          <Table>
            <TableHeader>
              <TableRow>
                <TableHead>Nº</TableHead>
                <TableHead>Tipo</TableHead>
                <TableHead>Data</TableHead>
                <TableHead>Vistoriador</TableHead>
                <TableHead className="text-center">Status</TableHead>
                <TableHead className="text-right">Ações</TableHead>
              </TableRow>
            </TableHeader>
            <TableBody>
              {st.loading ? (
                <TableLoadingRows rows={3} />
              ) : st.error ? (
                <ListErrorState message={st.error} onRetry={st.retry} />
              ) : st.vistorias.length === 0 ? (
                <ListEmptyState message="Nenhuma vistoria registrada para este contrato." />
              ) : (
                st.vistorias.map((vistoria) => (
                  <TableRow
                    key={vistoria.id}
                    className="hover:bg-muted/50 transition-colors"
                  >
                    <TableCell className="font-semibold text-sm">
                      {vistoria.numero}
                    </TableCell>
                    <TableCell className="text-sm">{vistoria.tipo}</TableCell>
                    <TableCell className="text-xs">
                      {formatDate(vistoria.data)}
                    </TableCell>
                    <TableCell className="text-sm">
                      {vistoria.vistoriador ?? "—"}
                    </TableCell>
                    <TableCell className="text-center">
                      <Badge
                        variant={COR_STATUS[vistoria.status]}
                        className="text-xs"
                      >
                        {vistoria.status}
                      </Badge>
                    </TableCell>
                    <TableCell className="text-right">
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
                            <DropdownMenuItem
                              className="cursor-pointer gap-2"
                              onClick={() => st.goToVistoria(vistoria.id)}
                            >
                              <Eye size={15} /> Visualizar
                            </DropdownMenuItem>
                            <DropdownMenuItem
                              className="cursor-pointer gap-2"
                              disabled={Boolean(st.baixandoRegistro)}
                              onClick={() => st.baixarRegistro(vistoria.id)}
                            >
                              <FilePdf size={15} /> {st.baixandoRegistro === vistoria.id ? "Gerando PDF..." : "Baixar registro em PDF"}
                            </DropdownMenuItem>
                            <DropdownMenuItem
                              className="cursor-pointer gap-2"
                              onClick={() => st.goToEdicao(vistoria.id)}
                            >
                              <PencilSimple size={15} /> Editar
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
        {!st.loading && !st.error && st.filteredCount > 5 && (
          <p className="mt-2 text-xs text-muted-foreground text-center">
            {st.filteredCount} vistorias no total — veja todas no menu{" "}
            <b>Vistorias</b>.
          </p>
        )}
      </CardContent>
    </Card>
  );
}
