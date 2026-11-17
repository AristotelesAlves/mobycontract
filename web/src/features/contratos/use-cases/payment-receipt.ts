import { systemDocumentHtml } from "@/lib/system-document-html";
import { escapeDocumentTextForHtml as escape } from "@/lib/tiptap/document-variables";
import { formatCurrency, formatDate } from "@/lib/formatters";
import type { Contrato, ParcelaFinanceira } from "../data/contrato-types";

export function depositReceiptHtml(contract: Contrato, issuedAt = new Date()): string {
  if (contract.garantia !== "Caução") throw new Error("Este contrato não possui garantia por caução.");
  if (contract.valorCaucao == null || !Number.isFinite(contract.valorCaucao) || contract.valorCaucao <= 0) {
    throw new Error("Informe um valor de caução válido no contrato para gerar o recibo.");
  }
  const tenant = contract.snapshot?.inquilino;
  const landlord = contract.snapshot?.locador?.nome ?? contract.assinaturas?.find((party) => party.papel === "Locador")?.nome;
  return systemDocumentHtml("Recibo de caução", `Contrato nº ${contract.numero}`, `
    <h2>Identificação da garantia</h2>
    <p><strong>Locatário:</strong> ${escape(tenant?.nome ?? contract.inquilinoNome ?? "Não informado")}</p>
    ${tenant?.cpf ? `<p><strong>CPF:</strong> ${escape(tenant.cpf)}</p>` : ""}
    <p><strong>Locador:</strong> ${escape(landlord ?? "Não informado")}</p>
    <p><strong>Imóvel:</strong> ${escape(contract.imovelTitulo ?? "Não informado")}</p>
    <p><strong>Endereço:</strong> ${escape(contract.imovelEndereco ?? "Não informado")}</p>
    <table class="registro-tabela"><tbody>
      <tr><th>Modalidade</th><td>Caução</td></tr>
      <tr><th>Valor da caução cadastrado</th><td><strong>${formatCurrency(contract.valorCaucao)}</strong></td></tr>
      <tr><th>Início da locação</th><td>${escape(formatDate(contract.dataInicio))}</td></tr>
    </tbody></table>
    <p>Consta no contrato nº ${escape(contract.numero)} uma caução no valor de <strong>${formatCurrency(contract.valorCaucao)}</strong>, vinculada à garantia da locação do imóvel acima.</p>
    <p class="registro-meta">Registro da garantia: contrato #${escape(contract.id)}. A data de emissão identifica a geração deste documento.</p>`, issuedAt);
}

export function paymentReceiptHtml(contract: Contrato, installment: ParcelaFinanceira, issuedAt = new Date()): string {
  if (installment.contratoId !== contract.id || installment.status !== "pago") {
    throw new Error("O recibo está disponível apenas para parcelas pagas deste contrato.");
  }
  if (installment.valorPago == null || !Number.isFinite(installment.valorPago)) {
    throw new Error("O pagamento não possui um valor registrado para emitir o recibo.");
  }
  const tenant = contract.snapshot?.inquilino;
  const landlord = contract.snapshot?.locador?.nome ?? contract.assinaturas?.find((party) => party.papel === "Locador")?.nome;
  const reference = installment.referencia ?? String(installment.numero);
  const date = installment.dataPagamento || installment.pagoEm?.slice(0, 10) || "";
  const content = `<h2>Identificação do pagamento</h2>
    <p><strong>Locatário:</strong> ${escape(tenant?.nome ?? contract.inquilinoNome ?? "Não informado")}</p>
    ${tenant?.cpf ? `<p><strong>CPF:</strong> ${escape(tenant.cpf)}</p>` : ""}
    <p><strong>Locador:</strong> ${escape(landlord ?? "Não informado")}</p>
    <p><strong>Imóvel:</strong> ${escape(contract.imovelTitulo ?? "Não informado")}</p>
    <p><strong>Endereço:</strong> ${escape(contract.imovelEndereco ?? "Não informado")}</p>
    <table class="registro-tabela"><tbody>
      <tr><th>Referência da parcela</th><td>${escape(reference)}</td></tr>
      <tr><th>Vencimento</th><td>${escape(formatDate(installment.dataVencimento))}</td></tr>
      <tr><th>Data do pagamento</th><td>${escape(formatDate(date))}</td></tr>
      <tr><th>Valor base</th><td>${formatCurrency(installment.valor)}</td></tr>
      <tr><th>Juros e multas</th><td>${formatCurrency(installment.juros ?? 0)}</td></tr>
      <tr><th>Taxas adicionais</th><td>${formatCurrency(installment.taxasAdicionais ?? 0)}</td></tr>
      <tr><th>Valor pago</th><td><strong>${formatCurrency(installment.valorPago)}</strong></td></tr>
    </tbody></table>
    <p>Consta no sistema o pagamento de <strong>${formatCurrency(installment.valorPago)}</strong>, referente à parcela acima do contrato nº ${escape(contract.numero)}.</p>
    <p class="registro-meta">Registro do pagamento: contrato #${escape(contract.id)} / parcela #${escape(installment.id)}.</p>`;
  return systemDocumentHtml("Recibo de pagamento", `Contrato nº ${contract.numero} • Parcela ${reference}`, content, issuedAt);
}
