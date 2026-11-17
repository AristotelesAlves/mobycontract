import { Input } from "@/components/ui/input";
import { type CreateContractHookData } from "../hooks/use-create-contract";

export function ContractMultasSection({ data }: { data: CreateContractHookData }) {
  const {
    lateFeePercent,
    setLateFeePercent,
    terminationFeePercent,
    setTerminationFeePercent,
    observations,
    setObservations,
    fieldErrors,
  } = data;

  return (
    <div className="space-y-6">
      <h3 className="text-lg font-semibold flex items-center gap-2">Multas e Observações Finais</h3>
      <div className="space-y-6">
        <div className="grid gap-4 md:grid-cols-2 w-full">
          <div className="space-y-2 w-full">
            <label className="text-sm font-medium">Multa Moratória (% ao mês)</label>
            <Input className="w-full" type="text" inputMode="decimal" placeholder="Ex.: 1,5" value={lateFeePercent} onChange={(e) => setLateFeePercent(e.target.value)} />
            <p className="text-xs text-muted-foreground">Juros moratórios incidentes em caso de atraso.</p>
            {fieldErrors.multaAtraso && <p className="text-destructive text-xs">{fieldErrors.multaAtraso}</p>}
          </div>
          <div className="space-y-2 w-full">
            <label className="text-sm font-medium">Multa Penal / Rescisória (Qtd. Aluguéis)</label>
            <Input className="w-full" type="number" placeholder="Ex: 3" value={terminationFeePercent} onChange={(e) => setTerminationFeePercent(e.target.value)} />
            <p className="text-xs text-muted-foreground">Aplicada proporcionalmente em rescisão antecipada.</p>
            {fieldErrors.multaRescisao && <p className="text-destructive text-xs">{fieldErrors.multaRescisao}</p>}
          </div>
        </div>

        <div className="space-y-2 w-full pt-4">
          <label className="text-sm font-medium">Outras Observações</label>
          <textarea
            className="flex min-h-[80px] w-full rounded-md border border-input bg-background px-3 py-2 text-sm shadow-sm focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring"
            placeholder="Quaisquer outras informações..."
            value={observations}
            onChange={(e) => setObservations(e.target.value)}
          />
          {fieldErrors.observacoes && <p className="text-destructive text-xs">{fieldErrors.observacoes}</p>}
        </div>
      </div>
    </div>
  );
}
