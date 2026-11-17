import { Check, Eye, Plus, Spinner, Trash, UploadSimple } from "@phosphor-icons/react";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from "@/components/ui/select";
import { type CreateContractHookData } from "../hooks/use-create-contract";

export function ContractGarantiaSection({ data }: { data: CreateContractHookData }) {
  const {
    templateId,
    setTemplateId,
    contractTemplates,
    guarantees,
    fieldErrors,
  } = data;

  const {
    guaranteeType,
    depositAmount,
    guarantors,
    attachmentStatus,
    attachment,
    addGuarantor,
    removeGuarantor,
    updateGuarantor,
    handleAttachment,
    clearAttachment,
  } = guarantees;

  function handleAttachmentChange(
    event: React.ChangeEvent<HTMLInputElement>,
  ) {
    handleAttachment(event.target.files?.[0]);
  }

  return (
    <div className="space-y-6">
      <h3 className="text-lg font-semibold flex items-center gap-2">Modalidades de Garantia</h3>
      {guarantees.attachmentError && <p role="alert" className="text-destructive">{guarantees.attachmentError}</p>}
      <div className="space-y-4">
        <div className="grid gap-4 md:grid-cols-2 w-full">
          <div className="space-y-2 w-full">
            <label className="text-sm font-medium">Modalidade de garantia</label>
            <Select value={guaranteeType} onValueChange={(v) => {
              if (v == null || v === guaranteeType) return;
              guarantees.setGuaranteeType(v);
              setTemplateId("");
              clearAttachment();
            }}>
              <SelectTrigger className="w-full">
                <SelectValue>
                  {guaranteeType === "Caução" ? "Opção A: Caução em dinheiro ou imóvel" :
                   guaranteeType === "Fiador" ? "Opção B: Fiador" :
                   guaranteeType === "Seguro-fiança" ? "Opção C: Seguro-fiança locatícia" :
                   guaranteeType === "Título de capitalização" ? "Opção D: Título de capitalização" :
                   "Opção E: Sem garantia"}
                </SelectValue>
              </SelectTrigger>
              <SelectContent>
                <SelectItem value="Caução">Opção A: Caução em dinheiro ou imóvel</SelectItem>
                <SelectItem value="Fiador">Opção B: Fiador</SelectItem>
                <SelectItem value="Seguro-fiança">Opção C: Seguro-fiança locatícia</SelectItem>
                <SelectItem value="Título de capitalização">Opção D: Título de capitalização</SelectItem>
                <SelectItem value="Sem garantia">Opção E: Sem garantia</SelectItem>
              </SelectContent>
            </Select>
          </div>

          <div className="space-y-2 w-full">
            <label className="text-sm font-medium">Modelo Base do Contrato *</label>
            <Select value={templateId} onValueChange={(v) => { if (v != null) setTemplateId(v); }}>
              <SelectTrigger className="w-full">
                <SelectValue placeholder="Selecione o modelo">
                  {templateId && contractTemplates.find(m => m.uuid === templateId)
                    ? `${contractTemplates.find(m => m.uuid === templateId)?.titulo} (v${contractTemplates.find(m => m.uuid === templateId)?.versao})`
                    : undefined}
                </SelectValue>
              </SelectTrigger>
              <SelectContent>
                {contractTemplates.map((modelo) => (
                  <SelectItem key={modelo.uuid} value={modelo.uuid}>
                    {modelo.titulo} (v{modelo.versao})
                  </SelectItem>
                ))}
              </SelectContent>
            </Select>
            {contractTemplates.length === 0 && <p className="text-sm text-muted-foreground">Nenhum modelo de contrato ativo para esta garantia. Cadastre um modelo com a modalidade selecionada.</p>}
            {fieldErrors.modelo && <p className="text-destructive text-xs">{fieldErrors.modelo}</p>}
          </div>
        </div>

        {guaranteeType === "Caução" && (
          <div className="grid gap-4 md:grid-cols-2 w-full">
            <div className="space-y-2 w-full">
              <label className="text-sm font-medium">Valor da Caução (R$)</label>
              <Input className="w-full" type="text" inputMode="decimal" placeholder="Ex.: 4.500,00 (recomendado: até 3 aluguéis)" value={depositAmount} onChange={(e) => guarantees.setDepositAmount(e.target.value)} />
              {fieldErrors.guarantees && <p className="text-destructive text-xs">{fieldErrors.guarantees}</p>}
            </div>
          </div>
        )}

        {guaranteeType === "Fiador" && (
          <div className="p-4 border rounded-md bg-muted/10 space-y-4 animate-in fade-in duration-300">
            <div className="flex justify-between items-center">
              <h4 className="font-semibold text-sm">Qualificação do(s) Fiador(es)</h4>
              <Button variant="outline" size="sm" onClick={addGuarantor} disabled={guarantors.length >= 2}>
                <Plus className="mr-1" />
                Adicionar Fiador
              </Button>
            </div>
            {guarantors.length === 0 ? (
              <p className="text-sm text-muted-foreground">Nenhum fiador adicionado.</p>
            ) : (
              guarantors.map((f, i) => (
                <div key={i} className="flex flex-col gap-3 p-3 bg-background border rounded-md shadow-sm relative">
                  <div className="flex justify-between items-center mb-1">
                    <span className="text-xs font-bold text-muted-foreground uppercase">Fiador {i + 1}</span>
                    <Button variant="ghost" size="icon" className="h-6 w-6 text-destructive" onClick={() => removeGuarantor(i)}><Trash size={14} /></Button>
                  </div>
                  <div className="grid md:grid-cols-3 gap-3 w-full">
                    <div className="space-y-1">
                      <label className="text-xs">Nome</label>
                      <Input className="h-8 text-sm" value={f.nome} onChange={(e) => updateGuarantor(i, "nome", e.target.value)} placeholder="Nome completo" />
                    </div>
                    <div className="space-y-1">
                      <label className="text-xs">CPF</label>
                      <Input className="h-8 text-sm" value={f.cpf} onChange={(e) => updateGuarantor(i, "cpf", e.target.value)} placeholder="000.000.000-00" />
                    </div>
                    <div className="space-y-1">
                      <label className="text-xs">Telefone</label>
                      <Input className="h-8 text-sm" value={f.telefone} onChange={(e) => updateGuarantor(i, "telefone", e.target.value)} placeholder="(00) 00000-0000" />
                    </div>
                  </div>
                </div>
              ))
            )}
            {fieldErrors.guarantees && <p className="text-destructive text-xs">{fieldErrors.guarantees}</p>}
          </div>
        )}

        {(guaranteeType === "Caução" || guaranteeType === "Fiador" || guaranteeType === "Seguro-fiança" || guaranteeType === "Título de capitalização") && (
          <div className="p-4 border border-dashed rounded-md bg-muted/5 flex flex-col items-center justify-center gap-2 mt-4">
            {attachmentStatus === "idle" && (
              <>
                <UploadSimple size={32} className="text-muted-foreground" />
                <p className="text-sm text-center font-medium">Anexar Comprovante de Garantia Obrigatório</p>
                <p className="text-xs text-muted-foreground text-center">
                  {guaranteeType === "Fiador" ? "Comprovante de renda e residência, certidão de casamento." : "Comprovante de depósito, apólice ou título."}
                </p>
                <label className="mt-2 cursor-pointer inline-flex items-center justify-center whitespace-nowrap rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 bg-primary text-primary-foreground hover:bg-primary/90 h-10 px-4 py-2">
                  Selecionar Arquivo
                  <input
                type="file"
                className="hidden"
                onChange={handleAttachmentChange}
              />
                </label>
              </>
            )}
            {attachmentStatus === "uploading" && (
              <div className="flex flex-col items-center gap-2 py-4">
                <Spinner size={32} className="animate-spin text-primary" />
                <p className="text-sm text-muted-foreground">Enviando documento...</p>
              </div>
            )}
            {attachmentStatus === "done" && attachment && (
              <div className="flex items-center justify-between w-full p-3 border rounded-md bg-background shadow-sm mt-2">
                <div className="flex items-center gap-3 overflow-hidden">
                  <div className="h-10 w-10 bg-emerald-100 rounded-md flex items-center justify-center shrink-0">
                    <Check size={20} className="text-emerald-600" />
                  </div>
                  <div className="flex flex-col overflow-hidden">
                    <span className="text-sm font-medium truncate" title={attachment.name}>{attachment.name}</span>
                    <span className="text-xs text-muted-foreground">Documento anexado</span>
                  </div>
                </div>
                <div className="flex items-center gap-1 shrink-0 ml-4">
                  <Button type="button" variant="ghost" size="icon" className="h-8 w-8 text-muted-foreground hover:text-foreground" onClick={(e) => { e.preventDefault(); window.open(URL.createObjectURL(attachment), "_blank"); }} title="Visualizar">
                    <Eye size={18} />
                  </Button>
                  <label className="inline-flex items-center justify-center rounded-md text-sm font-medium transition-colors focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:pointer-events-none disabled:opacity-50 hover:bg-accent hover:text-accent-foreground h-8 w-8 text-muted-foreground hover:text-foreground cursor-pointer" title="Substituir">
                    <UploadSimple size={18} />
                    <input
                type="file"
                className="hidden"
                onChange={handleAttachmentChange}
              />
                  </label>
                  <Button type="button" variant="ghost" size="icon" className="h-8 w-8 text-destructive hover:bg-destructive/10 hover:text-destructive" onClick={(e) => { e.preventDefault(); clearAttachment(); }} title="Remover">
                    <Trash size={18} />
                  </Button>
                </div>
              </div>
            )}
            {fieldErrors.upload && <p className="text-destructive text-xs mt-2">{fieldErrors.upload}</p>}
          </div>
        )}

        {fieldErrors.guarantees && guaranteeType !== "Caução" && guaranteeType !== "Fiador" && (
          <p className="text-destructive text-xs">{fieldErrors.guarantees}</p>
        )}
      </div>
    </div>
  );
}
