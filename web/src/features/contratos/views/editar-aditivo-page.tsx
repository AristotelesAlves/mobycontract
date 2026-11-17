"use client";

import { ArrowLeft, Check } from "@phosphor-icons/react";
import { Button } from "@/components/ui/button";
import {
  Card,
  CardContent,
  CardDescription,
  CardHeader,
  CardTitle,
} from "@/components/ui/card";
import { Input } from "@/components/ui/input";
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from "@/components/ui/select";
import { Badge } from "@/components/ui/badge";
import { cn } from "@/lib/utils";
import { useAddendumEditor } from "../hooks/use-addendum-editor";

interface IEditarAditivoPageProps {
  idContrato: string;
  idAditivo: string;
}

export function EditarAditivoPage({ idContrato, idAditivo }: IEditarAditivoPageProps) {
  const editor = useAddendumEditor(idContrato, idAditivo);
  const {
    contract: contrato,
    draft,
    fieldErrors,
    patchDraft,
    toggleReason,
    models: modelosAditivo,
    reasons: motivos,
    save,
    goToContracts,
  } = editor;

  if (!contrato || editor.isNotFound) {
    return (
      <div className="p-6">
        <Button
          variant="outline"
          onClick={goToContracts}
        >
          <ArrowLeft size={16} />
          Voltar
        </Button>
        <p className="text-muted-foreground mt-4 text-sm">
          Contrato ou Aditivo não encontrado.
        </p>
      </div>
    );
  }

  return (
    <div className="flex flex-col gap-4">
      <Card>
        <CardHeader>
          <CardTitle>Editar aditivo — Contrato #{contrato.numero}</CardTitle>
          <CardDescription>
            Atualize as informações deste aditivo e reavalie os draft.motivos.
          </CardDescription>
        </CardHeader>
        <CardContent className="space-y-6">
          <div className="grid gap-4 md:grid-cols-4">
            <div className="space-y-2 md:col-span-3">
              <label className="text-sm font-medium">Título *</label>
              <Input
                placeholder="Ex.: Aditivo de reajuste anual"
                value={draft.titulo}
                onChange={(e) => patchDraft("titulo", e.target.value)}
              />
              {fieldErrors.titulo && (
                <p className="text-destructive text-xs">{fieldErrors.titulo}</p>
              )}
            </div>
            <div className="space-y-2 md:col-span-1">
              <label className="text-sm font-medium">Data</label>
              <Input
                type="date"
                value={draft.data}
                onChange={(e) => patchDraft("data", e.target.value)}
              />
            </div>
          </div>

          <div className="space-y-2 flex flex-col w-full">
            <label className="text-sm font-medium">
              Modelo do documento do aditivo *
            </label>
            <Select
              value={draft.modeloId}
              onValueChange={(v) => {
                if (v) patchDraft("modeloId", v);
              }}
            >
              <SelectTrigger className="w-full">
                <SelectValue placeholder="Selecione o modelo" />
              </SelectTrigger>
              <SelectContent>
                {modelosAditivo.map((modelo) => (
                  <SelectItem key={modelo.uuid} value={modelo.uuid}>
                    {modelo.titulo} (v{modelo.versao})
                  </SelectItem>
                ))}
              </SelectContent>
            </Select>
            {fieldErrors.modelo && (
              <p className="text-destructive text-xs">{fieldErrors.modelo}</p>
            )}
          </div>

          <div className="space-y-2 rounded-md border p-4 bg-muted/10">
            <label className="text-sm font-medium">Motivos do aditivo *</label>
            <div className="flex flex-wrap gap-2 mt-2">
              {motivos.filter((motivo) => {
                if (motivo.value === "Água") {
                  return contrato?.consumoAgua !== undefined;
                }
                if (motivo.value === "Energia") {
                  return contrato?.consumoLuz !== undefined;
                }
                return true;
              }).map((motivo) => {
                const selecionado = draft.motivos.includes(motivo.value);
                return (
                  <Badge
                    key={motivo.value}
                    variant={selecionado ? "default" : "outline"}
                    className={cn(
                      "cursor-pointer select-none text-sm px-3 py-1",
                      !selecionado && "hover:bg-muted",
                    )}
                    onClick={() => toggleReason(motivo.value)}
                  >
                    {motivo.label}
                  </Badge>
                );
              })}
            </div>
            {fieldErrors.motivos && (
              <p className="text-destructive text-xs mt-2">{fieldErrors.motivos}</p>
            )}
            
            {draft.motivos.length > 0 && (
              <div className="mt-4 space-y-4 pt-4 border-t animate-in fade-in zoom-in-95 duration-200">
                <h4 className="text-sm font-semibold">Dados Específicos</h4>
                <div className="grid gap-4 md:grid-cols-2">
                  {draft.motivos.includes("Valor") && (
                    <div className="space-y-2">
                      <label className="text-sm font-medium">Novo Valor Mensal (R$)</label>
                      <Input
                        type="text" inputMode="decimal"
                        placeholder="Ex: 1500"
                        value={draft.novoValor}
                        onChange={(e) => patchDraft("novoValor", e.target.value)}
                      />
                    </div>
                  )}
                  {draft.motivos.includes("Vigência") && (
                    <div className="space-y-2">
                      <label className="text-sm font-medium">Nova Data de Término</label>
                      <Input
                        type="date"
                        value={draft.novaDataFim}
                        onChange={(e) => patchDraft("novaDataFim", e.target.value)}
                      />
                    </div>
                  )}
                  {draft.motivos.includes("Cláusula") && (
                    <div className="space-y-2 md:col-span-2">
                      <label className="text-sm font-medium">Cláusula Modificada</label>
                      <textarea
                        className="flex min-h-[60px] w-full rounded-md border border-input bg-transparent px-3 py-2 text-sm shadow-sm focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring"
                        placeholder="Ex: Alterada cláusula 5.a sobre fiadores..."
                        value={draft.clausulaModificada}
                        onChange={(e) => patchDraft("clausulaModificada", e.target.value)}
                      />
                    </div>
                  )}
                  {draft.motivos.includes("Taxa de Condomínio") && (
                    <div className="space-y-2">
                      <label className="text-sm font-medium">Novo Valor Condomínio (R$)</label>
                      <Input
                        type="text" inputMode="decimal"
                        placeholder="Ex: 500"
                        value={draft.novaTaxaCondominio}
                        onChange={(e) => patchDraft("novaTaxaCondominio", e.target.value)}
                      />
                    </div>
                  )}
                  {draft.motivos.includes("IPTU") && (
                    <div className="space-y-2">
                      <label className="text-sm font-medium">Novo Valor IPTU (R$)</label>
                      <Input
                        type="text" inputMode="decimal"
                        placeholder="Ex: 120"
                        value={draft.novoIPTU}
                        onChange={(e) => patchDraft("novoIPTU", e.target.value)}
                      />
                    </div>
                  )}
                  {draft.motivos.includes("Seguro Incêndio") && (
                    <div className="space-y-2">
                      <label className="text-sm font-medium">Novo Seguro Incêndio (R$)</label>
                      <Input
                        type="text" inputMode="decimal"
                        placeholder="Ex: 50"
                        value={draft.novoSeguroIncendio}
                        onChange={(e) => patchDraft("novoSeguroIncendio", e.target.value)}
                      />
                    </div>
                  )}
                  {draft.motivos.includes("Água") && (
                    <div className="space-y-2">
                      <label className="text-sm font-medium">Novo Valor de Água (R$)</label>
                      <Input
                        type="text" inputMode="decimal"
                        placeholder="Ex: 80"
                        value={draft.novoAgua}
                        onChange={(e) => patchDraft("novoAgua", e.target.value)}
                      />
                    </div>
                  )}
                  {draft.motivos.includes("Energia") && (
                    <div className="space-y-2">
                      <label className="text-sm font-medium">Novo Valor de Energia (R$)</label>
                      <Input
                        type="text" inputMode="decimal"
                        placeholder="Ex: 150"
                        value={draft.novoEnergia}
                        onChange={(e) => patchDraft("novoEnergia", e.target.value)}
                      />
                    </div>
                  )}
                </div>
              </div>
            )}
          </div>

          <div className="space-y-2">
            <label className="text-sm font-medium">Descrição / Justificativa</label>
            <textarea
              className="flex min-h-[80px] w-full rounded-md border border-input bg-transparent px-3 py-2 text-sm shadow-sm focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring"
              placeholder="Detalhe o que este aditivo altera ou a justificativa..."
              value={draft.descricao}
              onChange={(e) => patchDraft("descricao", e.target.value)}
            />
          </div>

          <div className="flex justify-end gap-2">
            <Button
              variant="outline"
              onClick={editor.goToContracts}
            >
              Cancelar
            </Button>
            <Button onClick={() => void save()} disabled={editor.submitting}>
              <Check size={16} />
              {editor.submitting ? "Salvando..." : "Salvar Alterações"}
            </Button>
          </div>
        </CardContent>
      </Card>
    </div>
  );
}
