"use client";

import { useEffect, useState } from "react";
import { Combobox } from "@base-ui/react/combobox";
import { CaretDown } from "@phosphor-icons/react";
import { Input } from "@/components/ui/input";
import { Button } from "@/components/ui/button";
import { api } from "@/services/api/base-api";

export function ProfissaoInput({ value, onChange }: { value: string; onChange: (value: string) => void }) {
  const [opcoes, setOpcoes] = useState<string[]>([]);
  const [outros, setOutros] = useState(false);
  const [aberto, setAberto] = useState(false);
  const [erro, setErro] = useState(false);
  const [tentativa, setTentativa] = useState(0);
  useEffect(() => {
    let cancelado = false;
    setErro(false);
    api.get<{ dados: Array<{ id: number; nome: string }> }>("/inquilinos/profissoes").then(({ dados }) => {
      if (!cancelado) setOpcoes(dados.map((item) => item.nome));
    }).catch(() => { if (!cancelado) setErro(true); });
    return () => { cancelado = true; };
  }, [tentativa]);
  const personalizado = outros || Boolean(value && !opcoes.includes(value));
  const selecionarOutros = () => { setOutros(true); setAberto(false); onChange(""); };
  return (
    <div className="space-y-2">
      <Combobox.Root open={aberto} onOpenChange={setAberto} items={[...opcoes, "Outros"]} value={personalizado ? "Outros" : value || null} onValueChange={(option) => { setOutros(option === "Outros"); onChange(option === "Outros" ? "" : option ?? ""); }}>
        <div className="relative">
          <Combobox.Input aria-label="Profissão" placeholder="Busque ou selecione uma profissão" render={<Input className="w-full pr-9" />} />
          <Combobox.Trigger aria-label="Mostrar profissões" className="absolute inset-y-0 right-0 flex w-9 items-center justify-center"><CaretDown size={14} /></Combobox.Trigger>
        </div>
        <Combobox.Portal>
          <Combobox.Positioner sideOffset={4} className="z-50 w-[var(--anchor-width)]">
            <Combobox.Popup className="rounded-lg border bg-popover p-1 text-popover-foreground shadow-md">
              <Combobox.Empty className="px-3 py-2 text-xs text-muted-foreground">Nenhuma profissão encontrada.</Combobox.Empty>
              <Combobox.List className="max-h-56 overflow-y-auto">{(option: string) => <Combobox.Item key={option} value={option} className="cursor-pointer rounded px-3 py-2 text-sm data-highlighted:bg-accent data-selected:font-semibold">{option}</Combobox.Item>}</Combobox.List>
              <Button variant="ghost" size="sm" className="mt-1 w-full justify-start border-t" onClick={selecionarOutros}>Não encontrou? Outros</Button>
            </Combobox.Popup>
          </Combobox.Positioner>
        </Combobox.Portal>
      </Combobox.Root>
      {personalizado && <Input aria-label="Outra profissão" placeholder="Informe a profissão" maxLength={120} value={value} onChange={(e) => onChange(e.target.value)} />}
      {erro && <div className="text-xs text-muted-foreground">Não foi possível carregar as profissões. Você pode usar Outros. <button type="button" className="underline" onClick={() => setTentativa((current) => current + 1)}>Tentar novamente</button></div>}
    </div>
  );
}
