import { Badge } from "@/components/ui/badge";
import { Button } from "@/components/ui/button";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { MapPin, Ruler, Bed, Bathtub, Car, CurrencyDollar, FileText, MapTrifold, House, Drop, Lightning, Flame, WifiHigh, Notebook, CalendarPlus, Trash, CheckCircle, CalendarX, Plus } from "@phosphor-icons/react";
import { formatCurrency, formatDate } from "@/lib/formatters";
import type { AgendamentoVisita, AnotacaoImovel, Imovel } from "../data/imovel-types";

function hasComodidade(imovel: Imovel, comodidade: string): boolean {
  return imovel.caracteristicas.some(c => c.toLowerCase().includes(comodidade.toLowerCase()));
}

interface ImovelDetalhesTabProps {
  imovel: Imovel;
  statusCalculado: string;
  statusColor: string;
  anotacoes: AnotacaoImovel[];
  agendamentos: AgendamentoVisita[];
  aoAbrirAnotacao: () => void;
  aoAbrirAgendamento: () => void;
  aoRemoverAnotacao: (anotacaoId: string) => void;
  aoMudarStatusAgendamento: (agendamentoId: string, status: AgendamentoVisita["status"]) => void;
}

export function ImovelDetalhesTab({
  imovel,
  statusCalculado,
  statusColor,
  anotacoes,
  agendamentos,
  aoAbrirAnotacao,
  aoAbrirAgendamento,
  aoRemoverAnotacao,
  aoMudarStatusAgendamento,
}: ImovelDetalhesTabProps) {
  return (
    <div className="space-y-6 mt-4 animate-in fade-in zoom-in-95 duration-200">
      <div className="rounded-xl border p-6 space-y-3">
        <Badge className={statusColor}>{statusCalculado}</Badge>
        <h1 className="text-2xl font-semibold">{imovel.titulo || `Imóvel #${imovel.codigo}`}</h1>
        <p className="flex items-center gap-2"><MapPin size={18} />{imovel.endereco.logradouro}, {imovel.endereco.cidade}</p>
      </div>

      <div className="space-y-6">
        <div className="grid grid-cols-2 lg:grid-cols-4 gap-4">
          <Card className="shadow-sm border-border/50 hover:border-primary/30 transition-colors">
            <CardContent className="p-4 flex items-center gap-4">
              <div className="w-12 h-12 rounded-xl bg-emerald-500/10 flex items-center justify-center text-emerald-600 dark:text-emerald-400 shrink-0">
                <Ruler size={24} />
              </div>
              <div>
                <p className="text-[10px] text-muted-foreground uppercase tracking-widest font-bold">Área</p>
                <p className="text-xl font-bold text-foreground mt-0.5">{imovel.area} <span className="text-sm font-normal text-muted-foreground">m²</span></p>
              </div>
            </CardContent>
          </Card>
          
          <Card className="shadow-sm border-border/50 hover:border-primary/30 transition-colors">
            <CardContent className="p-4 flex items-center gap-4">
              <div className="w-12 h-12 rounded-xl bg-blue-500/10 flex items-center justify-center text-blue-600 dark:text-blue-400 shrink-0">
                <Bed size={24} />
              </div>
              <div>
                <p className="text-[10px] text-muted-foreground uppercase tracking-widest font-bold">Quartos</p>
                <p className="text-xl font-bold text-foreground mt-0.5">{imovel.quartos}</p>
              </div>
            </CardContent>
          </Card>
          
          <Card className="shadow-sm border-border/50 hover:border-primary/30 transition-colors">
            <CardContent className="p-4 flex items-center gap-4">
              <div className="w-12 h-12 rounded-xl bg-orange-500/10 flex items-center justify-center text-orange-600 dark:text-orange-400 shrink-0">
                <Bathtub size={24} />
              </div>
              <div>
                <p className="text-[10px] text-muted-foreground uppercase tracking-widest font-bold">Banheiros</p>
                <p className="text-xl font-bold text-foreground mt-0.5">{imovel.banheiros}</p>
              </div>
            </CardContent>
          </Card>
          
          <Card className="shadow-sm border-border/50 hover:border-primary/30 transition-colors">
            <CardContent className="p-4 flex items-center gap-4">
              <div className="w-12 h-12 rounded-xl bg-purple-500/10 flex items-center justify-center text-purple-600 dark:text-purple-400 shrink-0">
                <Car size={24} />
              </div>
              <div>
                <p className="text-[10px] text-muted-foreground uppercase tracking-widest font-bold">Vagas</p>
                <p className="text-xl font-bold text-foreground mt-0.5">{imovel.vagas}</p>
              </div>
            </CardContent>
          </Card>
        </div>

        <div className="grid gap-6 lg:grid-cols-3">
          <div className="lg:col-span-2 space-y-6">
            <Card className="border-border/50 shadow-sm">
              <CardHeader className="bg-muted/20 border-b border-border/50 pb-4">
                <CardTitle className="text-base flex items-center gap-2"><CurrencyDollar size={20} className="text-primary"/> Valores e Encargos</CardTitle>
              </CardHeader>
              <CardContent className="p-5 space-y-4">
                <div className="flex items-center justify-between border-b border-border/50 pb-3">
                  <span className="text-sm font-semibold text-muted-foreground">Aluguel Base</span>
                  <span className="font-bold">{formatCurrency(imovel.valorAluguel)}/mês</span>
                </div>
                <div className="flex items-center justify-between border-b border-border/50 pb-3">
                  <span className="text-sm font-semibold text-muted-foreground">Condomínio</span>
                  <span className="font-bold">{imovel.valorCondominio ? `${formatCurrency(imovel.valorCondominio)}/mês` : "Não informado"}</span>
                </div>
                <div className="flex items-center justify-between border-b border-border/50 pb-3">
                  <span className="text-sm font-semibold text-muted-foreground">IPTU</span>
                  <span className="font-bold">{imovel.valorIptu ? `${formatCurrency(imovel.valorIptu)}/ano` : "Não informado"}</span>
                </div>
                
                <div className="pt-2">
                  <div className="flex items-center justify-between p-3 rounded-lg bg-primary/5 border border-primary/20">
                    <span className="text-sm font-bold text-primary">Total Mensal Estimado</span>
                    <span className="font-bold text-primary text-lg">
                      {formatCurrency(imovel.valorAluguel + (imovel.valorCondominio || 0) + ((imovel.valorIptu || 0) / 12))}
                    </span>
                  </div>
                  <p className="text-[10px] text-muted-foreground mt-2 text-center">O total inclui Aluguel, Condomínio e proporção mensal do IPTU.</p>
                </div>
              </CardContent>
            </Card>

            <Card className="border-border/50 shadow-sm overflow-hidden">
              <CardHeader className="bg-muted/20 border-b border-border/50 pb-4">
                 <CardTitle className="text-base flex items-center gap-2"><FileText size={20} className="text-primary"/> Sobre o Imóvel</CardTitle>
              </CardHeader>
              <CardContent className="p-6">
                 <p className="text-sm text-muted-foreground leading-relaxed">
                   {imovel.descricao}
                 </p>
              </CardContent>
            </Card>

            <Card className="border-border/50 shadow-sm overflow-hidden">
              <CardHeader className="bg-muted/20 border-b border-border/50 pb-4">
                <CardTitle className="text-base flex items-center gap-2"><Notebook size={20} className="text-primary"/> Anotações</CardTitle>
                <div data-slot="card-action">
                  <Button variant="outline" size="sm" className="gap-1.5 h-8 text-xs" onClick={aoAbrirAnotacao}>
                    <Plus size={14} /> Nova anotação
                  </Button>
                </div>
              </CardHeader>
              <CardContent className="p-4 space-y-3">
                {anotacoes.length === 0 ? (
                  <p className="text-sm text-muted-foreground py-2">
                    Nenhuma anotação registrada para este imóvel.
                  </p>
                ) : (
                  anotacoes.map((anotacao) => (
                    <div
                      key={anotacao.id}
                      className="flex items-start justify-between gap-3 rounded-xl border bg-muted/10 p-3.5"
                    >
                      <div className="min-w-0">
                        <p className="text-sm whitespace-pre-wrap break-words">{anotacao.texto}</p>
                        <p className="text-[11px] text-muted-foreground mt-1.5">
                          {anotacao.autorNome} • {formatDate(anotacao.criadoEm)}
                        </p>
                      </div>
                      <Button
                        variant="ghost"
                        size="icon"
                        className="text-muted-foreground hover:text-destructive shrink-0"
                        aria-label="Excluir anotação"
                        onClick={() => void aoRemoverAnotacao(anotacao.id)}
                      >
                        <Trash size={16} />
                      </Button>
                    </div>
                  ))
                )}
              </CardContent>
            </Card>

            <Card className="border-border/50 shadow-sm overflow-hidden">
              <CardHeader className="bg-muted/20 border-b border-border/50 pb-4">
                <CardTitle className="text-base flex items-center gap-2"><CalendarPlus size={20} className="text-primary"/> Visitas Agendadas</CardTitle>
                <div data-slot="card-action">
                  <Button variant="outline" size="sm" className="gap-1.5 h-8 text-xs" onClick={aoAbrirAgendamento}>
                    <Plus size={14} /> Agendar visita
                  </Button>
                </div>
              </CardHeader>
              <CardContent className="p-4 space-y-3">
                {agendamentos.length === 0 ? (
                  <p className="text-sm text-muted-foreground py-2">
                    Nenhuma visita agendada para este imóvel.
                  </p>
                ) : (
                  agendamentos.map((visita) => (
                    <div
                      key={visita.id}
                      className="flex items-center justify-between gap-3 rounded-xl border bg-muted/10 p-3.5"
                    >
                      <div className="min-w-0">
                        <p className="text-sm font-semibold truncate">
                          {visita.interessado}
                        </p>
                        <p className="text-[11px] text-muted-foreground mt-0.5">
                          {formatDate(visita.data)} às {visita.hora}
                          {visita.telefone ? ` • ${visita.telefone}` : ""}
                        </p>
                      </div>
                      <div className="flex items-center gap-1.5 shrink-0">
                        <Badge
                          variant={
                            visita.status === "Cancelado"
                              ? "destructive"
                              : visita.status === "Realizado"
                                ? "default"
                                : "secondary"
                          }
                          className="text-[10px] uppercase font-bold tracking-wider"
                        >
                          {visita.status}
                        </Badge>
                        {visita.status === "Agendado" && (
                          <>
                            <Button
                              variant="ghost"
                              size="icon"
                              className="text-muted-foreground hover:text-emerald-600"
                              aria-label="Marcar visita como realizada"
                              onClick={() => void aoMudarStatusAgendamento(visita.id, "Realizado")}
                            >
                              <CheckCircle size={16} />
                            </Button>
                            <Button
                              variant="ghost"
                              size="icon"
                              className="text-muted-foreground hover:text-destructive"
                              aria-label="Cancelar visita"
                              onClick={() => void aoMudarStatusAgendamento(visita.id, "Cancelado")}
                            >
                              <CalendarX size={16} />
                            </Button>
                          </>
                        )}
                      </div>
                    </div>
                  ))
                )}
              </CardContent>
            </Card>

            <Card className="border-border/50 shadow-sm overflow-hidden">
              <CardHeader className="bg-muted/20 border-b border-border/50 pb-4">
                  <CardTitle className="text-base flex items-center gap-2"><MapPin size={20} className="text-primary"/> Localização Detalhada e Mapa</CardTitle>
              </CardHeader>
              <CardContent className="p-0">
                  <div className="p-6 grid grid-cols-2 md:grid-cols-3 gap-4 border-b border-border/50">
                    <div className="flex flex-col">
                      <span className="text-[11px] text-muted-foreground uppercase font-bold tracking-wider">CEP</span>
                      <span className="text-sm font-medium">{imovel.endereco.cep ? imovel.endereco.cep.replace(/^(\d{5})(\d{3})/, "$1-$2") : "Não informado"}</span>
                    </div>
                    <div className="flex flex-col md:col-span-2">
                      <span className="text-[11px] text-muted-foreground uppercase font-bold tracking-wider">Logradouro</span>
                      <span className="text-sm font-medium">{imovel.endereco.logradouro || "Não informado"}</span>
                    </div>
                    <div className="flex flex-col">
                      <span className="text-[11px] text-muted-foreground uppercase font-bold tracking-wider">Número</span>
                      <span className="text-sm font-medium">{imovel.endereco.numero || "S/N"}</span>
                    </div>
                    <div className="flex flex-col">
                      <span className="text-[11px] text-muted-foreground uppercase font-bold tracking-wider">Complemento</span>
                      <span className="text-sm font-medium">{imovel.endereco.complemento || "-"}</span>
                    </div>
                    <div className="flex flex-col">
                      <span className="text-[11px] text-muted-foreground uppercase font-bold tracking-wider">Bairro</span>
                      <span className="text-sm font-medium">{imovel.endereco.bairro || "Não informado"}</span>
                    </div>
                    <div className="flex flex-col">
                      <span className="text-[11px] text-muted-foreground uppercase font-bold tracking-wider">Cidade/UF</span>
                      <span className="text-sm font-medium">{imovel.endereco.cidade}{imovel.endereco.estado ? ` - ${imovel.endereco.estado}` : ""}</span>
                    </div>
                  </div>
                  <iframe
                    width="100%"
                    height="300"
                    style={{ border: 0 }}
                    loading="lazy"
                    allowFullScreen
                    referrerPolicy="no-referrer-when-downgrade"
                    src={`https://www.google.com/maps?q=${encodeURIComponent(`${imovel.endereco.logradouro}, ${imovel.endereco.numero}, ${imovel.endereco.bairro}, ${imovel.endereco.cidade}, ${imovel.endereco.estado}`)}&output=embed`}
                  ></iframe>
              </CardContent>
            </Card>
          </div>

          <div className="space-y-6">
            <Card className="border-border/50 shadow-sm">
              <CardHeader className="bg-muted/20 border-b border-border/50 pb-4">
                <CardTitle className="text-base flex items-center gap-2"><MapTrifold size={20} className="text-primary"/> Documentação</CardTitle>
              </CardHeader>
              <CardContent className="p-6">
                <div className="p-6 rounded-xl border-2 border-dashed border-border/60 bg-muted/10 flex flex-col items-center justify-center text-center">
                  <MapTrifold size={32} className="text-muted-foreground mb-3 opacity-60" />
                  <p className="text-lg font-bold text-foreground font-mono tracking-widest">{imovel.codigo || "Não informada"}</p>
                  <p className="text-[10px] text-muted-foreground uppercase tracking-widest mt-1.5 font-semibold">Matrícula no Cartório</p>
                </div>
              </CardContent>
            </Card>

            <Card className="border-border/50 shadow-sm">
              <CardHeader className="bg-muted/20 border-b border-border/50 pb-4">
                <CardTitle className="text-base flex items-center gap-2"><House size={20} className="text-primary"/> Comodidades Inclusas</CardTitle>
              </CardHeader>
              <CardContent className="p-5 space-y-3">
                <div className="flex items-center justify-between p-3.5 rounded-xl border bg-muted/10 hover:bg-muted/30 transition-colors">
                    <div className="flex items-center gap-3.5">
                      <div className={`p-2.5 rounded-lg ${hasComodidade(imovel, 'agua') ? 'bg-blue-500/15 text-blue-500' : 'bg-muted text-muted-foreground'}`}>
                          <Drop size={20} />
                      </div>
                      <span className="font-semibold text-sm">Água</span>
                    </div>
                    <Badge variant={hasComodidade(imovel, 'agua') ? "default" : "secondary"} className="text-[10px] uppercase font-bold tracking-wider">{hasComodidade(imovel, 'agua') ? "Inclusa" : "Não Inclusa"}</Badge>
                </div>
                
                <div className="flex items-center justify-between p-3.5 rounded-xl border bg-muted/10 hover:bg-muted/30 transition-colors">
                    <div className="flex items-center gap-3.5">
                      <div className={`p-2.5 rounded-lg ${hasComodidade(imovel, 'energia') ? 'bg-yellow-500/15 text-yellow-600 dark:text-yellow-500' : 'bg-muted text-muted-foreground'}`}>
                          <Lightning size={20} />
                      </div>
                      <span className="font-semibold text-sm">Energia</span>
                    </div>
                    <Badge variant={hasComodidade(imovel, 'energia') ? "default" : "secondary"} className="text-[10px] uppercase font-bold tracking-wider">{hasComodidade(imovel, 'energia') ? "Inclusa" : "Não Inclusa"}</Badge>
                </div>
                
                <div className="flex items-center justify-between p-3.5 rounded-xl border bg-muted/10 hover:bg-muted/30 transition-colors">
                    <div className="flex items-center gap-3.5">
                      <div className={`p-2.5 rounded-lg ${hasComodidade(imovel, 'gas') ? 'bg-orange-500/15 text-orange-600 dark:text-orange-500' : 'bg-muted text-muted-foreground'}`}>
                          <Flame size={20} />
                      </div>
                      <span className="font-semibold text-sm">Gás</span>
                    </div>
                    <Badge variant={hasComodidade(imovel, 'gas') ? "default" : "secondary"} className="text-[10px] uppercase font-bold tracking-wider">{hasComodidade(imovel, 'gas') ? "Inclusa" : "Não Inclusa"}</Badge>
                </div>
                
                <div className="flex items-center justify-between p-3.5 rounded-xl border bg-muted/10 hover:bg-muted/30 transition-colors">
                    <div className="flex items-center gap-3.5">
                      <div className={`p-2.5 rounded-lg ${hasComodidade(imovel, 'internet') ? 'bg-purple-500/15 text-purple-500' : 'bg-muted text-muted-foreground'}`}>
                          <WifiHigh size={20} />
                      </div>
                      <span className="font-semibold text-sm">Internet</span>
                    </div>
                    <Badge variant={hasComodidade(imovel, 'internet') ? "default" : "secondary"} className="text-[10px] uppercase font-bold tracking-wider">{hasComodidade(imovel, 'internet') ? "Inclusa" : "Não Inclusa"}</Badge>
                </div>
              </CardContent>
            </Card>

          </div>
        </div>
      </div>
    </div>
  );
}
