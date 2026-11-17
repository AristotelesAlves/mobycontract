"use client";

import { useEffect, useState } from "react";
import Image from "next/image";
import {
  ArrowClockwise,
  ArrowLeft,
  Camera,
  FilePdf,
  CheckCircle,
  ImageSquare,
  Play,
  Plus,
  SpinnerGap,
  Trash,
  XCircle,
} from "@phosphor-icons/react";
import { Badge } from "@/components/ui/badge";
import { Button } from "@/components/ui/button";
import {
  Card,
  CardContent,
  CardHeader,
  CardTitle,
} from "@/components/ui/card";
import { Input } from "@/components/ui/input";
import {
  Dialog,
  DialogContent,
  DialogDescription,
  DialogFooter,
  DialogHeader,
  DialogTitle,
} from "@/components/ui/dialog";
import { Skeleton } from "@/components/ui/skeleton";
import {
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from "@/components/ui/table";
import { VistoriaPhotoCard } from "../components/vistoria-photo-card";
import { formatDate } from "@/lib/formatters";
import { useVistoriaDetail } from "../hooks/use-vistoria-detail";
import { TRANSICOES_VISTORIA, type VistoriaStatus } from "../data/vistoria-types";

const COR_STATUS: Record<VistoriaStatus, "default" | "secondary" | "destructive" | "outline"> = {
  Agendada: "secondary",
  "Em andamento": "outline",
  "Concluída": "default",
  Cancelada: "destructive",
};

interface AcaoStatus {
  destino: VistoriaStatus;
  rotulo: string;
  icone: typeof Play;
}

const ACAOES_PRIMARIAS: Record<VistoriaStatus, AcaoStatus[]> = {
  Agendada: [
    { destino: "Em andamento", rotulo: "Iniciar vistoria", icone: Play },
    { destino: "Concluída", rotulo: "Concluir direto", icone: CheckCircle },
  ],
  "Em andamento": [
    { destino: "Concluída", rotulo: "Concluir vistoria", icone: CheckCircle },
  ],
  "Concluída": [],
  Cancelada: [],
};

interface IVistoriaDetailsViewProps {
  contratoId: string;
  vistoriaId: string;
}

export function VistoriaDetailsView({
  contratoId,
  vistoriaId,
}: IVistoriaDetailsViewProps) {
  const st = useVistoriaDetail(contratoId, vistoriaId);
  const [dialogFotoAberto, setDialogFotoAberto] = useState(false);

  if (!st.vistoria) {
    return (
      <div className="flex flex-col gap-4">
        <Button
          variant="outline"
          onClick={st.goToContrato}
          className="w-fit gap-2"
        >
          <ArrowLeft size={16} />
          Voltar
        </Button>
        <Card>
          <CardContent className="py-12 flex flex-col items-center gap-3 text-center">
            {st.error ? (
              <>
                <XCircle size={28} className="text-destructive" />
                <p className="text-sm text-destructive">{st.error}</p>
                <Button variant="outline" size="sm" className="gap-2" onClick={st.retry}>
                  <ArrowClockwise size={14} /> Tentar novamente
                </Button>
              </>
            ) : (
              <div className="space-y-4 w-full">
                <Skeleton className="h-8 w-1/3 mx-auto" />
                <Skeleton className="h-32 w-full" />
              </div>
            )}
          </CardContent>
        </Card>
      </div>
    );
  }

  const vistoria = st.vistoria;
  const podeAlterar = vistoria.podeAlterar ?? true;
  const podeExcluir = vistoria.podeExcluir ?? true;
  const acoes = ACAOES_PRIMARIAS[vistoria.status];
  const podeCancelar = TRANSICOES_VISTORIA[vistoria.status].includes("Cancelada");

  return (
    <div className="flex flex-col gap-4">
      <div className="flex flex-wrap items-center gap-3">
        <Button
          variant="outline"
          size="sm"
          className="gap-2"
          onClick={st.goToContrato}
        >
          <ArrowLeft size={15} />
          Voltar
        </Button>
        <h1 className="text-xl font-semibold tracking-tight flex items-center gap-2">
          <Camera size={19} className="text-primary" />
          Vistoria {vistoria.numero}
        </h1>
        <Badge variant={COR_STATUS[vistoria.status]} className="text-xs">
          {vistoria.status}
        </Badge>
        <Button variant="outline" size="sm" className="gap-2 sm:ml-auto" disabled={Boolean(st.working)} onClick={st.baixarRelatorio}>
          {st.working === "pdf" ? <SpinnerGap size={15} className="animate-spin" /> : <FilePdf size={15} />}
          {st.working === "pdf" ? "Gerando PDF..." : "Baixar relatório em PDF"}
        </Button>
      </div>

      <div className="grid gap-4 lg:grid-cols-3">
        <Card className="lg:col-span-2">
          <CardHeader className="pb-3">
            <CardTitle className="text-base">Resumo</CardTitle>
          </CardHeader>
          <CardContent className="space-y-5">
            <div className="grid gap-4 sm:grid-cols-2">
              <div>
                <p className="text-xs text-muted-foreground">Tipo</p>
                <p className="text-sm font-medium">{vistoria.tipo}</p>
              </div>
              <div>
                <p className="text-xs text-muted-foreground">Data</p>
                <p className="text-sm font-medium">{formatDate(vistoria.data)}</p>
              </div>
              <div>
                <p className="text-xs text-muted-foreground">Vistoriador</p>
                <p className="text-sm font-medium">{vistoria.vistoriador ?? "—"}</p>
              </div>
              <div>
                <p className="text-xs text-muted-foreground">Contrato</p>
                <p className="text-sm font-medium">
                  <a className="text-primary hover:underline" href={`/contratos/${contratoId}`}>
                    {`#${contratoId}`}
                  </a>
                </p>
              </div>
            </div>

            <div>
              <p className="text-xs text-muted-foreground">Observações</p>
              <p className="text-sm whitespace-pre-line">
                {vistoria.observacoes?.trim()
                  ? vistoria.observacoes
                  : "Sem observações registradas."}
              </p>
            </div>

            {vistoria.chaveAcesso && (
              <div className="rounded-lg border border-border bg-muted/30 px-3 py-2.5 flex items-center justify-between gap-3">
                <div className="min-w-0">
                  <p className="text-xs text-muted-foreground">
                    Chave de acesso externo do vistoriador
                  </p>
                  <p className="text-xs font-mono truncate">{vistoria.chaveAcesso}</p>
                </div>
                <Button
                  variant="outline"
                  size="sm"
                  className="h-7 text-xs shrink-0"
                  onClick={st.copiarChaveAcesso}
                >
                  Copiar
                </Button>
              </div>
            )}
          </CardContent>
        </Card>

        <Card className="h-fit">
          <CardHeader className="pb-3">
            <CardTitle className="text-base">Ações</CardTitle>
          </CardHeader>
          <CardContent className="space-y-2">
            {acoes.map((acao) => {
              const Icone = acao.icone;
              return (
                <Button
                  key={acao.destino}
                  className="w-full gap-2"
                  disabled={Boolean(st.working) || !podeAlterar}
                  title={podeAlterar ? undefined : "Status não permite alteração."}
                  onClick={() => st.changeStatus(acao.destino)}
                >
                  <Icone size={15} />
                  {acao.rotulo}
                </Button>
              );
            })}
            {podeCancelar && (
              <Button
                variant="outline"
                className="w-full gap-2 text-destructive border-destructive/40 hover:bg-destructive/10"
                disabled={Boolean(st.working) || !podeAlterar}
                title={podeAlterar ? undefined : "Status não permite alteração."}
                onClick={() => st.changeStatus("Cancelada")}
              >
                <XCircle size={15} />
                Cancelar vistoria
              </Button>
            )}
            <Button
              variant="secondary"
              className="w-full gap-2"
              disabled={Boolean(st.working) || !podeAlterar}
              title={podeAlterar ? undefined : "Status não permite edição."}
              onClick={st.irParaEdicao}
            >
              <Camera size={15} />
              Editar vistoria
            </Button>
            <Button
              variant="ghost"
              className="w-full gap-2 text-destructive hover:bg-destructive/10"
              disabled={Boolean(st.working) || !podeExcluir}
              title={podeExcluir ? undefined : "Apenas vistorias agendadas podem ser excluídas."}
              onClick={st.remover}
            >
              <Trash size={15} />
              Excluir vistoria
            </Button>
            {st.working && (
              <p className="text-xs text-muted-foreground text-center animate-pulse">
                Processando...
              </p>
            )}
          </CardContent>
        </Card>
      </div>

      <Card>
        <CardHeader className="pb-3">
          <CardTitle className="text-base">
            Checklist ({vistoria.itens.length})
          </CardTitle>
        </CardHeader>
        <CardContent>
          <div className="rounded-xl border border-border overflow-hidden">
            <Table>
              <TableHeader>
                <TableRow>
                  <TableHead>Ambiente</TableHead>
                  <TableHead>Item</TableHead>
                  <TableHead className="w-[120px]">Estado</TableHead>
                  <TableHead>Observação</TableHead>
                </TableRow>
              </TableHeader>
              <TableBody>
                {vistoria.itens.length === 0 ? (
                  <TableRow>
                    <TableCell
                      colSpan={4}
                      className="py-8 text-center text-sm text-muted-foreground"
                    >
                      Nenhum item registrado nesta vistoria.
                    </TableCell>
                  </TableRow>
                ) : (
                  vistoria.itens.map((item, indice) => (
                    <TableRow key={item.id ?? indice}>
                      <TableCell className="text-sm">{item.ambiente}</TableCell>
                      <TableCell className="text-sm">{item.item}</TableCell>
                      <TableCell>
                        <Badge
                          variant={
                            item.estado === "Ótimo" || item.estado === "Bom"
                              ? "default"
                              : item.estado === "Ruim" || item.estado === "Substituir"
                                ? "destructive"
                                : "secondary"
                          }
                          className="text-xs"
                        >
                          {item.estado}
                        </Badge>
                      </TableCell>
                      <TableCell className="text-sm text-muted-foreground">
                        {item.observacao ?? "—"}
                      </TableCell>
                    </TableRow>
                  ))
                )}
              </TableBody>
            </Table>
          </div>
        </CardContent>
      </Card>

      <Card>
        <CardHeader className="pb-3">
          <div className="flex items-center justify-between gap-2">
            <CardTitle className="text-base">Fotos ({vistoria.fotos.length})</CardTitle>
            {podeAlterar && (
              <Button
                variant="outline"
                size="sm"
                className="h-7 text-xs gap-1.5"
                onClick={() => setDialogFotoAberto(true)}
              >
                <Plus size={13} />
                Anexar foto
              </Button>
            )}
          </div>
        </CardHeader>
        <CardContent>
          {vistoria.fotos.length === 0 ? (
            <div className="py-6 flex flex-col items-center gap-2 text-center">
              <ImageSquare size={24} className="text-muted-foreground" />
              <p className="text-sm text-muted-foreground">
                Nenhuma foto registrada nesta vistoria.
              </p>
            </div>
          ) : (
            <ul className="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
              {vistoria.fotos.map((foto) => (
                <VistoriaPhotoCard
                  key={foto.id}
                  contratoId={contratoId}
                  vistoriaId={vistoriaId}
                  foto={foto}
                  podeAlterar={podeAlterar}
                  trabalhando={Boolean(st.working)}
                  excluir={() => st.excluirFoto(foto.id, foto.nomeAnexo ?? "foto")}
                />
              ))}
            </ul>
          )}
        </CardContent>
      </Card>

      {dialogFotoAberto && <DialogFotoVistoria
        aberto={dialogFotoAberto}
        aoAbrir={() => setDialogFotoAberto(true)}
        aoFechar={() => setDialogFotoAberto(false)}
        enviando={st.working === "foto-adicionar"}
        aoConfirmar={(nomeAnexo, descricao, arquivo) => {
          st.anexarFoto(
            { nomeAnexo, descricao: descricao.trim() || undefined, arquivo },
            () => setDialogFotoAberto(false),
          );
        }}
      />}
    </div>
  );
}

interface IDialogFotoVistoriaProps {
  aberto: boolean;
  aoAbrir: () => void;
  aoFechar: () => void;
  enviando: boolean;
  aoConfirmar: (nomeAnexo: string, descricao: string, arquivo: File) => void;
}

function DialogFotoVistoria({
  aberto,
  aoAbrir,
  aoFechar,
  enviando,
  aoConfirmar,
}: IDialogFotoVistoriaProps) {
  const [arquivo, setArquivo] = useState<File | null>(null);
  const [nomeAnexo, setNomeAnexo] = useState("");
  const [descricao, setDescricao] = useState("");
  const [previa, setPrevia] = useState("");
  const [erroArquivo, setErroArquivo] = useState("");

  useEffect(() => {
    if (!arquivo) { setPrevia(""); return; }
    const url = URL.createObjectURL(arquivo);
    setPrevia(url);
    return () => URL.revokeObjectURL(url);
  }, [arquivo]);

  function selecionarFoto(foto?: File) {
    setErroArquivo("");
    setArquivo(null);
    setNomeAnexo("");
    if (!foto) return;
    if (!["image/jpeg", "image/png", "image/webp"].includes(foto.type) || foto.size > 4 * 1024 * 1024) {
      setErroArquivo("Selecione uma foto JPG, PNG ou WebP de até 4 MB.");
      return;
    }
    setArquivo(foto);
    setNomeAnexo(foto.name);
  }

  function handleConfirmar() {
    const nome = nomeAnexo.trim();
    if (!nome || !arquivo) return;
    aoConfirmar(nome, descricao, arquivo);
  }

  return (
    <Dialog
      open={aberto}
      onOpenChange={(valor) => {
        if (enviando) return;
        if (valor) aoAbrir();
        else aoFechar();
      }}
    >
      <DialogContent className="max-h-[90dvh] overflow-y-auto">
        <DialogHeader>
          <DialogTitle>Anexar foto à vistoria</DialogTitle>
          <DialogDescription>
            Envie uma foto JPG, PNG ou WebP de até 4 MB.
          </DialogDescription>
        </DialogHeader>
        <div className="py-2 space-y-4">
          <div className="space-y-2">
            <label className="text-sm font-medium">Foto *</label>
            <label htmlFor="foto-vistoria" className="flex min-h-28 cursor-pointer flex-col items-center justify-center gap-2 rounded-xl border border-dashed bg-muted/20 p-4 text-center hover:bg-muted/40">
              <Camera size={28} className="text-muted-foreground" />
              <span className="text-sm font-medium">{arquivo ? "Trocar foto" : "Selecionar foto"}</span>
              <span className="text-xs text-muted-foreground">JPG, PNG ou WebP • até 4 MB</span>
            </label>
            <Input id="foto-vistoria" type="file" className="sr-only" disabled={enviando} accept="image/jpeg,image/png,image/webp" onChange={evento => selecionarFoto(evento.target.files?.[0])} />
            {erroArquivo && <p role="alert" className="text-xs text-destructive">{erroArquivo}</p>}
            {previa && <Image src={previa} alt="Prévia da foto selecionada" width={800} height={600} unoptimized className="max-h-52 w-full rounded-lg bg-muted/20 object-contain" />}
            <label className="text-sm font-medium">Nome do anexo *</label>
            <Input
              placeholder="Ex.: foto-sala-01.jpg"
              disabled={enviando}
              value={nomeAnexo}
              onChange={(evento) => setNomeAnexo(evento.target.value)}
            />
          </div>
          <div className="space-y-2">
            <label className="text-sm font-medium">Descrição</label>
            <Input
              placeholder="Ex.: Rachadura na parede da sala"
              disabled={enviando}
              value={descricao}
              onChange={(evento) => setDescricao(evento.target.value)}
            />
          </div>
        </div>
        <DialogFooter>
          <Button variant="outline" onClick={aoFechar} disabled={enviando}>
            Cancelar
          </Button>
          <Button onClick={handleConfirmar} disabled={!arquivo || !nomeAnexo.trim() || enviando} className="gap-2">
            {enviando && <SpinnerGap size={14} className="animate-spin" />}
            {enviando ? "Enviando..." : "Anexar foto"}
          </Button>
        </DialogFooter>
      </DialogContent>
    </Dialog>
  );
}
