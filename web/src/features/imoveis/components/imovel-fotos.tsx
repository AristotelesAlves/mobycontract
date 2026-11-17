"use client";

import { useEffect, useState } from "react";
import { CaretLeft, CaretRight, ArrowsOut, Trash } from "@phosphor-icons/react";
import { api } from "@/services/api/base-api";
import { getReadableErrorMessage } from "@/services/api/get-readable-error-message";
import { Button } from "@/components/ui/button";
import { Dialog, DialogContent, DialogDescription, DialogHeader, DialogTitle } from "@/components/ui/dialog";

type Foto = { id: number; nome: string; url: string };

export function FotoSelecionada({ arquivo, alt }: { arquivo: File; alt: string }) {
  const [url, setUrl] = useState("");
  useEffect(() => {
    const temporaria = URL.createObjectURL(arquivo);
    setUrl(temporaria);
    return () => URL.revokeObjectURL(temporaria);
  }, [arquivo]);
  // eslint-disable-next-line @next/next/no-img-element
  return <img src={url || undefined} alt={alt} className="object-cover w-full h-full relative z-10" />;
}

export function ImovelFotos({ imovelId, editavel = false }: { imovelId: string; editavel?: boolean }) {
  const [fotos, setFotos] = useState<Foto[]>([]);
  const [fotoAberta, setFotoAberta] = useState<Foto | null>(null);
  const [erro, setErro] = useState("");
  const [carregando, setCarregando] = useState(true);
  useEffect(() => {
    let cancelado = false;
    const urls: string[] = [];
    async function carregar() {
      try {
        const resposta = await api.get<{ dados: { id: number; nome: string }[] }>(`/imoveis/${imovelId}/fotos`);
        const carregadas: Foto[] = [];
        for (const foto of resposta.dados) {
          if (cancelado) break;
          const blob = await api.arquivo(`/imoveis/${imovelId}/fotos/${foto.id}`);
          const url = URL.createObjectURL(blob);
          urls.push(url);
          carregadas.push({ ...foto, url });
        }
        if (!cancelado) setFotos(carregadas);
      } catch (error) {
        if (!cancelado) setErro(getReadableErrorMessage(error));
      } finally {
        if (cancelado) urls.forEach(url => URL.revokeObjectURL(url));
        else setCarregando(false);
      }
    }
    void carregar();
    return () => { cancelado = true; urls.forEach(url => URL.revokeObjectURL(url)); };
  }, [imovelId]);
  async function excluir(foto: Foto) {
    try {
      await api.delete(`/imoveis/${imovelId}/fotos/${foto.id}`);
      setFotos(current => current.filter(item => item.id !== foto.id));
      if (fotoAberta?.id === foto.id) setFotoAberta(null);
      URL.revokeObjectURL(foto.url);
    } catch (error) { setErro(getReadableErrorMessage(error)); }
  }
  const indiceAtual = fotos.findIndex(foto => foto.id === fotoAberta?.id);
  function navegar(direcao: number) {
    if (fotos.length < 2) return;
    setFotoAberta(fotos[(indiceAtual + direcao + fotos.length) % fotos.length]);
  }

  return <div className="space-y-3 my-4">
    {(erro || carregando || !fotos.length) && <p role="status" className="text-sm text-muted-foreground">{erro || (carregando ? "Carregando fotos..." : "Nenhuma foto armazenada.")}</p>}
    <div className="grid grid-flow-dense auto-rows-[150px] grid-cols-2 gap-3 sm:auto-rows-[190px] lg:grid-cols-4">
      {fotos.map((foto, index) => {
        const destaque = index % 7 === 0;
        const larga = index % 7 === 3 || index % 7 === 6;
        return <div key={foto.id} className={`group relative overflow-hidden rounded-2xl bg-muted ${destaque ? "col-span-2 row-span-2" : larga ? "col-span-2" : ""}`}>
          <button type="button" onClick={() => setFotoAberta(foto)} aria-label={`Ampliar ${foto.nome}`} className="relative h-full w-full cursor-zoom-in text-left focus-visible:outline-2 focus-visible:outline-offset-[-3px] focus-visible:outline-primary">
            {/* eslint-disable-next-line @next/next/no-img-element */}
            <img src={foto.url} alt={foto.nome} className="h-full w-full object-cover transition-transform duration-500 motion-safe:group-hover:scale-105" />
            <span className="pointer-events-none absolute inset-0 bg-gradient-to-t from-black/70 via-transparent to-transparent opacity-70 transition-opacity group-hover:opacity-100" />
            <span className="absolute inset-x-0 bottom-0 flex items-end justify-between gap-3 p-4 text-white">
              <span className="min-w-0"><span className="block text-[10px] uppercase tracking-widest text-white/70">{index === 0 ? "Conheça o imóvel" : `Foto ${index + 1}`}</span><span className="mt-1 block truncate text-sm font-medium">{foto.nome}</span></span>
              <ArrowsOut size={20} className="shrink-0" />
            </span>
          </button>
          {editavel && <Button type="button" size="icon" variant="secondary" aria-label={`Excluir ${foto.nome}`} title="Excluir foto" className="absolute right-3 top-3 rounded-full shadow-sm" onClick={() => excluir(foto)}><Trash size={16} /></Button>}
        </div>;
      })}
    </div>
    <Dialog open={Boolean(fotoAberta)} onOpenChange={(open) => { if (!open) setFotoAberta(null); }}>
      <DialogContent className="w-[calc(100vw-1.5rem)] max-w-6xl overflow-hidden rounded-2xl p-3 sm:p-5" onKeyDown={(event) => {
        if (event.key === "ArrowLeft" || event.key === "ArrowRight") {
          event.preventDefault();
          navegar(event.key === "ArrowLeft" ? -1 : 1);
        }
      }}>
        <DialogHeader className="pr-10">
          <DialogTitle className="truncate">{fotoAberta?.nome || "Foto do imóvel"}</DialogTitle>
          <DialogDescription aria-live="polite">Foto {indiceAtual + 1} de {fotos.length}</DialogDescription>
        </DialogHeader>
        <div className="relative flex h-[55dvh] items-center justify-center overflow-hidden rounded-xl bg-black/95 sm:h-[65dvh]">
          {/* eslint-disable-next-line @next/next/no-img-element */}
          {fotoAberta && <img src={fotoAberta.url} alt={fotoAberta.nome} className="h-full w-full object-contain" />}
          {fotos.length > 1 && <>
            <Button type="button" size="icon" variant="secondary" onClick={() => navegar(-1)} aria-label="Foto anterior" className="absolute left-2 rounded-full shadow-lg sm:left-4"><CaretLeft size={22} /></Button>
            <Button type="button" size="icon" variant="secondary" onClick={() => navegar(1)} aria-label="Próxima foto" className="absolute right-2 rounded-full shadow-lg sm:right-4"><CaretRight size={22} /></Button>
          </>}
        </div>
        {fotos.length > 1 && <div className="flex gap-2 overflow-x-auto pb-1" aria-label="Fotos do imóvel">
          {fotos.map((foto, index) => <button key={foto.id} type="button" onClick={() => setFotoAberta(foto)} aria-label={`Ver foto ${index + 1}: ${foto.nome}`} aria-pressed={foto.id === fotoAberta?.id} className={`h-14 w-20 shrink-0 overflow-hidden rounded-lg border-2 transition-opacity ${foto.id === fotoAberta?.id ? "border-primary opacity-100" : "border-transparent opacity-50 hover:opacity-100"}`}>
            {/* eslint-disable-next-line @next/next/no-img-element */}
            <img src={foto.url} alt="" className="h-full w-full object-cover" />
          </button>)}
        </div>}
      </DialogContent>
    </Dialog>
  </div>;
}
