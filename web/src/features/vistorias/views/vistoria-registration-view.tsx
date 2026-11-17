"use client";

import { useState } from "react";
import {
  ArrowLeft,
  Camera,
  Plus,
  SpinnerGap,
  Trash,
  X,
} from "@phosphor-icons/react";
import { Badge } from "@/components/ui/badge";
import { Button } from "@/components/ui/button";
import {
  Card,
  CardContent,
  CardFooter,
  CardHeader,
  CardTitle,
} from "@/components/ui/card";
import { Input } from "@/components/ui/input";
import { Textarea } from "@/components/ui/textarea";
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from "@/components/ui/select";
import {
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from "@/components/ui/table";
import { Skeleton } from "@/components/ui/skeleton";
import { useVistoriaRegistration } from "../hooks/use-vistoria-registration";
import type { VistoriaEstadoItem, VistoriaTipo } from "../data/vistoria-types";

const LABEL_TIPO: Record<VistoriaTipo, string> = {
  Entrada: "Entrada (início da locação)",
  Retirada: "Retirada (fim da locação)",
  "Periódica": "Periódica (durante o contrato)",
};

interface IVistoriaRegistrationViewProps {
  contratoId: string;
  vistoriaId?: string;
}

export function VistoriaRegistrationView({
  contratoId,
  vistoriaId,
}: IVistoriaRegistrationViewProps) {
  const st = useVistoriaRegistration({ contratoId, vistoriaId });
  const [tentouSalvar, setTentouSalvar] = useState(false);

  function handleSalvar() {
    setTentouSalvar(true);
    st.submit(() => setTentouSalvar(false));
  }

  return (
    <div className="flex flex-col gap-4">
      <div className="flex items-center gap-3">
        <Button variant="outline" size="sm" className="gap-2" onClick={st.voltar}>
          <ArrowLeft size={15} />
          Voltar
        </Button>
        <h1 className="text-xl font-semibold tracking-tight flex items-center gap-2">
          <Camera size={19} className="text-primary" />
          {st.modo === "criar" ? "Nova Vistoria" : "Editar Vistoria"}
        </h1>
      </div>

      {st.carregando ? (
        <Card>
          <CardContent className="space-y-4 py-8">
            <Skeleton className="h-6 w-48" />
            <Skeleton className="h-10 w-full" />
            <Skeleton className="h-10 w-2/3" />
          </CardContent>
        </Card>
      ) : (
        <>
          {st.bannerErro && (
            <div className="rounded-lg border border-destructive/40 bg-destructive/10 px-4 py-3 text-sm text-destructive">
              {st.bannerErro}
            </div>
          )}

          <div className="grid items-start gap-4 xl:grid-cols-5">
            <Card className="xl:col-span-3">
              <CardHeader className="pb-3">
                <div className="flex items-center justify-between gap-2">
                  <CardTitle className="text-base">Checklist da vistoria</CardTitle>
                  <div className="flex items-center gap-2">
                    <Button
                      variant="outline"
                      size="sm"
                      className="h-7 text-xs"
                      onClick={st.aplicarChecklistPadrao}
                    >
                      Checklist padrão
                    </Button>
                    <Button
                      variant="outline"
                      size="sm"
                      className="h-7 text-xs gap-1.5"
                      onClick={st.addItem}
                    >
                      <Plus size={13} />
                      Item
                    </Button>
                  </div>
                </div>
                <p className="text-xs text-muted-foreground">
                  Itens avaliados no imóvel. O relatório fica disponível para o
                  vistoriador indicado.
                </p>
              </CardHeader>
              <CardContent>
                <div className="rounded-xl border border-border overflow-hidden">
                  <Table>
                    <TableHeader>
                      <TableRow>
                        <TableHead className="w-[26%]">Ambiente</TableHead>
                        <TableHead className="w-[34%]">Item</TableHead>
                        <TableHead className="w-[18%]">Estado</TableHead>
                        <TableHead className="w-[18%]">Observação</TableHead>
                        <TableHead className="w-[4%]" />
                      </TableRow>
                    </TableHeader>
                    <TableBody>
                      {st.itens.length === 0 ? (
                        <TableRow>
                          <TableCell
                            colSpan={5}
                            className="py-8 text-center text-sm text-muted-foreground"
                          >
                            Nenhum item no checklist. Use o checklist padrão ou
                            adicione itens.
                          </TableCell>
                        </TableRow>
                      ) : (
                        st.itens.map((item, indice) => (
                          <TableRow key={indice}>
                            <TableCell>
                              <Input
                                value={item.ambiente}
                                className="h-8 text-xs"
                                placeholder="Ex.: Cozinha"
                                onChange={(evento) =>
                                  st.changeItem(indice, {
                                    ambiente: evento.target.value,
                                  })
                                }
                              />
                            </TableCell>
                            <TableCell>
                              <Input
                                value={item.item}
                                className="h-8 text-xs"
                                placeholder="Ex.: Paredes"
                                onChange={(evento) =>
                                  st.changeItem(indice, { item: evento.target.value })
                                }
                              />
                            </TableCell>
                            <TableCell>
                              <Select
                                value={item.estado}
                                onValueChange={(valor) => {
                                  if (
                                    valor === "Ótimo" ||
                                    valor === "Bom" ||
                                    valor === "Regular" ||
                                    valor === "Ruim" ||
                                    valor === "Substituir"
                                  ) {
                                    st.changeItem(indice, {
                                      estado: valor as VistoriaEstadoItem,
                                    });
                                  }
                                }}
                              >
                                <SelectTrigger className="h-8 text-xs w-full">
                                  <SelectValue />
                                </SelectTrigger>
                                <SelectContent>
                                  {st.estadosDisponiveis.map((option) => (
                                    <SelectItem key={option} value={option}>
                                      {option}
                                    </SelectItem>
                                  ))}
                                </SelectContent>
                              </Select>
                            </TableCell>
                            <TableCell>
                              <Input
                                value={item.observacao ?? ""}
                                className="h-8 text-xs"
                                placeholder="Opcional"
                                onChange={(evento) =>
                                  st.changeItem(indice, {
                                    observacao: evento.target.value,
                                  })
                                }
                              />
                            </TableCell>
                            <TableCell>
                              <Button
                                variant="ghost"
                                size="sm"
                                className="h-7 w-7 p-0 text-muted-foreground hover:text-destructive"
                                onClick={() => st.removeItem(indice)}
                              >
                                <X size={14} />
                              </Button>
                            </TableCell>
                          </TableRow>
                        ))
                      )}
                    </TableBody>
                  </Table>
                </div>
                {st.errors.itens && (
                  <p className="mt-2 text-xs text-destructive">{st.errors.itens}</p>
                )}
                {st.itens.length > 0 && (
                  <div className="mt-3 flex items-center justify-between">
                    <Badge variant="secondary" className="text-xs">
                      {st.itens.length} itens
                    </Badge>
                    <Button
                      variant="ghost"
                      size="sm"
                      className="h-7 text-xs gap-1.5 text-muted-foreground"
                      onClick={st.limparChecklist}
                    >
                      <Trash size={13} />
                      Limpar checklist
                    </Button>
                  </div>
                )}
              </CardContent>
            </Card>
            <Card className="xl:col-span-2 xl:sticky xl:top-4 xl:h-[540px] xl:max-h-[calc(100dvh-2rem)]">
              <CardHeader className="shrink-0 pb-3">
                <CardTitle className="text-base">Dados da vistoria</CardTitle>
              </CardHeader>
              <CardContent className="min-h-0 flex-1 space-y-4 overflow-y-auto">
                <div className="space-y-1.5">
                  <label className="text-sm font-medium">Tipo *</label>
                  <Select
                    value={st.formulario.tipo}
                    onValueChange={(valor) => {
                      if (
                        valor === "Entrada" ||
                        valor === "Retirada" ||
                        valor === "Periódica"
                      ) {
                        st.formulario.setTipo(valor);
                      }
                    }}
                  >
                    <SelectTrigger className="w-full">
                      <SelectValue placeholder="Selecione o tipo" />
                    </SelectTrigger>
                    <SelectContent>
                      {st.tiposDisponiveis.map((option) => (
                        <SelectItem key={option} value={option}>
                          {LABEL_TIPO[option]}
                        </SelectItem>
                      ))}
                    </SelectContent>
                  </Select>
                  {st.errors.tipo && (
                    <p className="text-xs text-destructive">{st.errors.tipo}</p>
                  )}
                </div>

                <div className="space-y-1.5">
                  <label className="text-sm font-medium">Data *</label>
                  <Input
                    type="date"
                    value={st.formulario.data}
                    onChange={(evento) => st.formulario.setData(evento.target.value)}
                  />
                  {st.errors.data && (
                    <p className="text-xs text-destructive">{st.errors.data}</p>
                  )}
                </div>

                <div className="space-y-1.5">
                  <label className="text-sm font-medium">Vistoriador</label>
                  <Input
                    placeholder="Responsável pela vistoria"
                    value={st.formulario.vistoriador}
                    onChange={(evento) =>
                      st.formulario.setVistoriador(evento.target.value)
                    }
                  />
                  {st.errors.vistoriador && (
                    <p className="text-xs text-destructive">
                      {st.errors.vistoriador}
                    </p>
                  )}
                </div>

                <div className="space-y-1.5">
                  <label className="text-sm font-medium">Observações</label>
                  <Textarea
                    className="resize-none"
                    rows={4}
                    placeholder="Comentários gerais sobre o estado do imóvel..."
                    value={st.formulario.observacoes}
                    onChange={(evento) =>
                      st.formulario.setObservacoes(evento.target.value)
                    }
                  />
                  {st.errors.observacoes && (
                    <p className="text-xs text-destructive">
                      {st.errors.observacoes}
                    </p>
                  )}
                </div>
              </CardContent>
              <CardFooter className="shrink-0 justify-end gap-2 border-t pt-4">
                <Button variant="outline" onClick={st.voltar} disabled={st.saving}>
                  Cancelar
                </Button>
                <Button
                  onClick={handleSalvar}
                  disabled={st.saving || (tentouSalvar && Object.keys(st.errors).length > 0)}
                  className="gap-2"
                >
                  {st.saving && <SpinnerGap size={15} className="animate-spin" />}
                  {st.modo === "criar" ? "Criar vistoria" : "Salvar alterações"}
                </Button>
              </CardFooter>
            </Card>
          </div>
        </>
      )}
    </div>
  );
}
