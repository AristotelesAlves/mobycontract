"use client";

import { ImovelFotos, FotoSelecionada } from "../components/imovel-fotos";
import { useRouter } from "next/navigation";
import { Check, Drop, Flame, Lightning, SpinnerGap, Trash, UploadSimple, WifiHigh, Image as ImageIcon } from "@phosphor-icons/react";
import { Button } from "@/components/ui/button";
import {
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
import { usePropertyRegistration } from "../hooks/use-property-registration";
import { ArrowClockwise, WarningCircle } from "@phosphor-icons/react";
import { Card } from "@/components/ui/card";
import { Skeleton } from "@/components/ui/skeleton";

export function NovoImovelView({ propertyId }: { propertyId?: string }) {
  const router = useRouter();
  const registration = usePropertyRegistration(propertyId);
  const { values, fieldErrors, photos, photoUploadStatus } = registration;

  function handleUploadPhotos(
    event: React.ChangeEvent<HTMLInputElement>,
  ) {
    registration.addPhotos(Array.from(event.target.files ?? []));
  }

  if (registration.loading) {
    return (
      <div className="flex flex-col gap-4 w-full pb-10">
        <Card>
          <CardContent className="pt-6 space-y-6">
            <Skeleton className="h-7 w-64" />
            {Array.from({ length: 8 }, (_, index) => (
              <Skeleton key={index} className="h-9 w-full" />
            ))}
          </CardContent>
        </Card>
      </div>
    );
  }

  if (registration.loadError) {
    return (
      <div className="flex flex-col gap-4 w-full pb-10">
        <Card>
          <CardContent className="py-12 flex flex-col items-center gap-3 text-center">
            <WarningCircle size={28} className="text-destructive" />
            <p className="text-sm text-destructive">{registration.loadError}</p>
            <Button
              variant="outline"
              size="sm"
              className="gap-2"
              onClick={registration.retry}
            >
              <ArrowClockwise size={14} /> Tentar novamente
            </Button>
          </CardContent>
        </Card>
      </div>
    );
  }

  return (
    <div className="flex flex-col gap-4 w-full pb-10">
      <CardHeader>
        <CardTitle className="text-xl">
          {registration.modoEdicao ? "Editar Imóvel" : "Novo Imóvel"}
        </CardTitle>
        <CardDescription>
          Preencha os detalhes abaixo para{" "}
          {registration.modoEdicao
            ? "atualizar este imóvel do portfólio"
            : "adicionar um novo imóvel ao seu portfólio"}
          .
        </CardDescription>
      </CardHeader>

      <CardContent className="pt-6">
        <div className="w-full space-y-10">
          
          {/* Seção 1: Informações Principais */}
          <div className="space-y-6">
            <h3 className="text-lg font-semibold flex items-center gap-2">Informações Principais</h3>
            
            <div className="grid gap-4 md:grid-cols-2">
              <div className="space-y-2 md:col-span-2">
                <label className="text-sm font-semibold">Nome completo do proprietário</label>
                <Input placeholder="Nome completo do proprietário do imóvel" maxLength={150} value={values.nomeProprietario ?? ""} onChange={(e) => registration.patchField("nomeProprietario", e.target.value)} />
                {fieldErrors.nomeProprietario && <p className="text-destructive text-xs">{fieldErrors.nomeProprietario}</p>}
              </div>
              <div className="space-y-2 md:col-span-2">
                <label className="text-sm font-semibold">Título de Apresentação</label>
                <Input
                  className="bg-background"
                  placeholder="Ex.: Apartamento 302 - Edifício Aurora"
                  value={values.titulo}
                  onChange={(e) => registration.patchField("titulo", e.target.value)}
                />
                {fieldErrors.titulo && <p className="text-destructive text-xs mt-1">{fieldErrors.titulo}</p>}
              </div>

              <div className="space-y-2 md:col-span-2">
                <label className="text-sm font-semibold">Tipo do Imóvel</label>
                <Select
                  value={values.tipo}
                  onValueChange={(valor) => { if (valor != null) registration.patchField("tipo", valor); }}
                >
                  <SelectTrigger className="bg-background">
                    <SelectValue placeholder="Selecione o tipo" />
                  </SelectTrigger>
                  <SelectContent>
                    <SelectItem value={"Apartamento"}>Apartamento</SelectItem>
                    <SelectItem value={"Casa"}>Casa</SelectItem>
                    <SelectItem value="Studio">Studio</SelectItem>
                    <SelectItem value="Cobertura">Cobertura</SelectItem>
                    <SelectItem value={"Comercial"}>Comercial</SelectItem>
                    <SelectItem value={"Terreno"}>Terreno</SelectItem>
                  </SelectContent>
                </Select>
                {fieldErrors.tipo && <p className="text-destructive text-xs mt-1">{fieldErrors.tipo}</p>}
              </div>

              <div className="space-y-2">
                <label className="text-sm font-semibold">Número da Matrícula</label>
                <Input
                  className="bg-background"
                  placeholder="Opcional"
                  value={values.matricula}
                  onChange={(e) => registration.patchField("matricula", e.target.value)}
                />
                {fieldErrors.matricula && <p className="text-destructive text-xs mt-1">{fieldErrors.matricula}</p>}
              </div>

              <div className="space-y-2 md:col-span-2">
                <label className="text-sm font-semibold">Descrição Detalhada</label>
                <textarea
                  className="flex min-h-[100px] w-full rounded-md border border-input bg-background px-3 py-2 text-sm shadow-sm focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring"
                  placeholder="Descreva as características, pontos de referência..."
                  value={values.descricao}
                  onChange={(e) => registration.patchField("descricao", e.target.value)}
                />
                {fieldErrors.descricao && <p className="text-destructive text-xs mt-1">{fieldErrors.descricao}</p>}
              </div>
            </div>
          </div>

          <hr className="my-8" />

          {/* Seção 2: Localização */}
          <div className="space-y-6">
            <h3 className="text-lg font-semibold flex items-center gap-2">Localização</h3>
            
            <div className="space-y-4">
              
              <div className="grid grid-cols-1 md:grid-cols-4 gap-5">
                <div className="space-y-2 relative">
                  <label className="text-sm font-semibold">CEP</label>
                  <div className="relative">
                    <Input
                      className="bg-background pr-10"
                      placeholder="00000-000"
                      value={values.cep}
                      onChange={(e) => {
                        const val = e.target.value;
                        registration.patchField("cep", val);
                        if (val.replace(/\D/g, "").length === 8) {
                          registration.searchPostalCode(val);
                        }
                      }}
                      maxLength={9}
                    />
                    {registration.searchingAddress && (
                      <SpinnerGap className="absolute right-3 top-1/2 -translate-y-1/2 animate-spin text-muted-foreground" />
                    )}
                  </div>
                  {fieldErrors.cep && <p className="text-destructive text-xs mt-1">{fieldErrors.cep}</p>}
                </div>
                
                <div className="space-y-2 md:col-span-3">
                  <label className="text-sm font-semibold">Logradouro (Rua, Av.)</label>
                  <Input
                    className="bg-background"
                    placeholder="Nome da rua"
                    value={values.logradouro}
                    onChange={(e) => registration.patchField("logradouro", e.target.value)}
                  />
                  {fieldErrors.logradouro && <p className="text-destructive text-xs mt-1">{fieldErrors.logradouro}</p>}
                </div>
              </div>

              <div className="grid grid-cols-2 md:grid-cols-4 gap-5">
                <div className="space-y-2">
                  <label className="text-sm font-semibold">Número</label>
                  <Input
                    className="bg-background"
                    placeholder="123"
                    value={values.numero}
                    onChange={(e) => registration.patchField("numero", e.target.value)}
                  />
                  {fieldErrors.numero && <p className="text-destructive text-xs mt-1">{fieldErrors.numero}</p>}
                </div>
                
                <div className="space-y-2 md:col-span-3">
                  <label className="text-sm font-semibold">Complemento</label>
                  <Input
                    className="bg-background"
                    placeholder="Apto 101, Bloco B"
                    value={values.complemento}
                    onChange={(e) => registration.patchField("complemento", e.target.value)}
                  />
                {fieldErrors.complemento && <p className="text-destructive text-xs mt-1">{fieldErrors.complemento}</p>}
                </div>
              </div>

              <div className="grid grid-cols-1 md:grid-cols-3 gap-5">
                <div className="space-y-2">
                  <label className="text-sm font-semibold">Bairro</label>
                  <Input
                    className="bg-background"
                    placeholder="Bairro"
                    value={values.bairro}
                    onChange={(e) => registration.patchField("bairro", e.target.value)}
                  />
                {fieldErrors.bairro && <p className="text-destructive text-xs mt-1">{fieldErrors.bairro}</p>}
                </div>
                
                <div className="space-y-2">
                  <label className="text-sm font-semibold">Cidade</label>
                  <Input
                    className="bg-background"
                    placeholder="Cidade"
                    value={values.cidade}
                    onChange={(e) => registration.patchField("cidade", e.target.value)}
                  />
                  {fieldErrors.cidade && <p className="text-destructive text-xs mt-1">{fieldErrors.cidade}</p>}
                </div>

                <div className="space-y-2">
                  <label className="text-sm font-semibold">UF</label>
                  <Input
                    className="bg-background"
                    placeholder="SP, RJ, PR..."
                    value={values.estado}
                    onChange={(e) => registration.patchField("estado", e.target.value.toUpperCase())}
                    maxLength={2}
                  />
                {fieldErrors.estado && <p className="text-destructive text-xs mt-1">{fieldErrors.estado}</p>}
                </div>
              </div>

            </div>
          </div>

          <hr className="my-8" />

          {/* Seção 3: Características */}
          <div className="space-y-6">
            <h3 className="text-lg font-semibold flex items-center gap-2">Estrutura</h3>
            
            <div className="grid grid-cols-2 md:grid-cols-4 gap-4">
              <div className="space-y-2">
                <label className="text-sm font-semibold">Área (m²)</label>
                <Input
                  type="text"
                  inputMode="decimal"
                  className="bg-background"
                  placeholder="120,50"
                  value={values.area}
                  onChange={(e) => registration.patchField("area", e.target.value)}
                />
                {fieldErrors.area && <p className="text-destructive text-xs mt-1">{fieldErrors.area}</p>}
              </div>
              <div className="space-y-2">
                <label className="text-sm font-semibold">Quartos</label>
                <Input
                  type="number"
                  className="bg-background"
                  placeholder="0"
                  value={values.quartos}
                  onChange={(e) => registration.patchField("quartos", e.target.value)}
                />
                {fieldErrors.quartos && <p className="text-destructive text-xs mt-1">{fieldErrors.quartos}</p>}
              </div>
              <div className="space-y-2">
                <label className="text-sm font-semibold">Banheiros</label>
                <Input
                  type="number"
                  className="bg-background"
                  placeholder="0"
                  value={values.banheiros}
                  onChange={(e) => registration.patchField("banheiros", e.target.value)}
                />
                {fieldErrors.banheiros && <p className="text-destructive text-xs mt-1">{fieldErrors.banheiros}</p>}
              </div>
              <div className="space-y-2">
                <label className="text-sm font-semibold">Vagas</label>
                <Input
                  type="number"
                  className="bg-background"
                  placeholder="0"
                  value={values.vagasGaragem}
                  onChange={(e) => registration.patchField("vagasGaragem", e.target.value)}
                />
                {fieldErrors.vagasGaragem && <p className="text-destructive text-xs mt-1">{fieldErrors.vagasGaragem}</p>}
              </div>
            </div>
          </div>

          <hr className="my-8" />

          {/* Seção 4: Financeiro e Encargos */}
          <div className="space-y-6">
            <h3 className="text-lg font-semibold flex items-center gap-2">Valores e Encargos</h3>
            
            <div className="space-y-6">
              <div className="grid grid-cols-1 md:grid-cols-3 gap-5">
                <div className="space-y-2">
                  <label className="text-sm font-semibold">Aluguel Base (R$)</label>
                  <Input
                    type="text"
                    inputMode="decimal"
                    className="bg-background border-emerald-500/30 focus-visible:ring-emerald-500"
                    placeholder="1.500,00"
                    value={values.valorAluguel}
                    onChange={(e) => registration.patchField("valorAluguel", e.target.value)}
                  />
                  {fieldErrors.valorAluguel && <p className="text-destructive text-xs mt-1">{fieldErrors.valorAluguel}</p>}
                </div>
                <div className="space-y-2">
                  <label className="text-sm font-semibold text-muted-foreground">Condomínio (R$)</label>
                  <Input
                    type="text"
                    inputMode="decimal"
                    className="bg-background"
                    placeholder="Estimativa, ex.: 350,00"
                    value={values.valorCondominio}
                    onChange={(e) => registration.patchField("valorCondominio", e.target.value)}
                  />
                {fieldErrors.valorCondominio && <p className="text-destructive text-xs mt-1">{fieldErrors.valorCondominio}</p>}
                </div>
                <div className="space-y-2">
                  <label className="text-sm font-semibold text-muted-foreground">IPTU (R$)</label>
                  <Input
                    type="text"
                    inputMode="decimal"
                    className="bg-background"
                    placeholder="Estimativa, ex.: 120,00"
                    value={values.valorIptu}
                    onChange={(e) => registration.patchField("valorIptu", e.target.value)}
                  />
                {fieldErrors.valorIptu && <p className="text-destructive text-xs mt-1">{fieldErrors.valorIptu}</p>}
                </div>
              </div>

              <div className="pt-2">
                <label className="text-sm font-semibold block mb-3">Comodidades Inclusas no Aluguel</label>
                <div className="flex flex-wrap gap-3">
                  <label className={`flex cursor-pointer items-center gap-2 px-4 py-2.5 rounded-lg border transition-all ${registration.waterIncluded ? 'bg-blue-500/10 border-blue-500/50 text-blue-700 dark:text-blue-400 font-medium' : 'bg-background hover:bg-muted/50'}`}>
                    <input
                      type="checkbox"
                      className="hidden"
                      checked={registration.waterIncluded}
                      onChange={(e) => registration.setWaterIncluded(e.target.checked)}
                    />
                    <span className={`w-4 h-4 rounded border flex items-center justify-center ${registration.waterIncluded ? 'bg-blue-500 border-blue-500' : 'border-input'}`}>
                      {registration.waterIncluded && <Check weight="bold" className="text-white w-3 h-3" />}
                    </span>
                    <Drop size={18} className={registration.waterIncluded ? "text-blue-600" : "text-muted-foreground"} />
                    Água
                  </label>
                  
                  <label className={`flex cursor-pointer items-center gap-2 px-4 py-2.5 rounded-lg border transition-all ${registration.powerIncluded ? 'bg-amber-500/10 border-amber-500/50 text-amber-700 dark:text-amber-400 font-medium' : 'bg-background hover:bg-muted/50'}`}>
                    <input
                      type="checkbox"
                      className="hidden"
                      checked={registration.powerIncluded}
                      onChange={(e) => registration.setPowerIncluded(e.target.checked)}
                    />
                    <span className={`w-4 h-4 rounded border flex items-center justify-center ${registration.powerIncluded ? 'bg-amber-500 border-amber-500' : 'border-input'}`}>
                      {registration.powerIncluded && <Check weight="bold" className="text-white w-3 h-3" />}
                    </span>
                    <Lightning size={18} className={registration.powerIncluded ? "text-amber-600" : "text-muted-foreground"} />
                    Energia
                  </label>

                  <label className={`flex cursor-pointer items-center gap-2 px-4 py-2.5 rounded-lg border transition-all ${registration.gasIncluded ? 'bg-orange-500/10 border-orange-500/50 text-orange-700 dark:text-orange-400 font-medium' : 'bg-background hover:bg-muted/50'}`}>
                    <input
                      type="checkbox"
                      className="hidden"
                      checked={registration.gasIncluded}
                      onChange={(e) => registration.setGasIncluded(e.target.checked)}
                    />
                    <span className={`w-4 h-4 rounded border flex items-center justify-center ${registration.gasIncluded ? 'bg-orange-500 border-orange-500' : 'border-input'}`}>
                      {registration.gasIncluded && <Check weight="bold" className="text-white w-3 h-3" />}
                    </span>
                    <Flame size={18} className={registration.gasIncluded ? "text-orange-600" : "text-muted-foreground"} />
                    Gás
                  </label>

                  <label className={`flex cursor-pointer items-center gap-2 px-4 py-2.5 rounded-lg border transition-all ${registration.internetIncluded ? 'bg-purple-500/10 border-purple-500/50 text-purple-700 dark:text-purple-400 font-medium' : 'bg-background hover:bg-muted/50'}`}>
                    <input
                      type="checkbox"
                      className="hidden"
                      checked={registration.internetIncluded}
                      onChange={(e) => registration.setInternetIncluded(e.target.checked)}
                    />
                    <span className={`w-4 h-4 rounded border flex items-center justify-center ${registration.internetIncluded ? 'bg-purple-500 border-purple-500' : 'border-input'}`}>
                      {registration.internetIncluded && <Check weight="bold" className="text-white w-3 h-3" />}
                    </span>
                    <WifiHigh size={18} className={registration.internetIncluded ? "text-purple-600" : "text-muted-foreground"} />
                    Internet
                  </label>
                </div>
              </div>
            </div>
          </div>

          <hr className="my-8" />

          {/* Seção 5: Fotos do Imóvel */}
          <div className="space-y-6">
            <h3 className="text-lg font-semibold flex items-center gap-2">Fotos do Imóvel</h3>
            
            <div className="p-4 border border-dashed rounded-md bg-muted/5 flex flex-col items-center justify-center gap-2 mt-4 min-h-[160px]">
              {photoUploadStatus === "idle" && (
                <>
                  <UploadSimple size={32} className="text-muted-foreground" />
                  <p className="text-sm text-center font-medium">Anexar Fotos do Imóvel</p>
                  <p className="text-xs text-muted-foreground text-center">
                    Arraste e solte as imagens ou clique abaixo (formatos .jpg, .png).
                  </p>
                  <label className="mt-2 cursor-pointer inline-flex items-center justify-center whitespace-nowrap rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 bg-primary text-primary-foreground hover:bg-primary/90 h-10 px-4 py-2">
                    Selecionar Imagens
                    <input type="file" multiple accept="image/jpeg,image/png,image/webp" className="hidden" onChange={handleUploadPhotos} />
                  </label>
                </>
              )}
              {photoUploadStatus === "uploading" && (
                <div className="flex flex-col items-center gap-2 py-4">
                  <SpinnerGap size={32} className="animate-spin text-primary" />
                  <p className="text-sm text-muted-foreground">Enviando imagens...</p>
                </div>
              )}
            </div>

            {propertyId && <ImovelFotos imovelId={propertyId} editavel />}
            {photos.length > 0 && (
              <div className="grid grid-cols-2 md:grid-cols-4 gap-4 mt-4">
                {photos.map((foto, idx) => (
                  <div key={idx} className="relative group rounded-md overflow-hidden border shadow-sm aspect-video bg-muted/20 flex items-center justify-center">
                    <ImageIcon size={32} className="text-muted-foreground/30 absolute z-0" />
                    <FotoSelecionada arquivo={foto} alt={`Foto ${idx+1}`} />
                    <div className="absolute inset-0 bg-black/50 opacity-0 group-hover:opacity-100 transition-opacity z-20 flex items-center justify-center">
                      <Button variant="ghost" size="icon" className="text-white hover:bg-destructive hover:text-white" onClick={() => registration.removePhoto(idx)}>
                        <Trash size={20} />
                      </Button>
                    </div>
                  </div>
                ))}
              </div>
            )}
          </div>
          
          {registration.submitError && (
            <div role="alert" className="text-destructive text-sm space-y-2">
              <p>{registration.submitError}</p>
              {Object.values(fieldErrors).filter(Boolean).length > 0 && (
                <ul className="list-disc pl-5">{Object.entries(fieldErrors).filter(([, message]) => Boolean(message)).map(([field, message]) => <li key={field}>{message}</li>)}</ul>
              )}
            </div>
          )}

          <div className="flex justify-end gap-2 pt-4 border-t border-border/50 mt-10">
            <Button variant="outline" onClick={() => router.push("/imoveis")}>
              Cancelar
            </Button>
            <Button
              onClick={() => void registration.submit()}
              disabled={registration.submitting}
            >
              {registration.submitting ? (
                <SpinnerGap size={16} className="mr-2 animate-spin" />
              ) : (
                <Check size={16} className="mr-2" />
              )}
              {registration.submitting
                ? "Salvando imóvel..."
                : registration.modoEdicao
                  ? "Salvar alterações"
                  : "Salvar Imóvel"}
            </Button>
          </div>
        </div>
      </CardContent>
    </div>
  );
}
