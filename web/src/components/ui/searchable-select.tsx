"use client";

import { Combobox } from "@base-ui/react/combobox";
import { CaretDown } from "@phosphor-icons/react";
import { Input } from "@/components/ui/input";

export function SearchableSelect({ options, value, onValueChange, placeholder, label, disabled = false }: {
  options: Array<{ value: string; label: string }>;
  value: string;
  onValueChange: (value: string) => void;
  placeholder: string;
  label: string;
  disabled?: boolean;
}) {
  return (
    <Combobox.Root items={options} value={options.find((option) => option.value === value) ?? null} itemToStringLabel={(option) => option.label} isItemEqualToValue={(a, b) => a.value === b.value} filter={(option, query) => {
      const normalize = (text: string) => text.normalize("NFD").replace(/[\u0300-\u036f]/g, "").toLowerCase();
      const term = query.trim();
      return normalize(option.label).includes(normalize(term)) || (/^\d[\d.\-/ ]*$/.test(term) && option.label.replace(/\D/g, "").includes(term.replace(/\D/g, "")));
    }} onValueChange={(option) => onValueChange(option?.value ?? "")} disabled={disabled}>
      <div className="relative w-full">
        <Combobox.Input aria-label={label} placeholder={placeholder} render={<Input className="w-full pr-9" />} />
        <Combobox.Trigger aria-label={`Mostrar opções de ${label.toLowerCase()}`} className="absolute inset-y-0 right-0 flex w-9 items-center justify-center"><CaretDown size={14} /></Combobox.Trigger>
      </div>
      <Combobox.Portal>
        <Combobox.Positioner sideOffset={4} className="z-50 w-[var(--anchor-width)]">
          <Combobox.Popup className="rounded-lg border bg-popover p-1 text-popover-foreground shadow-md">
            <Combobox.Empty className="px-3 py-2 text-sm text-muted-foreground">Nenhum resultado encontrado.</Combobox.Empty>
            <Combobox.List className="max-h-64 overflow-y-auto">{(option: { value: string; label: string }) => <Combobox.Item key={option.value} value={option} className="cursor-pointer rounded px-3 py-2 text-sm data-highlighted:bg-accent data-selected:font-semibold">{option.label}</Combobox.Item>}</Combobox.List>
          </Combobox.Popup>
        </Combobox.Positioner>
      </Combobox.Portal>
    </Combobox.Root>
  );
}
