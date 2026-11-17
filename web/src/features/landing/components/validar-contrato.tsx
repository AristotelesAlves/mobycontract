"use client";

import { useState, type FormEvent } from "react";
import { cpfValido } from "@/lib/documentos";
import { api } from "@/services/api/base-api";
import { formatTaxId } from "@/lib/formatters";
import { getReadableErrorMessage } from "@/services/api/get-readable-error-message";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";

export function ValidarContrato() {
  const [cpf, setCpf] = useState("");
  const [numero, setNumero] = useState("");
  const [mensagem, setMensagem] = useState("");
  const [consultando, setConsultando] = useState(false);
  async function validar(event: FormEvent) {
    event.preventDefault();
    if (!cpfValido(cpf)) { setMensagem("Informe um CPF válido."); return; }
    setConsultando(true);
    setMensagem("");
    try {
      const resposta = await api.post<{ dados: { mensagem: string; situacao: string | null } }>(
        "/contratos/validar", { cpf, numero: numero.trim() }, { skipAuth: true },
      );
      setMensagem(resposta.dados.mensagem + (resposta.dados.situacao ? ` Situação: ${resposta.dados.situacao}.` : ""));
    } catch (erro) {
      setMensagem(getReadableErrorMessage(erro));
    } finally {
      setConsultando(false);
    }
  }
  return (
    <section id="validar-contrato" className="mx-auto w-full max-w-3xl px-6 py-16">
      <h2 className="text-2xl font-semibold">Validar contrato</h2>
      <p className="mt-2 text-muted-foreground">Consulte o reconhecimento do contrato pelo sistema usando o CPF do locatário ou fiador e o número do contrato. A consulta não valida a integridade de um arquivo.</p>
      <form onSubmit={validar} className="mt-6 space-y-4">
        <div><label htmlFor="validacao-cpf">CPF do locatário ou fiador</label><Input id="validacao-cpf" value={cpf} onChange={e => setCpf(formatTaxId(e.target.value))} inputMode="numeric" maxLength={14} required autoComplete="off" /></div>
        <div><label htmlFor="validacao-numero">Número do contrato</label><Input id="validacao-numero" value={numero} onChange={e => setNumero(e.target.value)} maxLength={30} placeholder="2026/0001" required autoComplete="off" /></div>
        <Button type="submit" disabled={consultando}>{consultando ? "Consultando..." : "Validar contrato"}</Button>
        <p role="status" aria-live="polite">{mensagem}</p>
      </form>
    </section>
  );
}
