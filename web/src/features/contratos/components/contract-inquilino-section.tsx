import { Check, MagnifyingGlass, Spinner } from "@phosphor-icons/react";
import { Button } from "@/components/ui/button";
import { SearchableSelect } from "@/components/ui/searchable-select";
import { Input } from "@/components/ui/input";
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from "@/components/ui/select";
import { useState } from "react";
import { formatPhone, formatTaxId } from "@/lib/formatters";
import { ProfissaoInput } from "@/features/inquilinos/components/profissao-input";
import { type CreateContractHookData } from "../hooks/use-create-contract";

type TenantKind = "PF" | "PJ";

export function ContractInquilinoSection({ data }: { data: CreateContractHookData }) {
  const { tenants, fieldErrors, parties } = data;
  const [tenantKind, setTenantKind] = useState<TenantKind>("PF");

  return (
    <div className="space-y-4 pt-2">
      <div className="flex flex-wrap items-center justify-between gap-3">
        <h4 className="font-semibold text-sm">Dados do Locatário (Inquilino)</h4>
        <div className="flex gap-2">
          <Button variant={parties.partyMode === "existente" ? "default" : "outline"} size="sm" onClick={() => parties.changePartyMode("existente")}>
            Já cadastrado
          </Button>
          <Button variant={parties.partyMode === "novo" ? "default" : "outline"} size="sm" onClick={() => parties.changePartyMode("novo")}>
            Novo
          </Button>
        </div>
      </div>

      {parties.partyMode === "existente" ? (
        <div className="space-y-4 w-full">
          <div className="space-y-2 w-full">
            <SearchableSelect label="Locatário" placeholder="Busque por nome ou CPF do locatário" value={parties.existingTenantId} onValueChange={parties.setExistingTenantId} disabled={data.loading} options={tenants.map((tenant) => ({ value: tenant.id, label: `${tenant.nome} • ${formatTaxId(tenant.cpf)}` }))} />
            {fieldErrors.inquilino && <p className="text-destructive text-xs">{fieldErrors.inquilino}</p>}
          </div>

          {parties.existingTenantId && (() => {
            const selecionado = tenants.find(i => i.id === parties.existingTenantId);
            if (!selecionado) return null;
            return (
              <div className="p-4 border rounded-md bg-muted/5 flex flex-col gap-3 animate-in fade-in slide-in-from-top-2 duration-300">
                <div className="flex items-center gap-2 border-b pb-2">
                  <div className="bg-primary/10 p-2 rounded-full text-primary">
                    <Check size={16} weight="bold" />
                  </div>
                  <h5 className="font-semibold text-sm">Dados do Inquilino Selecionado</h5>
                </div>
                <div className="grid grid-cols-1 md:grid-cols-2 gap-3 text-sm">
                  <div className="flex flex-col">
                    <span className="text-xs text-muted-foreground uppercase font-semibold">Nome / Razão Social</span>
                    <span className="font-medium">{selecionado.nome}</span>
                  </div>
                  <div className="flex flex-col">
                    <span className="text-xs text-muted-foreground uppercase font-semibold">Documento</span>
                    <span className="font-medium">{selecionado.cpf}</span>
                  </div>
                  {selecionado.telefone && (
                    <div className="flex flex-col">
                      <span className="text-xs text-muted-foreground uppercase font-semibold">Telefone</span>
                      <span className="font-medium">{selecionado.telefone}</span>
                    </div>
                  )}
                  {selecionado.email && (
                    <div className="flex flex-col">
                      <span className="text-xs text-muted-foreground uppercase font-semibold">E-mail</span>
                      <span className="font-medium">{selecionado.email}</span>
                    </div>
                  )}

                </div>
              </div>
            );
          })()}
        </div>
      ) : (
        <div className="grid w-full gap-4 rounded-xl border bg-muted/10 p-5 sm:grid-cols-2 xl:grid-cols-3">
          <div className="space-y-2 w-full sm:col-span-2 xl:col-span-3">
            <label className="text-sm font-medium">{tenantKind === "PF" ? "Nome Completo *" : "Razão Social *"}</label>
            <Input className="w-full" placeholder={tenantKind === "PF" ? "Nome do inquilino" : "Razão social da empresa"} value={parties.newTenant.nome} onChange={(e) => parties.patchNewTenant("nome", e.target.value)} />
          </div>
          <div className="space-y-2 w-full">
            <label className="text-sm font-medium">Tipo</label>
            <Select value={tenantKind} onValueChange={(v: "PF" | "PJ" | null) => { if (v) setTenantKind(v); }}>
              <SelectTrigger className="w-full">
                <SelectValue>
                  {tenantKind === "PF" ? "Pessoa Física" : "Pessoa Jurídica"}
                </SelectValue>
              </SelectTrigger>
              <SelectContent>
                <SelectItem value="PF">Pessoa Física</SelectItem>
                <SelectItem value="PJ">Pessoa Jurídica</SelectItem>
              </SelectContent>
            </Select>
          </div>
          <div className="space-y-2 w-full">
            <label className="text-sm font-medium">{tenantKind === "PF" ? "CPF *" : "CNPJ *"}</label>
            <div className="flex gap-2 w-full">
              <Input className="w-full" inputMode="numeric" maxLength={tenantKind === "PF" ? 14 : 18} placeholder={tenantKind === "PF" ? "000.000.000-00" : "00.000.000/0000-00"} value={parties.newTenant.documento} onChange={(e) => parties.patchNewTenant("documento", formatTaxId(tenantKind === "PF" ? e.target.value.replace(/\D/g, "").slice(0, 11) : e.target.value))} />
              {tenantKind === "PJ" && (
                <Button variant="secondary" size="icon" onClick={() => void parties.searchCompany()} disabled={parties.searchingCompany}>
                  {parties.searchingCompany ? <Spinner className="animate-spin" /> : <MagnifyingGlass />}
                </Button>
              )}
            </div>
          </div>

          {tenantKind === "PF" && (
            <div className="space-y-2 w-full">
              <label className="text-sm font-medium">RG / Órgão Emissor</label>
              <Input className="w-full" placeholder="Ex: 1234567 SSP/SP" value={parties.newTenant.rg} onChange={(e) => parties.patchNewTenant("rg", e.target.value)} />
            </div>
          )}
          <div className="space-y-2 w-full">
            <label className="text-sm font-medium">Telefone *</label>
            <Input className="w-full" placeholder="(00) 00000-0000" inputMode="tel" maxLength={15} value={parties.newTenant.telefone} onChange={(e) => parties.patchNewTenant("telefone", formatPhone(e.target.value))} />
          </div>
          <div className="space-y-2 w-full">
            <label className="text-sm font-medium">E-mail *</label>
            <Input className="w-full" type="email" placeholder="email@exemplo.com" value={parties.newTenant.email} onChange={(e) => parties.patchNewTenant("email", e.target.value)} />
          </div>
          <div className="space-y-2 w-full">
            <label className="text-sm font-medium">Data de nascimento *</label>
            <Input className="w-full" type="date" value={parties.newTenant.dataNascimento} onChange={(e) => parties.patchNewTenant("dataNascimento", e.target.value)} />
          </div>
          <div className="space-y-2 w-full">
            <label className="text-sm font-medium">Estado civil *</label>
            <Select value={parties.newTenant.estadoCivil} onValueChange={(v: string | null) => { if (v) parties.patchNewTenant("estadoCivil", v); }}>
              <SelectTrigger className="w-full">
                <SelectValue placeholder="Selecione o estado civil" />
              </SelectTrigger>
              <SelectContent>
                <SelectItem value="Solteiro(a)">Solteiro(a)</SelectItem>
                <SelectItem value="Casado(a)">Casado(a)</SelectItem>
                <SelectItem value="Divorciado(a)">Divorciado(a)</SelectItem>
                <SelectItem value="Viúvo(a)">Viúvo(a)</SelectItem>
                <SelectItem value="União Estável">União Estável</SelectItem>
              </SelectContent>
            </Select>
          </div>
          <div className="space-y-2 w-full">
            <label className="text-sm font-medium">Renda mensal (R$) *</label>
            <Input className="w-full" type="text" inputMode="decimal" placeholder="Ex.: 4.500,00" value={parties.newTenant.renda} onChange={(e) => parties.patchNewTenant("renda", e.target.value)} />
          </div>
          <div className="space-y-2 w-full">
            <label className="text-sm font-medium">Profissão</label>
            <ProfissaoInput value={parties.newTenant.profissao ?? ""} onChange={(value) => parties.patchNewTenant("profissao", value)} />
          </div>
          {fieldErrors.novoInquilino && <p className="text-destructive text-xs sm:col-span-2 xl:col-span-3">{fieldErrors.novoInquilino}</p>}
        </div>
      )}
    </div>
  );
}
