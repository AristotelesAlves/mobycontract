import { Check } from "@phosphor-icons/react";
import { SearchableSelect } from "@/components/ui/searchable-select";
import Link from "next/link";
import { formatCurrency } from "@/lib/formatters";
import { type CreateContractHookData } from "../hooks/use-create-contract";

export function ContractImovelSection({ data }: { data: CreateContractHookData }) {
  const { properties, propertyId, setPropertyId, fieldErrors } = data;

  return (
    <div className="space-y-6">
      <div className="flex flex-col w-full">
        <div className="space-y-2 w-full">
          <label className="text-sm font-medium">Imóvel (Objeto Principal) *</label>
          <SearchableSelect label="Imóvel" placeholder="Busque por nome ou matrícula do imóvel" value={propertyId} onValueChange={setPropertyId} disabled={data.loading} options={properties.map((property) => ({ value: property.id, label: `${property.titulo} • ${property.codigo} • ${formatCurrency(property.valorAluguel)}` }))} />
          {fieldErrors.imovel && <p className="text-destructive text-xs">{fieldErrors.imovel}</p>}
        </div>

        {fieldErrors.modelo && <p className="text-destructive text-xs mt-2">{fieldErrors.modelo}</p>}

      </div>

      {propertyId && (() => {
        const selecionado = properties.find(i => i.id === propertyId);
        if (!selecionado) return null;
        return (
          <div className="p-5 border rounded-md bg-muted/5 flex flex-col gap-4 animate-in fade-in slide-in-from-top-2 duration-300">
            <div className="flex items-center gap-2 border-b pb-3">
              <div className="bg-primary/10 p-2 rounded-full text-primary">
                <Check size={18} weight="bold" />
              </div>
              <h5 className="font-semibold text-base">Dados do Imóvel</h5>
            </div>
            <div className="grid grid-cols-2 md:grid-cols-5 gap-5 text-sm">
              <div className="col-span-2 md:col-span-5 space-y-1">
                <span className="text-xs text-muted-foreground uppercase font-semibold">Proprietário (locador)</span>
                {selecionado.nomeProprietario?.trim() ? (
                  <p className="font-medium">{selecionado.nomeProprietario}</p>
                ) : (
                  <p className="text-sm text-destructive">Informe o proprietário no <Link className="underline" href={`/imoveis/${selecionado.id}/editar`}>cadastro do imóvel</Link> para criar o contrato.</p>
                )}
                {fieldErrors.locador && <p className="text-destructive text-xs">{fieldErrors.locador}</p>}
              </div>
              <div className="flex flex-col col-span-2 md:col-span-5 bg-card border rounded-md p-3">
                <span className="text-xs text-muted-foreground uppercase font-semibold">Endereço Completo</span>
                <span className="font-medium mt-1">{selecionado.endereco?.logradouro}, {selecionado.endereco?.numero} - {selecionado.endereco?.bairro}, {selecionado.endereco?.cidade}/{selecionado.endereco?.estado}</span>
              </div>
              <div className="flex flex-col">
                <span className="text-xs text-muted-foreground uppercase font-semibold">Tipo</span>
                <span className="font-medium">{selecionado.tipo}</span>
              </div>
              <div className="flex flex-col">
                <span className="text-xs text-muted-foreground uppercase font-semibold">Área</span>
                <span className="font-medium">{selecionado.area} m²</span>
              </div>
              <div className="flex flex-col">
                <span className="text-xs text-muted-foreground uppercase font-semibold">Quartos</span>
                <span className="font-medium">{selecionado.quartos}</span>
              </div>
              <div className="flex flex-col">
                <span className="text-xs text-muted-foreground uppercase font-semibold">Banheiros</span>
                <span className="font-medium">{selecionado.banheiros}</span>
              </div>
              <div className="flex flex-col">
                <span className="text-xs text-muted-foreground uppercase font-semibold">Vagas</span>
                <span className="font-medium">{selecionado.vagas}</span>
              </div>
              
              <div className="flex flex-col col-span-2 md:col-span-5 mt-2">
                <div className="grid grid-cols-1 md:grid-cols-3 gap-4 border-t border-dashed pt-4">
                  <div className="flex flex-col">
                    <span className="text-xs text-muted-foreground uppercase font-semibold">Valor Aluguel</span>
                    <span className="font-medium text-emerald-600 font-bold text-lg">{formatCurrency(selecionado.valorAluguel)}</span>
                  </div>
                  <div className="flex flex-col">
                    <span className="text-xs text-muted-foreground uppercase font-semibold">Despesas Extra</span>
                    <span className="font-medium text-sm text-muted-foreground mt-1 leading-relaxed">
                      IPTU: <span className="text-foreground">{selecionado.valorIptu ? formatCurrency(selecionado.valorIptu) : "N/A"}</span><br/>
                      Condomínio: <span className="text-foreground">{selecionado.valorCondominio ? formatCurrency(selecionado.valorCondominio) : "N/A"}</span>
                    </span>
                  </div>
                  <div className="flex flex-col">
                    <span className="text-xs text-muted-foreground uppercase font-semibold">Consumo</span>
                    <span className="font-medium text-sm text-muted-foreground mt-1 leading-relaxed">
                      Água: <span className="text-foreground">Inquilino</span><br/>
                      Luz: <span className="text-foreground">Inquilino</span>
                    </span>
                  </div>
                </div>
              </div>

              {selecionado.descricao && (
                <div className="flex flex-col col-span-2 md:col-span-5 mt-2 pt-4 border-t border-dashed">
                  <span className="text-xs text-muted-foreground uppercase font-semibold">Descrição do Anúncio</span>
                  <span className="font-medium text-muted-foreground mt-2 leading-relaxed">{selecionado.descricao}</span>
                </div>
              )}
            </div>
          </div>
        );
      })()}
    </div>
  );
}
