"use client";

import { downloadContractDocument } from "../../services/contract-service";

import { useState } from "react";
import Link from "next/link";
import {
  FileText,
  LinkSimple,
  User,
  UserSound,
  MapPin,
  House,
  Buildings,
  Ruler,
  Bed,
  Bathtub,
  Car,
  IdentificationCard,
  CalendarBlank,
  Scales,
  Receipt,
  Drop,
  Lightning,
  Flame,
  ShieldCheck,
  Check,
  Copy,
  ChatTeardropText,
  Tag,
  ArrowsClockwise,
  ArrowUpRight,
} from "@phosphor-icons/react";
import { Badge } from "@/components/ui/badge";
import { formatDate, formatCurrency } from "@/lib/formatters";
import type { Contrato } from "../../data/contrato-types";
import { CaucaoReceiptButton } from "../../components/caucao-receipt-button";

interface IContratoDetalhesCardProps {
  contrato: Contrato;
}

export function ContratoDetalhesCard({ contrato }: IContratoDetalhesCardProps) {
  const [copiado, setCopiado] = useState(false);

  const copiarHash = () => {
    if (!contrato.hashBlockchain) return;
    navigator.clipboard.writeText(contrato.hashBlockchain);
    setCopiado(true);
    setTimeout(() => setCopiado(false), 2000);
  };

  const isComercial =
    contrato.destinacao === "Comercial" || contrato.imovelTipo === "Comercial";
  const IconeImovel = isComercial ? Buildings : House;

  const temDesconto = Boolean(
    contrato.descontoMensal && contrato.descontoMensal > 0
  );
  const valorMensal = contrato.valorMensal ?? 0;
  const valorLiquido = temDesconto
    ? valorMensal - (contrato.descontoMensal ?? 0)
    : valorMensal;

  return (
    <div className="flex flex-col gap-6 w-full">
      <div className="rounded-xl border bg-card p-6 shadow-sm">
        <div className="flex flex-wrap items-center justify-between gap-3 pb-5 border-b border-border/60">
          <div className="flex items-center gap-2.5">
            <div className="p-2 rounded-lg bg-primary/10 text-primary">
              <FileText size={20} weight="bold" />
            </div>
            <div>
              <div className="flex items-center gap-2">
                <h3 className="text-base font-semibold tracking-tight text-foreground">
                  Contrato #{contrato.numero}
                </h3>
                <Badge variant="outline" className="font-mono text-xs">
                  {contrato.destinacao || "Residencial"}
                </Badge>
              </div>
              <p className="text-xs text-muted-foreground">
                Responsável: <span className="font-medium text-foreground">{contrato.responsavel}</span>
              </p>
            </div>
          </div>

          <div className="flex items-center gap-2">
            <span className="text-xs text-muted-foreground">Status do Contrato:</span>
            <Badge
              variant={
                contrato.status === "Validado"
                  ? "positive"
                  : contrato.status === "Pendente"
                  ? "secondary"
                  : "outline"
              }
              className="text-xs font-semibold px-2.5 py-0.5"
            >
              {contrato.status}
            </Badge>
          </div>
        </div>

        <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-4 pt-5">
          <div className="flex flex-col justify-between rounded-lg border bg-muted/20 p-4">
            <div>
              <p className="text-[10px] font-bold uppercase tracking-wider text-muted-foreground mb-1">
                Aluguel Mensal
              </p>
              <p className="text-2xl font-bold tracking-tight text-emerald-600 dark:text-emerald-400">
                {formatCurrency(valorLiquido)}
              </p>
            </div>
            {temDesconto && (
              <div className="mt-2 flex items-center gap-1.5 text-xs text-muted-foreground pt-1.5 border-t border-border/50">
                <Tag size={13} className="text-amber-500" />
                <span>Bruto: {formatCurrency(valorMensal)}</span>
                <span className="text-emerald-600 font-medium">
                  (-{formatCurrency(contrato.descontoMensal ?? 0)})
                </span>
              </div>
            )}
          </div>

          <div className="flex flex-col justify-between rounded-lg border bg-muted/20 p-4">
            <div>
              <div className="flex items-center justify-between mb-1">
                <p className="text-[10px] font-bold uppercase tracking-wider text-muted-foreground">
                  Vigência
                </p>
                <Badge variant="secondary" className="text-[10px] h-4 px-1.5 py-0 font-normal">
                  {contrato.prazoVigencia || "Determinado"}
                </Badge>
              </div>
              <div className="flex items-center gap-1.5 text-sm font-semibold text-foreground">
                <CalendarBlank size={16} className="text-primary shrink-0" />
                <span>
                  {formatDate(contrato.dataInicio)}
                </span>
              </div>
              <p className="text-xs text-muted-foreground pl-5.5">
                até {contrato.dataFim ? formatDate(contrato.dataFim) : "Indeterminado"}
              </p>
            </div>
          </div>

          <div className="flex flex-col justify-between rounded-lg border bg-muted/20 p-4">
            <div>
              <p className="text-[10px] font-bold uppercase tracking-wider text-muted-foreground mb-1">
                Garantia Locatícia
              </p>
              <div className="flex items-center gap-1.5">
                <ShieldCheck size={18} className="text-primary shrink-0" />
                <p className="text-base font-bold text-foreground">
                  {contrato.garantia}
                </p>
              </div>
              {contrato.valorCaucao ? (
                <p className="text-xs text-muted-foreground mt-1">
                  Caução: <span className="font-semibold text-foreground">{formatCurrency(contrato.valorCaucao)}</span>
                </p>
              ) : contrato.fiador ? (
                <p className="text-xs text-muted-foreground mt-1 truncate">
                  Fiador: <span className="font-semibold text-foreground">{contrato.fiador.nome}</span>
                </p>
              ) : (
                <p className="text-xs text-muted-foreground mt-1">
                  Multa atraso: {contrato.multaPorAtraso}% a.m.
                </p>
              )}
              <CaucaoReceiptButton contrato={contrato} />
            </div>
          </div>

          <div className="flex flex-col justify-between rounded-lg border bg-muted/20 p-4">
            <div>
              <div className="flex items-center justify-between mb-1">
                <p className="text-[10px] font-bold uppercase tracking-wider text-muted-foreground">
                  Blockchain
                </p>
                <span className="text-[10px] text-muted-foreground font-mono">
                  {contrato.redeBlockchain?.split(" ")[0] || "—"}
                </span>
              </div>
              <p className="text-sm font-semibold text-foreground flex items-center gap-1.5">
                <span
                  className={`size-2 rounded-full ${
                    contrato.statusBlockchain === "Registrado"
                      ? "bg-emerald-500 animate-pulse"
                      : "bg-amber-500"
                  }`}
                />
                {contrato.statusBlockchain}
              </p>
              {contrato.hashBlockchain ? (
                <div className="mt-2 flex items-center gap-1">
                  <span className="font-mono text-[10px] bg-background px-2 py-0.5 rounded border text-muted-foreground truncate max-w-[130px]">
                    {contrato.hashBlockchain}
                  </span>
                  <button
                    onClick={copiarHash}
                    title="Copiar hash blockchain"
                    className="p-1 rounded hover:bg-muted text-muted-foreground hover:text-foreground transition-colors"
                  >
                    {copiado ? (
                      <Check size={13} className="text-emerald-600" />
                    ) : (
                      <Copy size={13} />
                    )}
                  </button>
                </div>
              ) : (
                <p className="text-[11px] text-muted-foreground mt-2">
                  Aguardando emissão na rede
                </p>
              )}
            </div>
          </div>
        </div>
      </div>

      <div className="rounded-xl border bg-card p-6 shadow-sm">
        <div className="flex flex-wrap items-center justify-between gap-4 pb-4 border-b border-border/60">
          <div className="flex items-center gap-3">
            <div className="flex size-10 items-center justify-center rounded-lg bg-primary/10 text-primary">
              <IconeImovel size={22} weight="duotone" />
            </div>
            <div>
              <div className="flex items-center gap-2">
                <h4 className="text-base font-semibold text-foreground">
                  {contrato.imovelTitulo}
                </h4>
                {contrato.imovelTipo && (
                  <Badge variant="secondary" className="text-xs">
                    {contrato.imovelTipo}
                  </Badge>
                )}
              </div>
              {(contrato.imovelEndereco || contrato.imovelCidade) && (
                <p className="flex items-center gap-1 text-xs text-muted-foreground mt-0.5">
                  <MapPin size={14} className="text-primary shrink-0" />
                  {contrato.imovelEndereco}
                  {contrato.imovelCidade ? ` — ${contrato.imovelCidade}` : ""}
                </p>
              )}
            </div>
          </div>

          <Link
            href={`/imoveis/${contrato.imovelId}`}
            className="inline-flex items-center gap-1.5 rounded-lg border bg-background px-3 py-1.5 text-xs font-semibold text-primary hover:bg-accent transition-colors shadow-xs"
          >
            <LinkSimple size={14} />
            Ver Cadastro do Imóvel
            <ArrowUpRight size={12} className="text-muted-foreground" />
          </Link>
        </div>

        <div className="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-5 pt-4">
          <div className="flex items-center gap-2.5 rounded-lg border bg-muted/20 p-3">
            <div className="rounded-md bg-background p-2 text-primary border shadow-2xs">
              <Ruler size={18} />
            </div>
            <div>
              <p className="text-[10px] font-medium text-muted-foreground uppercase">Área Total</p>
              <p className="text-sm font-bold text-foreground">
                {contrato.imovelArea != null ? `${contrato.imovelArea} m²` : "—"}
              </p>
            </div>
          </div>

          <div className="flex items-center gap-2.5 rounded-lg border bg-muted/20 p-3">
            <div className="rounded-md bg-background p-2 text-primary border shadow-2xs">
              <Bed size={18} />
            </div>
            <div>
              <p className="text-[10px] font-medium text-muted-foreground uppercase">Quartos</p>
              <p className="text-sm font-bold text-foreground">
                {contrato.imovelQuartos ?? "—"}
              </p>
            </div>
          </div>

          <div className="flex items-center gap-2.5 rounded-lg border bg-muted/20 p-3">
            <div className="rounded-md bg-background p-2 text-primary border shadow-2xs">
              <Bathtub size={18} />
            </div>
            <div>
              <p className="text-[10px] font-medium text-muted-foreground uppercase">Banheiros</p>
              <p className="text-sm font-bold text-foreground">
                {contrato.imovelBanheiros ?? "—"}
              </p>
            </div>
          </div>

          <div className="flex items-center gap-2.5 rounded-lg border bg-muted/20 p-3">
            <div className="rounded-md bg-background p-2 text-primary border shadow-2xs">
              <Car size={18} />
            </div>
            <div>
              <p className="text-[10px] font-medium text-muted-foreground uppercase">Vagas</p>
              <p className="text-sm font-bold text-foreground">
                {contrato.imovelVagas ?? "—"}
              </p>
            </div>
          </div>

          <div className="flex items-center gap-2.5 rounded-lg border bg-muted/20 p-3 col-span-2 sm:col-span-1">
            <div className="rounded-md bg-background p-2 text-primary border shadow-2xs">
              <IdentificationCard size={18} />
            </div>
            <div className="truncate">
              <p className="text-[10px] font-medium text-muted-foreground uppercase">Matrícula</p>
              <p className="text-sm font-bold text-foreground truncate font-mono">
                {contrato.imovelMatricula || "—"}
              </p>
            </div>
          </div>
        </div>
      </div>

      <div className="grid gap-6 md:grid-cols-2">
        <div className="rounded-xl border bg-card p-6 shadow-sm flex flex-col justify-between">
          <div>
            <h4 className="text-sm font-semibold text-foreground flex items-center gap-2 pb-4 border-b border-border/60">
              <Scales size={18} className="text-primary" /> Cláusulas e Regras Contratuais
            </h4>

            <div className="grid grid-cols-2 gap-4 pt-4">
              <div>
                <p className="text-[10px] font-bold text-muted-foreground uppercase tracking-widest">
                  Destinação
                </p>
                <p className="text-sm font-medium text-foreground mt-0.5">
                  {contrato.destinacao || "Residencial"}
                </p>
              </div>

              <div>
                <p className="text-[10px] font-bold text-muted-foreground uppercase tracking-widest">
                  Reajuste Anual
                </p>
                <div className="flex items-center gap-1.5 mt-0.5">
                  <ArrowsClockwise size={14} className="text-primary" />
                  <span className="text-sm font-medium text-foreground">
                    Índice {contrato.indiceReajuste || "IPCA"}
                  </span>
                </div>
              </div>

              <div>
                <p className="text-[10px] font-bold text-muted-foreground uppercase tracking-widest">
                  Multa por Atraso
                </p>
                <p className="text-sm font-medium text-foreground mt-0.5">
                  {contrato.multaPorAtraso}% ao mês
                </p>
              </div>

              <div>
                <p className="text-[10px] font-bold text-muted-foreground uppercase tracking-widest">
                  Multa de Rescisão
                </p>
                <p className="text-sm font-medium text-foreground mt-0.5">
                  {contrato.multaRescisao ?? 3} aluguéis proporcionais
                </p>
              </div>

              <div className="col-span-2 pt-2 border-t border-border/40">
                <p className="text-[10px] font-bold text-muted-foreground uppercase tracking-widest">
                  Modelo Vinculado
                </p>
                <p className="text-xs font-medium text-foreground mt-0.5">
                  {contrato.modeloTitulo || "Modelo Base de Contrato Residencial"}
                </p>
                {["Caução", "Fiador"].includes(contrato.garantia ?? "") && <button type="button" className="text-xs underline block mt-2" onClick={() => downloadContractDocument(contrato.id, undefined, true).catch(() => window.alert("Comprovante não disponível."))}>Baixar comprovante da garantia</button>}
                {contrato.anexoContratoNome && (
                  <p className="text-xs text-primary flex items-center gap-1 mt-1">
                    <FileText size={13} />
                    <button type="button" className="underline" onClick={() => downloadContractDocument(contrato.id).catch(() => window.alert("Não foi possível baixar o anexo."))}>Baixar anexo: {contrato.anexoContratoNome}</button>
                  </p>
                )}
              </div>
            </div>
          </div>
        </div>

        <div className="rounded-xl border bg-card p-6 shadow-sm flex flex-col justify-between">
          <div>
            <h4 className="text-sm font-semibold text-foreground flex items-center gap-2 pb-4 border-b border-border/60">
              <Receipt size={18} className="text-primary" /> Encargos & Despesas de Consumo
            </h4>

            <div className="grid grid-cols-2 gap-4 pt-4">
              <div>
                <p className="text-[10px] font-bold text-muted-foreground uppercase tracking-widest">
                  IPTU
                </p>
                <p className="text-sm font-semibold text-foreground mt-0.5">
                  {contrato.responsabilidadeIptu || "Inquilino"}
                </p>
                {contrato.valorEstimadoIptu ? (
                  <p className="text-xs text-muted-foreground">
                    Estimado: {formatCurrency(contrato.valorEstimadoIptu)}
                  </p>
                ) : null}
              </div>

              <div>
                <p className="text-[10px] font-bold text-muted-foreground uppercase tracking-widest">
                  Condomínio
                </p>
                <p className="text-sm font-semibold text-foreground mt-0.5">
                  {contrato.responsabilidadeCondominio || "Inquilino"}
                </p>
                {contrato.valorEstimadoCondominio ? (
                  <p className="text-xs text-muted-foreground">
                    Estimado: {formatCurrency(contrato.valorEstimadoCondominio)}
                  </p>
                ) : null}
              </div>

              <div className="col-span-2 pt-2 border-t border-border/40">
                <p className="text-[10px] font-bold text-muted-foreground uppercase tracking-widest mb-2">
                  Contas de Consumo
                </p>
                <div className="grid grid-cols-4 gap-2">
                  <div className="rounded-lg border bg-muted/20 p-2 text-center">
                    <Drop size={16} className="text-blue-500 mx-auto mb-1" />
                    <p className="text-[10px] font-medium text-muted-foreground">Água</p>
                    <p className="text-xs font-semibold text-foreground truncate" title={contrato.consumoAgua || "Inquilino"}>
                      {contrato.consumoAgua || "Inquilino"}
                    </p>
                  </div>

                  <div className="rounded-lg border bg-muted/20 p-2 text-center">
                    <Lightning size={16} className="text-amber-500 mx-auto mb-1" />
                    <p className="text-[10px] font-medium text-muted-foreground">Energia</p>
                    <p className="text-xs font-semibold text-foreground truncate" title={contrato.consumoLuz || "Inquilino"}>
                      {contrato.consumoLuz || "Inquilino"}
                    </p>
                  </div>

                  <div className="rounded-lg border bg-muted/20 p-2 text-center">
                    <Flame size={16} className="text-orange-500 mx-auto mb-1" />
                    <p className="text-[10px] font-medium text-muted-foreground">Gás</p>
                    <p className="text-xs font-semibold text-foreground truncate" title={contrato.consumoGas || "Inquilino"}>
                      {contrato.consumoGas || "Inquilino"}
                    </p>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div className="rounded-xl border bg-card p-6 shadow-sm">
        <h4 className="text-sm font-semibold text-foreground flex items-center gap-2 pb-4 border-b border-border/60">
          <User size={18} className="text-primary" /> Partes Envolvidas
        </h4>

        <div className="grid gap-4 sm:grid-cols-2 pt-4">
          <div className="flex items-center gap-3 p-3 rounded-lg border bg-muted/10">
            <div className="flex size-10 shrink-0 items-center justify-center rounded-full bg-primary/10 text-primary">
              <User size={18} />
            </div>
            <div>
              <p className="text-[10px] font-bold text-muted-foreground uppercase tracking-widest">
                Locatário (Inquilino)
              </p>
              <p className="text-sm font-semibold text-foreground">
                {contrato.inquilinoNome}
              </p>
            </div>
          </div>

          <div className="flex items-center gap-3 p-3 rounded-lg border bg-muted/10">
            <div className="flex size-10 shrink-0 items-center justify-center rounded-full bg-secondary text-secondary-foreground">
              <UserSound size={18} />
            </div>
            <div>
              <p className="text-[10px] font-bold text-muted-foreground uppercase tracking-widest">
                Intermediação
              </p>
              <p className="text-sm font-semibold text-foreground">
                {contrato.responsavel}
              </p>
            </div>
          </div>

          {contrato.fiador && (
            <div className="flex items-center justify-between gap-3 p-3 rounded-lg border bg-muted/10 sm:col-span-2">
              <div className="flex items-center gap-3">
                <div className="flex size-10 shrink-0 items-center justify-center rounded-full bg-amber-500/10 text-amber-600 dark:text-amber-400">
                  <UserSound size={18} />
                </div>
                <div>
                  <div className="flex items-center gap-2">
                    <p className="text-[10px] font-bold text-muted-foreground uppercase tracking-widest">
                      Fiador Principal
                    </p>
                    <Badge variant="outline" className="text-[10px] h-4 px-1 py-0">Garantia</Badge>
                  </div>
                  <p className="text-sm font-semibold text-foreground">
                    {contrato.fiador.nome}
                  </p>
                </div>
              </div>
              <div className="text-right">
                <p className="text-xs font-mono text-muted-foreground">
                  CPF: {contrato.fiador.cpf}
                </p>
                <p className="text-xs text-muted-foreground">
                  {contrato.fiador.telefone}
                </p>
              </div>
            </div>
          )}

          {contrato.segundoFiador && (
            <div className="flex items-center justify-between gap-3 p-3 rounded-lg border bg-muted/10 sm:col-span-2">
              <div className="flex items-center gap-3">
                <div className="flex size-10 shrink-0 items-center justify-center rounded-full bg-amber-500/10 text-amber-600 dark:text-amber-400">
                  <UserSound size={18} />
                </div>
                <div>
                  <div className="flex items-center gap-2">
                    <p className="text-[10px] font-bold text-muted-foreground uppercase tracking-widest">
                      Segundo Fiador
                    </p>
                    <Badge variant="outline" className="text-[10px] h-4 px-1 py-0">Garantia Adicional</Badge>
                  </div>
                  <p className="text-sm font-semibold text-foreground">
                    {contrato.segundoFiador.nome}
                  </p>
                </div>
              </div>
              <div className="text-right">
                <p className="text-xs font-mono text-muted-foreground">
                  CPF: {contrato.segundoFiador.cpf}
                </p>
                <p className="text-xs text-muted-foreground">
                  {contrato.segundoFiador.telefone}
                </p>
              </div>
            </div>
          )}
        </div>
      </div>

      {contrato.observacoes && contrato.observacoes.trim() && (
        <div className="rounded-xl border border-amber-500/30 bg-amber-500/5 p-5 shadow-sm">
          <div className="flex items-start gap-3">
            <div className="p-2 rounded-lg bg-amber-500/10 text-amber-600 dark:text-amber-400 shrink-0">
              <ChatTeardropText size={18} weight="fill" />
            </div>
            <div>
              <p className="text-xs font-bold uppercase tracking-wider text-amber-700 dark:text-amber-300 mb-1">
                Observações do Contrato
              </p>
              <p className="text-sm text-foreground leading-relaxed whitespace-pre-line">
                {contrato.observacoes}
              </p>
            </div>
          </div>
        </div>
      )}
    </div>
  );
}
