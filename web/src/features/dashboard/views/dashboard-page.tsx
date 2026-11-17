"use client";

import {
  FileText,
  Hexagon,
  HouseLine,
  Users,
  Plus,
  FilePlus,
  WarningCircle,
  ArrowClockwise,
} from "@phosphor-icons/react/dist/ssr"
import { useRouter } from "next/navigation"
import { useDashboardSummary } from "../hooks/use-dashboard-summary";

import { Card, CardContent, CardDescription, CardHeader, CardTitle } from "@/components/ui/card"
import { ChartPieDonutText } from "../components/novo/chart-pina"
import { DashboardMovimentacoesTable } from "../components/dashboard-movimentacoes-table"
import { formatCurrency } from "@/lib/formatters";
import { Button } from "@/components/ui/button";

export default function DashboardPage() {
  const router = useRouter();
  const { metrics, portfolioSlices, movements, loading, error, retry } =
    useDashboardSummary();

  if (error) {
    return (
      <div className="flex flex-col gap-6 w-full pb-10">
        <Card>
          <CardContent className="py-12 flex flex-col items-center gap-3 text-center">
            <WarningCircle size={28} className="text-destructive" />
            <p className="text-sm text-destructive">{error}</p>
            <Button variant="outline" size="sm" className="gap-2" onClick={retry}>
              <ArrowClockwise size={14} /> Tentar novamente
            </Button>
          </CardContent>
        </Card>
      </div>
    );
  }

  if (loading) {
    return (
      <div className="flex flex-col gap-6 w-full pb-10" aria-busy="true" aria-live="polite">
        <Card className="w-full shadow-sm border-border/60">
          <CardContent className="flex flex-col gap-3 py-12 items-center text-center">
            <p className="text-sm text-muted-foreground">Carregando indicadores do dashboard...</p>
          </CardContent>
        </Card>
      </div>
    );
  }

  return (
    <div className="flex flex-col gap-6 w-full pb-10">

      {/* TOP ROW: Main Account (Revenue) */}
      <div className="w-full">
        <Card className="w-full shadow-sm flex flex-col justify-between border-border/60">
          <CardHeader className="pb-2">
            <CardDescription className="uppercase tracking-widest font-semibold text-[11px]">Visão Geral da Receita</CardDescription>
            <CardTitle className="text-3xl font-extrabold tracking-tight text-foreground">Receita Mensal Esperada</CardTitle>
          </CardHeader>
          <CardContent className="pb-6">
            <p className="text-5xl font-black tracking-tighter text-emerald-600 dark:text-emerald-500 mt-2">{formatCurrency(metrics.monthlyRevenue)}</p>

            <div className="flex flex-wrap items-center gap-3 mt-8">
              <Button size="default" className="gap-2 bg-foreground text-background hover:bg-foreground/90 font-bold rounded-xl" onClick={() => router.push("/contratos/novo")}>
                <FilePlus weight="bold" size={16} /> Novo Contrato
              </Button>
              <Button size="default" variant="secondary" className="gap-2 font-bold rounded-xl" onClick={() => router.push("/imoveis/novo")}>
                <Plus weight="bold" size={16} /> Novo Imóvel
              </Button>
              <Button size="default" variant="outline" className="gap-2 font-bold rounded-xl" onClick={() => router.push("/inquilinos/novo")}>
                <Users weight="bold" size={16} /> Cadastrar Inquilino
              </Button>
            </div>
          </CardContent>
        </Card>
      </div>

      {/* MIDDLE ROW: 4 Metric Cards using standard Shadcn structure */}
      <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
        {[
          { title: "Contratos Ativos", value: metrics.activeContracts, icon: FileText, sub: "Vigentes ou Validados" },
          { title: "Imóveis Vagos", value: metrics.vacantProperties, icon: HouseLine, sub: "Disponíveis para locação" },
          { title: "Inquilinos", value: metrics.registeredTenants, icon: Users, sub: "Cadastrados para locação" },
          { title: "Smart Contracts", value: metrics.registeredSmartContracts, icon: Hexagon, sub: "Registrados na rede" },
        ].map((m, i) => (
          <Card key={i} className="shadow-sm">
            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
              <CardTitle className="text-sm font-semibold text-muted-foreground">
                {m.title}
              </CardTitle>
              <m.icon className="h-5 w-5 text-emerald-600 opacity-80" />
            </CardHeader>
            <CardContent>
              <div className="text-3xl font-bold tracking-tight">{m.value}</div>
              <p className="text-xs font-medium text-muted-foreground/80 mt-1">
                {m.sub}
              </p>
            </CardContent>
          </Card>
        ))}
      </div>

      {/* BOTTOM ROW: Tables and Charts */}
      <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {/* Movimentações */}
        <Card className="col-span-1 lg:col-span-2 flex flex-col shadow-sm">
          <CardHeader>
            <CardTitle className="text-xl">Últimas Movimentações</CardTitle>
            <CardDescription>Principais eventos e registros da plataforma e rede.</CardDescription>
          </CardHeader>
          <CardContent className="flex-1 p-0">
            <DashboardMovimentacoesTable movimentacoes={movements} />
          </CardContent>        </Card>

        {/* Resumo Donut */}
        <Card className="col-span-1 flex flex-col shadow-sm">
          <CardHeader>
            <CardTitle className="text-xl">Resumo de Imóveis</CardTitle>
            <CardDescription>Distribuição atual de status.</CardDescription>
          </CardHeader>
          <CardContent className="flex-1 p-0 pb-4">
             <ChartPieDonutText data={portfolioSlices} />
          </CardContent>
        </Card>

      </div>

    </div>
  )
}
