"use client";

import { Camera, Eye, PencilSimple, Plus, Trash } from "@phosphor-icons/react";
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
import {
  ListEmptyState,
  ListErrorState,
} from "@/components/ui/table-feedback";
import { formatDate } from "@/lib/formatters";
import type { Imovel } from "../data/imovel-types";
import { useImovelVistorias } from "../hooks/use-imovel-vistorias";
import type { VistoriaStatus } from "@/features/vistorias/data/vistoria-types";

const COR_STATUS: Record<VistoriaStatus, "default" | "secondary" | "destructive" | "outline"> = {
  Agendada: "secondary",
  "Em andamento": "outline",
  "Concluída": "default",
  Cancelada: "destructive",
};

interface IImovelVistoriasTabProps {
  imovel: Imovel;
}

export function ImovelVistoriasTab({ imovel }: IImovelVistoriasTabProps) {
  const st = useImovelVistorias(imovel.id);

  return (
    <Card className="mt-2">
      <CardHeader className="pb-3">
        <div className="flex items-center justify-between gap-2">
          <CardTitle className="text-base flex items-center gap-2">
            <Camera size={17} className="text-primary" />
            Vistorias do imóvel ({st.totalVistorias})
          </CardTitle>
        </div>
        <p className="text-xs text-muted-foreground">
          Vistorias dos contratos vinculados a este imóvel. Para criar ou editar,
          abra as vistorias de um contrato específico.
        </p>
      </CardHeader>
      <CardContent className="pt-0 space-y-3">
        <div className="flex flex-wrap items-center gap-2">
          <Button
            size="sm"
            variant={st.status === "todas" ? "default" : "outline"}
            className="text-xs h-8"
            onClick={() => st.changeStatus("todas")}
          >
            Todas ({st.totalVistorias})
          </Button>
          {st.statusDisponiveis.map((situacao) => {
            const quantidade = st.vistorias.filter(
              (vistoria) => vistoria.status === situacao,
            ).length;
            return (
              <Button
                key={situacao}
                size="sm"
                variant={st.status === situacao ? "default" : "outline"}
                className="text-xs h-8"
                onClick={() => st.changeStatus(situacao)}
              >
                {situacao} ({quantidade})
              </Button>
            );
          })}
        </div>

        <div className="rounded-xl border border-border overflow-hidden">
          <Table>
            <TableHeader>
              <TableRow>
                <TableHead>Nº</TableHead>
                <TableHead>Contrato</TableHead>
                <TableHead>Tipo</TableHead>
                <TableHead>Data</TableHead>
                <TableHead>Vistoriador</TableHead>
                <TableHead className="text-center">Status</TableHead>
                <TableHead className="text-right">Ações</TableHead>
              </TableRow>
            </TableHeader>
            <TableBody>
              {st.loading ? (
                <ListEmptyState message="Carregando vistorias..." />
              ) : st.error ? (
                <ListErrorState message={st.error} onRetry={st.retry} />
              ) : st.vistorias.length === 0 ? (
                <ListEmptyState message="Nenhuma vistoria registrada para este imóvel." />
              ) : (
                st.vistorias.map((vistoria) => (
                  <TableRow key={vistoria.id} className="hover:bg-muted/50 transition-colors">
                    <TableCell className="font-semibold text-sm">
                      {vistoria.numero}
                    </TableCell>
                    <TableCell className="text-sm">
                      {vistoria.contratoNumeroResolvido}
                    </TableCell>
                    <TableCell className="text-sm">{vistoria.tipo}</TableCell>
                    <TableCell className="text-xs">
                      {formatDate(vistoria.data)}
                    </TableCell>
                    <TableCell className="text-sm">
                      {vistoria.vistoriador ?? "—"}
                    </TableCell>
                    <TableCell className="text-center">
                      <Badge variant={COR_STATUS[vistoria.status]} className="text-xs">
                        {vistoria.status}
                      </Badge>
                    </TableCell>
                    <TableCell className="text-right">
                      <div className="flex items-center justify-end gap-1">
                        <Button
                          variant="ghost"
                          size="sm"
                          className="h-7 w-7 p-0"
                          title="Visualizar"
                          onClick={() => st.abrirVistoria(vistoria.contratoId, vistoria.id)}
                        >
                          <Eye size={14} />
                        </Button>
                        {vistoria.podeAlterar !== false && (
                          <Button
                            variant="ghost"
                            size="sm"
                            className="h-7 w-7 p-0"
                            title="Editar"
                            onClick={() => st.abrirEdicao(vistoria.contratoId, vistoria.id)}
                          >
                            <PencilSimple size={14} />
                          </Button>
                        )}
                        {vistoria.podeExcluir !== false && (
                          <Button
                            variant="ghost"
                            size="sm"
                            className="h-7 w-7 p-0 text-muted-foreground hover:text-destructive"
                            title="Excluir"
                            disabled={st.removendo === vistoria.id}
                            onClick={() =>
                              st.remover(
                                vistoria.contratoId,
                                vistoria.id,
                                vistoria.numero,
                                true,
                              )
                            }
                          >
                            <Trash size={14} />
                          </Button>
                        )}
                        <Button
                          variant="ghost"
                          size="sm"
                          className="h-7 w-7 p-0"
                          title="Vistorias do contrato"
                          onClick={() => st.irParaVistorias(vistoria.contratoId)}
                        >
                          <Plus size={14} />
                        </Button>
                      </div>
                    </TableCell>
                  </TableRow>
                ))
              )}
            </TableBody>
          </Table>
        </div>

        {st.contratos.length === 0 && !st.loading && !st.error && (
          <p className="text-xs text-muted-foreground text-center">
            Este imóvel ainda não tem contratos — vistorias pertencem a um contrato.
          </p>
        )}
      </CardContent>
    </Card>
  );
}
