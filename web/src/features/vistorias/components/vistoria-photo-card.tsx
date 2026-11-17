"use client";

import { useEffect, useState } from "react";
import Image from "next/image";
import { ArrowClockwise, DownloadSimple, ImageSquare, SpinnerGap, Trash } from "@phosphor-icons/react";
import { Button } from "@/components/ui/button";
import { Dialog, DialogContent, DialogDescription, DialogHeader, DialogTitle } from "@/components/ui/dialog";
import { getReadableErrorMessage } from "@/services/api/get-readable-error-message";
import { getVistoriaPhoto } from "../services/vistoria-service";
import type { VistoriaFoto } from "../data/vistoria-types";

export function VistoriaPhotoCard({ contratoId, vistoriaId, foto, podeAlterar, trabalhando, excluir }: {
  contratoId: string;
  vistoriaId: string;
  foto: VistoriaFoto;
  podeAlterar: boolean;
  trabalhando: boolean;
  excluir: () => void;
}) {
  const [url, setUrl] = useState("");
  const [erro, setErro] = useState("");
  const [tentativa, setTentativa] = useState(0);
  const [aberta, setAberta] = useState(false);
  const nome = foto.nomeAnexo ?? "Foto da vistoria";

  useEffect(() => {
    let cancelado = false;
    let objectUrl = "";
    setUrl("");
    setErro("");
    getVistoriaPhoto(contratoId, vistoriaId, foto.id).then((blob) => {
      if (cancelado) return;
      objectUrl = URL.createObjectURL(blob);
      setUrl(objectUrl);
    }).catch((error) => {
      if (!cancelado) setErro(getReadableErrorMessage(error));
    });
    return () => {
      cancelado = true;
      if (objectUrl) URL.revokeObjectURL(objectUrl);
    };
  }, [contratoId, vistoriaId, foto.id, tentativa]);

  return (
    <li className="overflow-hidden rounded-xl border bg-card">
      {erro ? (
        <div className="flex aspect-[4/3] flex-col items-center justify-center gap-2 bg-muted/30 p-4 text-center">
          <ImageSquare size={28} className="text-muted-foreground" />
          <p role="alert" className="text-xs text-destructive">{erro}</p>
          <Button size="sm" variant="outline" onClick={() => setTentativa((value) => value + 1)}><ArrowClockwise size={14} /> Tentar novamente</Button>
        </div>
      ) : (
        <button type="button" className="relative flex aspect-[4/3] w-full items-center justify-center bg-muted/30 focus-visible:outline-2 focus-visible:outline-primary" disabled={!url} onClick={() => setAberta(true)} aria-label={`Ampliar ${nome}`}>
          {url ? <Image src={url} alt={foto.descricao || nome} fill unoptimized sizes="(min-width: 1024px) 33vw, 100vw" className="object-cover" onError={() => setErro("Não foi possível exibir esta foto.")} /> : <SpinnerGap size={24} className="animate-spin text-muted-foreground" aria-label="Carregando foto" />}
        </button>
      )}
      <div className="flex items-start justify-between gap-2 p-3">
        <div className="min-w-0 space-y-1">
          <p className="truncate text-sm font-medium" title={nome}>{nome}</p>
          {foto.descricao && <p className="break-words text-xs text-muted-foreground">{foto.descricao}</p>}
        </div>
        <div className="flex shrink-0 gap-1">
          {url && <Button nativeButton={false} render={<a href={url} download={nome} />} variant="ghost" size="icon" title="Baixar foto" aria-label={`Baixar ${nome}`}><DownloadSimple size={16} /></Button>}
          {podeAlterar && <Button variant="ghost" size="icon" disabled={trabalhando} onClick={excluir} title="Excluir foto" aria-label={`Excluir ${nome}`} className="text-muted-foreground hover:text-destructive"><Trash size={16} /></Button>}
        </div>
      </div>
      <Dialog open={aberta} onOpenChange={setAberta}>
        <DialogContent className="sm:max-w-4xl">
          <DialogHeader><DialogTitle className="break-all">{nome}</DialogTitle><DialogDescription>{foto.descricao || "Foto anexada à vistoria."}</DialogDescription></DialogHeader>
          {url && <Image src={url} alt={foto.descricao || nome} width={1600} height={1200} unoptimized className="max-h-[70dvh] w-full rounded-lg object-contain" />}
        </DialogContent>
      </Dialog>
    </li>
  );
}
