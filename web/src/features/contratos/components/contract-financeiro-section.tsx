import { Input } from "@/components/ui/input";
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from "@/components/ui/select";
import { formatCurrency } from "@/lib/formatters";
import { type CreateContractHookData } from "../hooks/use-create-contract";

export function ContractFinanceiroSection({ data }: { data: CreateContractHookData }) {
  const {
    properties,
    propertyId,
    termType,
    setTermType,
    startDate,
    setStartDate,
    endDate,
    setEndDate,
    monthlyDiscount,
    setMonthlyDiscount,
    adjustmentIndex,
    setAdjustmentIndex,
    fieldErrors,
  } = data;

  return (
    <div className="space-y-6">
      <h3 className="text-lg font-semibold flex items-center gap-2">Prazos e Acordos Financeiros</h3>
      <div className="grid gap-4 md:grid-cols-2 w-full">
        <div className="space-y-2 w-full">
          <label className="text-sm font-medium">Prazo de Vigência</label>
          <Select value={termType} onValueChange={(v) => { if (v != null) setTermType(v); }}>
            <SelectTrigger className="w-full">
              <SelectValue>
                {termType === "Determinado" ? "Prazo Determinado" : "Prazo Indeterminado"}
              </SelectValue>
            </SelectTrigger>
            <SelectContent>
              <SelectItem value="Determinado">Prazo Determinado</SelectItem>
              <SelectItem value="Indeterminado">Prazo Indeterminado</SelectItem>
            </SelectContent>
          </Select>
          {fieldErrors.vigencia && <p className="text-destructive text-xs">{fieldErrors.vigencia}</p>}
        </div>
        <div className="space-y-2 w-full">
          <label className="text-sm font-medium">Data de Início</label>
          <Input className="w-full" type="date" value={startDate} onChange={(e) => setStartDate(e.target.value)} />
          {fieldErrors.startDate && <p className="text-destructive text-xs">{fieldErrors.startDate}</p>}
        </div>
        {termType === "Determinado" && (
          <div className="space-y-2 w-full">
            <label className="text-sm font-medium">Data de Término *</label>
            <Input className="w-full" type="date" value={endDate} onChange={(e) => setEndDate(e.target.value)} />
            {fieldErrors.endDate && <p className="text-destructive text-xs">{fieldErrors.endDate}</p>}
          </div>
        )}
      </div>

      <div className="grid gap-4 md:grid-cols-2 w-full">
        <div className="space-y-2 w-full">
          <label className="text-sm font-medium">Desconto Mensal / Acordo (R$)</label>
          <Input className="w-full" type="text" inputMode="decimal" placeholder="Ex.: 150,00 (opcional)" value={monthlyDiscount} onChange={(e) => setMonthlyDiscount(e.target.value)} />
          {fieldErrors.desconto && <p className="text-destructive text-xs">{fieldErrors.desconto}</p>}
        </div>
        <div className="space-y-2 w-full">
          <label className="text-sm font-medium">Índice de Reajuste Anual</label>
          <Select value={adjustmentIndex} onValueChange={(v) => { if (v != null) setAdjustmentIndex(v); }}>
            <SelectTrigger className="w-full">
              <SelectValue>
                {adjustmentIndex}
              </SelectValue>
            </SelectTrigger>
            <SelectContent>
              <SelectItem value="IGP-M">IGP-M</SelectItem>
              <SelectItem value="IPCA">IPCA</SelectItem>
              <SelectItem value="INPC">INPC</SelectItem>
              <SelectItem value="IPET">IPET</SelectItem>
            </SelectContent>
          </Select>
          {fieldErrors.reajuste && <p className="text-destructive text-xs">{fieldErrors.reajuste}</p>}
        </div>
      </div>
      
      {propertyId && properties.find(i => i.id === propertyId) && (
        <div className="p-4 bg-emerald-50 dark:bg-emerald-950/20 border border-emerald-200 dark:border-emerald-900 rounded-md flex items-center justify-between">
          <div className="flex flex-col">
            <span className="text-xs font-semibold text-emerald-800 dark:text-emerald-400 uppercase tracking-wide">Valor Final da Locação</span>
            <span className="text-2xl font-bold text-emerald-700 dark:text-emerald-500">
              {formatCurrency(properties.find(i => i.id === propertyId)!.valorAluguel - (Number(monthlyDiscount) || 0))}
            </span>
          </div>
          {(Number(monthlyDiscount) > 0) && (
            <div className="flex flex-col text-right">
              <span className="text-xs text-muted-foreground line-through">{formatCurrency(properties.find(i => i.id === propertyId)!.valorAluguel)}</span>
              <span className="text-xs font-medium text-emerald-700 dark:text-emerald-500">Desconto aplicado</span>
            </div>
          )}
        </div>
      )}
    </div>
  );
}
