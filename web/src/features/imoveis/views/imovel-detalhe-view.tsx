"use client";

import { ArrowLeft, ArrowClockwise, House, FileText, Camera, CurrencyDollar, WarningCircle } from "@phosphor-icons/react";
import { Badge } from "@/components/ui/badge";
import { Button } from "@/components/ui/button";
import { Card, CardContent } from "@/components/ui/card";
import { Skeleton } from "@/components/ui/skeleton";
import { Tabs, TabsContent } from "@/components/ui/tabs";
import { PageTabsList, PageTabsTrigger } from "@/components/ui/page-tabs";

import { ImovelFotos } from "../components/imovel-fotos";
import { useImovelDetalhe } from "../hooks/use-property-detail";
import { ImovelDetalhesTab } from "../components/imovel-detalhes-tab";
import { ImovelContratosTab } from "../components/imovel-contratos-tab";
import { ImovelFinanceiroTab } from "../components/imovel-financeiro-tab";
import { ImovelVistoriasTab } from "../components/imovel-vistorias-tab";

import { ModalNovaAnotacao } from "../components/modal-nova-anotacao";
import { ModalNovoAgendamento } from "../components/modal-novo-agendamento";

export function ImovelDetalheView({ idInicial }: { idInicial: string }) {
  const st = useImovelDetalhe(idInicial);

  if (!st.imovel) {
    return (
      <div className="flex flex-col gap-4">
        <Button variant="outline" onClick={() => st.router.push("/imoveis")} className="w-fit">
          <ArrowLeft size={16} />
          Voltar
        </Button>
        <Card>
          <CardContent className="py-12 flex flex-col items-center gap-3 text-center">
            {st.error ? (
              <>
                <WarningCircle size={28} className="text-destructive" />
                <p className="text-sm text-destructive">{st.error}</p>
                <Button variant="outline" size="sm" className="gap-2" onClick={st.retry}>
                  <ArrowClockwise size={14} /> Tentar novamente
                </Button>
              </>
            ) : (
              <div className="space-y-4 w-full">
                <Skeleton className="h-8 w-1/3 mx-auto" />
                <Skeleton className="h-6 w-2/3 mx-auto" />
                <Skeleton className="h-64 w-full" />
              </div>
            )}
          </CardContent>
        </Card>
      </div>
    );
  }

  return (
    <div className="flex flex-col gap-4">
      <Tabs defaultValue="detalhes" className="w-full">
        <PageTabsList
          actions={
            <div className="flex items-center gap-2">
              <span className="text-xs text-muted-foreground font-medium hidden sm:inline">Status:</span>
              <Badge className={`text-xs px-3 py-1 font-semibold uppercase tracking-wider shadow-xs ${st.statusColor}`}>
                {st.statusCalculado}
              </Badge>
            </div>
          }
        >
          <PageTabsTrigger value="detalhes"><House size={15}/> Detalhes e Fotos</PageTabsTrigger>
          <PageTabsTrigger value="contratos"><FileText size={15}/> Histórico de Contratos</PageTabsTrigger>
          <PageTabsTrigger value="vistorias"><Camera size={15}/> Vistorias</PageTabsTrigger>
          <PageTabsTrigger value="financeiro"><CurrencyDollar size={15}/> Financeiro</PageTabsTrigger>
        </PageTabsList>

        <TabsContent value="detalhes">
          <ImovelFotos imovelId={idInicial} />
          <ImovelDetalhesTab
            imovel={st.imovel}
            statusCalculado={st.statusCalculado}
            statusColor={st.statusColor}
            anotacoes={st.anotacoes}
            agendamentos={st.agendamentos}
            aoAbrirAnotacao={() => st.setAbrirAnotacao(true)}
            aoAbrirAgendamento={() => st.setAbrirAgendamento(true)}
            aoRemoverAnotacao={(anotacaoId) => void st.removeAnotacao(anotacaoId)}
            aoMudarStatusAgendamento={(agendamentoId, status) =>
              void st.mudarStatusAgendamento(agendamentoId, status)
            }
          />
        </TabsContent>

        <TabsContent value="contratos">
          <ImovelContratosTab
            imovel={st.imovel}
            contratosDoImovel={st.contratosDoImovel}
            buscaContratos={st.buscaContratos}
            setBuscaContratos={st.setBuscaContratos}
            paginaContratos={st.paginaContratos}
            setPaginaContratos={st.setPaginaContratos}
            filtroContratos={st.filtroContratos}
            setFiltroContratos={st.setFiltroContratos}
            router={st.router}
          />
        </TabsContent>

        <TabsContent value="vistorias">
          <ImovelVistoriasTab imovel={st.imovel} />
        </TabsContent>

        <TabsContent value="financeiro">
          <ImovelFinanceiroTab
            imovel={st.imovel}
            contratoAtivo={st.contratoAtivo}
            buscaFinanceiro={st.buscaFinanceiro}
            setBuscaFinanceiro={st.setBuscaFinanceiro}
            paginaFinanceiro={st.paginaFinanceiro}
            setPaginaFinanceiro={st.setPaginaFinanceiro}
            filtroFinanceiro={st.filtroFinanceiro}
            setFiltroFinanceiro={st.setFiltroFinanceiro}
          />
        </TabsContent>
      </Tabs>

      <ModalNovaAnotacao
        aberto={st.abrirAnotacao}
        aoFechar={() => st.setAbrirAnotacao(false)}
        aoSalvar={(texto) => {
          void st.addAnotacao(texto);
          st.setAbrirAnotacao(false);
        }}
      />
      <ModalNovoAgendamento
        aberto={st.abrirAgendamento}
        aoFechar={() => st.setAbrirAgendamento(false)}
        aoSalvar={(dados) => {
          void st.addAgendamento(dados);
          st.setAbrirAgendamento(false);
        }}
      />
    </div>
  );
}
