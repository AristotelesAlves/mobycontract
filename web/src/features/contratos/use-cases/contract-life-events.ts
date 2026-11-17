import type {
  Contrato,
  ContratoAlteracao,
  ContratoHistorico,
  ParcelaFinanceira,
} from "../data/contrato-types";
import { formatCurrency } from "@/lib/formatters";

export function historicoEncargos(parcelas: ParcelaFinanceira[]): ContratoHistorico[] {
  return parcelas.flatMap((parcela) => (parcela.encargos ?? []).map((encargo) => ({
    id: `encargo-${encargo.id}`,
    data: encargo.criadoEm,
    evento: `Encargo: ${{ multa: "Multa", juros: "Juros", taxa: "Taxa", desconto: "Desconto" }[encargo.tipo] ?? encargo.tipo}`,
    detalhe: `Parcela ${parcela.referencia ?? parcela.numero} • ${formatCurrency(encargo.valor)} • ${encargo.motivo}`,
  }))).sort((a, b) => a.data.localeCompare(b.data));
}

export function derivarHistoricoContrato(
  contrato: Contrato,
): ContratoHistorico[] {
  if (contrato.historico?.length) {
    return [...contrato.historico, ...historicoEncargos(contrato.parcelas ?? [])].sort((a, b) => a.data.localeCompare(b.data));
  }
  const eventos: ContratoHistorico[] = [];

  if (contrato.criadoEm) {
    eventos.push({
      id: `${contrato.id}-criacao`,
      data: contrato.criadoEm,
      evento: "Contrato criado",
      detalhe: `Contrato nº ${contrato.numero} gerado a partir do modelo ${
        contrato.modeloTitulo ?? "selecionado"
      }.`.trim(),
    });
  }

  (contrato.assinaturas ?? [])
    .filter((assinatura) => assinatura.assinado && assinatura.dataAssinatura)
    .forEach((assinatura) => {
      eventos.push({
        id: `${contrato.id}-ass-${assinatura.id}`,
        data: assinatura.dataAssinatura as string,
        evento: `Assinatura: ${assinatura.nome}`,
        detalhe: `${assinatura.nome} (${assinatura.papel}) assinou o contrato.`,
      });
    });

  if (contrato.documentoFisicoConfirmado || contrato.anexoContratoNome) {
    eventos.push({
      id: `${contrato.id}-doc`,
      data: contrato.atualizadoEm || contrato.criadoEm || "",
      evento: "Documento físico",
      detalhe: `Contrato físico confirmado${
        contrato.anexoContratoNome ? ` (${contrato.anexoContratoNome})` : ""
      }.`,
    });
  }

  if (contrato.dataRegistroBlockchain) {
    eventos.push({
      id: `${contrato.id}-blockchain`,
      data: contrato.dataRegistroBlockchain,
      evento: "Registro na blockchain",
      detalhe: `Smart contract registrado${
        contrato.hashBlockchain ? ` — hash ${contrato.hashBlockchain}` : ""
      }.`,
    });
  }

  eventos.push(...historicoEncargos(contrato.parcelas ?? []));
  return eventos.sort((a, b) => a.data.localeCompare(b.data));
}

export function derivarAlteracoesContrato(
  contrato: Contrato,
  parcelas: ParcelaFinanceira[] = [],
): ContratoAlteracao[] {
  const eventos: ContratoAlteracao[] = [];

  function adicionar(evento: {
    id: string;
    data: string;
    acao: ContratoAlteracao["acao"];
    campo: string;
    para: string;
    valorAnterior?: string;
  }) {
    eventos.push({
      id: evento.id,
      contratoId: contrato.id,
      data: evento.data,
      usuario: "Sistema",
      acao: evento.acao,
      campo: evento.campo,
      valorAnterior: evento.valorAnterior ?? "—",
      valorNovo: evento.para,
      alteradoPor: "Sistema",
      alteradoEm: evento.data,
      de: evento.valorAnterior ?? "—",
      para: evento.para,
    });
  }

  if (contrato.criadoEm) {
    adicionar({
      id: `${contrato.id}-criacao`,
      data: contrato.criadoEm,
      acao: "Criação",
      campo: "Contrato",
      para: `Contrato nº ${contrato.numero} criado`,
    });
  }

  (contrato.assinaturas ?? [])
    .filter((assinatura) => assinatura.assinado && assinatura.dataAssinatura)
    .forEach((assinatura) => {
      adicionar({
        id: `${contrato.id}-ass-${assinatura.id}`,
        data: assinatura.dataAssinatura as string,
        acao: "Assinatura",
        campo: assinatura.papel,
        para: "Assinado",
        valorAnterior: "Pendente",
      });
    });

  if (contrato.documentoFisicoConfirmado || contrato.anexoContratoNome) {
    adicionar({
      id: `${contrato.id}-doc`,
      data: contrato.atualizadoEm || contrato.criadoEm || "",
      acao: "Blockchain",
      campo: "Documento físico",
      para: contrato.anexoContratoNome ?? "Confirmado",
      valorAnterior: "Pendente",
    });
  }

  parcelas
    .filter((parcela) => parcela.dataPagamento || parcela.pagoEm)
    .forEach((parcela) => {
      adicionar({
        id: `${contrato.id}-pag-${parcela.id}`,
        data: (parcela.dataPagamento ?? parcela.pagoEm) as string,
        acao: "Pagamento",
        campo: `Parcela ${parcela.numero}`,
        para: "Pago",
        valorAnterior: "Pendente",
      });
    });

  if (contrato.dataRegistroBlockchain) {
    adicionar({
      id: `${contrato.id}-blockchain`,
      data: contrato.dataRegistroBlockchain,
      acao: "Blockchain",
      campo: "Smart contract",
      para: contrato.hashBlockchain ?? "Registrado",
      valorAnterior: "Aguardando registro",
    });
  }

  return eventos;
}
