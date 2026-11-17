"use client";

import { useEffect, useRef, useState, type ReactNode } from "react";
import Link from "next/link";
import { CheckCircle, FileArrowUp, Hash, MagnifyingGlass, ShieldCheck, SpinnerGap, WarningCircle, XCircle } from "@phosphor-icons/react";
import { Button } from "@/components/ui/button";
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from "@/components/ui/card";
import { Input } from "@/components/ui/input";
import { SearchableSelect } from "@/components/ui/searchable-select";
import { getReadableErrorMessage } from "@/services/api/get-readable-error-message";
import { HttpError } from "@/core/errors/http-error";
import { listContracts } from "../services/contract-service";
import { verifyBlockchainTransaction, verifyContractDocument, type ContractVerificationResult, type TransactionVerificationResult } from "../services/contract-verification-service";

const MAX_FILE_SIZE = 25 * 1024 * 1024;
const ALLOWED_TYPES = ["application/pdf", "image/jpeg", "image/png", "image/webp"];
const TRANSACTION_HASH_PATTERN = /^0x[a-fA-F0-9]{64}$/;
type Mode = "arquivo" | "transacao";

export function VerifyContractView() {
  const [mode, setMode] = useState<Mode>("arquivo");
  const [options, setOptions] = useState<Array<{ value: string; label: string }>>([]);
  const [contractId, setContractId] = useState("");
  const [file, setFile] = useState<File | null>(null);
  const [transactionHash, setTransactionHash] = useState("");
  const [loading, setLoading] = useState(true);
  const [loadError, setLoadError] = useState("");
  const [error, setError] = useState("");
  const [verifying, setVerifying] = useState(false);
  const [documentResult, setDocumentResult] = useState<ContractVerificationResult | null>(null);
  const [transactionResult, setTransactionResult] = useState<TransactionVerificationResult | null>(null);
  const fileInput = useRef<HTMLInputElement>(null);

  useEffect(() => {
    let cancelled = false;
    async function load() {
      setLoading(true);
      try {
        const items: Array<{ value: string; label: string }> = [];
        let page = 1;
        let totalPages = 1;
        do {
          const response = await listContracts({ pagina: page, tamanhoPagina: 100 });
          if (cancelled) return;
          items.push(...response.itens.map((contract) => ({ value: contract.id, label: `${contract.numero} • ${contract.imovelTitulo || "Imóvel"} • ${contract.inquilinoNome || "Locatário"}` })));
          totalPages = response.totalPaginas;
          page += 1;
        } while (page <= totalPages);
        setOptions(items);
      } catch (failure) {
        if (!cancelled) setLoadError(getReadableErrorMessage(failure));
      } finally {
        if (!cancelled) setLoading(false);
      }
    }
    void load();
    return () => { cancelled = true; };
  }, []);

  function resetResults() {
    setError("");
    setDocumentResult(null);
    setTransactionResult(null);
  }

  function selectFile(incoming?: File) {
    if (verifying) return;
    resetResults();
    setFile(null);
    if (!incoming) return;
    const arquivoInvalido =
      !ALLOWED_TYPES.includes(incoming.type) ||
      incoming.size === 0 ||
      incoming.size > MAX_FILE_SIZE;

    if (arquivoInvalido) {
      setError("Selecione um PDF ou imagem JPG, PNG ou WebP de até 25 MB, com conteúdo.");
    }
    if (arquivoInvalido && fileInput.current) {
      fileInput.current.value = "";
    }
    if (arquivoInvalido) {
      return;
    }
    setFile(incoming);
  }

  async function verifyDocument() {
    resetResults();
    if (!contractId || !file) return setError("Selecione o contrato e anexe o arquivo para verificar.");
    setVerifying(true);
    try {
      setDocumentResult(await verifyContractDocument(contractId, file));
    } catch (failure) {
      const fields = failure instanceof HttpError ? (failure.body as { campos?: { arquivo?: string[] } } | null)?.campos?.arquivo : undefined;
      setError(fields?.join(" ") || getReadableErrorMessage(failure));
    } finally {
      setVerifying(false);
    }
  }

  async function verifyTransaction() {
    resetResults();
    const hash = transactionHash.trim();
    if (!TRANSACTION_HASH_PATTERN.test(hash)) return setError("Informe um hash de transação válido, iniciado em 0x e com 64 caracteres hexadecimais.");
    setVerifying(true);
    try {
      setTransactionResult(await verifyBlockchainTransaction(hash));
    } catch (failure) {
      setError(getReadableErrorMessage(failure));
    } finally {
      setVerifying(false);
    }
  }

  const matchingDocument = documentResult?.resultado === "correspondente";
  const differingDocument = documentResult?.resultado === "diferente";
  const transactionConfirmed = transactionResult?.resultado === "confirmada";

  return <div className="mx-auto flex w-full max-w-4xl flex-col gap-5 pb-8">
    <Card className="overflow-hidden">
      <CardHeader className="border-b bg-gradient-to-br from-primary/10 via-background to-background">
        <div className="mb-2 flex size-11 items-center justify-center rounded-xl bg-primary text-primary-foreground shadow-sm"><ShieldCheck size={25} /></div>
        <CardTitle className="text-xl">Verificar contrato</CardTitle>
        <CardDescription>Valide o arquivo assinado ou audite a transação registrada na blockchain.</CardDescription>
      </CardHeader>
      <CardContent className="space-y-5 pt-6">
        <div className="grid gap-2 sm:grid-cols-2" role="tablist" aria-label="Modo de consulta">
          <ModeButton active={mode === "arquivo"} icon={<MagnifyingGlass size={18} />} title="Validar arquivo" description="Compara o SHA-256 do arquivo com a âncora." onClick={() => { setMode("arquivo"); resetResults(); }} />
          <ModeButton active={mode === "transacao"} icon={<Hash size={18} />} title="Consultar transação" description="Mostra o hash de documento gravado na rede." onClick={() => { setMode("transacao"); resetResults(); }} />
        </div>

        {mode === "arquivo" ? <div className="space-y-5">
          <div className="space-y-2"><p className="text-sm font-medium">1. Selecione o contrato</p><SearchableSelect label="Contrato para verificar" placeholder={loading ? "Carregando contratos..." : "Busque por número, imóvel ou locatário"} options={options} value={contractId} onValueChange={(value) => { setContractId(value); resetResults(); }} disabled={loading || verifying} />{loadError && <p role="alert" className="text-sm text-destructive">{loadError}</p>}</div>
          <div className="space-y-2"><p className="text-sm font-medium">2. Anexe o documento assinado</p><input ref={fileInput} id="arquivo-verificacao" type="file" accept=".pdf,.jpg,.jpeg,.png,.webp" className="sr-only" disabled={verifying} onChange={(event) => selectFile(event.target.files?.[0])} /><button type="button" disabled={verifying} onClick={() => fileInput.current?.click()} onDragOver={(event) => event.preventDefault()} onDrop={(event) => { event.preventDefault(); selectFile(event.dataTransfer.files[0]); }} className="flex min-h-40 w-full flex-col items-center justify-center gap-3 rounded-xl border-2 border-dashed bg-muted/20 p-6 text-center transition-colors hover:border-primary/50 hover:bg-primary/5 disabled:opacity-60"><FileArrowUp size={30} className="text-primary" /><span className="max-w-full break-all text-sm font-medium">{file ? file.name : "Clique ou arraste o arquivo até aqui"}</span><span className="text-xs text-muted-foreground">{file ? `${(file.size / 1024 / 1024).toFixed(2)} MB • Clique para trocar` : "PDF, JPG, PNG ou WebP • Até 25 MB"}</span></button><p className="text-xs text-muted-foreground">Use o arquivo original assinado. Uma nova exportação ou digitalização altera o hash.</p></div>
          <Button onClick={() => void verifyDocument()} disabled={verifying || loading || !contractId || !file} className="w-full sm:w-auto">{verifying ? <SpinnerGap size={18} className="animate-spin" /> : <ShieldCheck size={18} />}{verifying ? "Consultando blockchain..." : "Validar arquivo"}</Button>
        </div> : <div className="space-y-5"><div className="rounded-xl border bg-muted/30 p-4 text-sm text-muted-foreground">Esta consulta comprova a existência da transação e informa o hash do documento ancorado. Para comparar um PDF, use a opção “Validar arquivo”.</div><div className="space-y-2"><label htmlFor="hash-transacao" className="text-sm font-medium">Hash da transação</label><Input id="hash-transacao" value={transactionHash} onChange={(event) => { setTransactionHash(event.target.value); resetResults(); }} placeholder="0x..." spellCheck={false} autoComplete="off" className="font-mono" disabled={verifying} /></div><Button onClick={() => void verifyTransaction()} disabled={verifying || !transactionHash.trim()} className="w-full sm:w-auto">{verifying ? <SpinnerGap size={18} className="animate-spin" /> : <Hash size={18} />}{verifying ? "Consultando blockchain..." : "Consultar transação"}</Button></div>}
        {error && <p role="alert" className="rounded-lg border border-destructive/30 bg-destructive/5 p-3 text-sm text-destructive">{error}</p>}
      </CardContent>
    </Card>

    {documentResult && <ResultCard success={Boolean(matchingDocument)} warning={!matchingDocument && !differingDocument} title={matchingDocument ? "Arquivo confirmado na blockchain" : differingDocument ? "Arquivo não corresponde" : "Registro indisponível"} message={documentResult.mensagem} details={[{ label: "SHA-256 do arquivo enviado", value: documentResult.hashArquivo }, { label: "Hash ancorado", value: documentResult.hashRegistrado }, { label: "Transação de registro", value: documentResult.hashTransacao }]} footer={<p>Contrato <Link className="font-medium underline" href={`/contratos/${documentResult.contratoId}`}>{documentResult.numero}</Link> • Consultado em {new Date(documentResult.verificadoEm).toLocaleString("pt-BR")}</p>} />}
    {transactionResult && <ResultCard success={transactionConfirmed} warning={!transactionConfirmed} title={transactionConfirmed ? "Transação confirmada" : "Transação não encontrada"} message={transactionResult.mensagem} details={[{ label: "Hash da transação", value: transactionResult.hashTransacao }, { label: "Hash do documento ancorado", value: transactionResult.hashDocumento }, { label: "Chave do contrato na blockchain", value: transactionResult.chaveBlockchain }]} footer={transactionConfirmed ? <div className="space-y-2"><p>{transactionResult.contratoId && transactionResult.numero ? <>Contrato associado: <Link className="font-medium underline" href={`/contratos/${transactionResult.contratoId}`}>{transactionResult.numero}</Link></> : "A transação não está vinculada a um contrato local."}</p><p>Bloco <strong>{transactionResult.numeroBloco}</strong> • {transactionResult.confirmacoes} confirmações</p></div> : undefined} />}
  </div>;
}

function ModeButton({ active, icon, title, description, onClick }: { active: boolean; icon: ReactNode; title: string; description: string; onClick: () => void }) {
  return <button type="button" role="tab" aria-selected={active} onClick={onClick} className={`rounded-xl border p-4 text-left transition-colors ${active ? "border-primary bg-primary/5 ring-1 ring-primary/20" : "hover:bg-muted/50"}`}><span className="mb-2 flex items-center gap-2 font-medium text-primary">{icon}{title}</span><span className="block text-xs text-muted-foreground">{description}</span></button>;
}

function ResultCard({ success, warning, title, message, details, footer }: { success: boolean; warning: boolean; title: string; message: string; details: Array<{ label: string; value?: string }>; footer?: ReactNode }) {
  const Icon = success ? CheckCircle : warning ? WarningCircle : XCircle;
  const color = success ? "text-emerald-500" : warning ? "text-amber-500" : "text-destructive";
  const values = details.filter((detail) => detail.value);
  return <Card className={success ? "border-emerald-500/40" : warning ? "border-amber-500/40" : "border-destructive/40"}><CardHeader><div role="status" className="flex items-start gap-3"><Icon size={28} className={`shrink-0 ${color}`} /><div className="space-y-1"><CardTitle className="text-lg">{title}</CardTitle><CardDescription>{message}</CardDescription></div></div></CardHeader><CardContent className="space-y-4"><div className="text-sm">{footer}</div>{values.length > 0 && <details className="rounded-lg border p-3 text-sm"><summary className="cursor-pointer font-medium">Detalhes da consulta</summary><dl className="mt-3 space-y-3">{values.map((detail) => <div key={detail.label}><dt className="text-muted-foreground">{detail.label}</dt><dd className="break-all font-mono text-xs">{detail.value}</dd></div>)}</dl></details>}</CardContent></Card>;
}
