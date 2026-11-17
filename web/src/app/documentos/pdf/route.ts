import { NextRequest, NextResponse } from "next/server";
import puppeteer from "puppeteer";

export const runtime = "nodejs";
let rendering = false;

export async function POST(request: NextRequest) {
  if (Number(request.headers.get("content-length")) > 2_000_000) {
    return NextResponse.json({ mensagem: "Documento muito grande para exportação." }, { status: 413 });
  }
  let browser;
  let acquired = false;
  try {
    const { html, contractId, autorizacao } = await request.json();
    if (typeof html !== "string" || !html || html.length > 2_000_000 || !/^\d+$/.test(String(contractId))) {
      return NextResponse.json({ mensagem: "Documento inválido." }, { status: 400 });
    }
    const apiBase = process.env.BASE_URI_API ?? process.env.NEXT_PUBLIC_BASE_URI_API ?? "http://localhost:8000/api/v1";
    const base = new URL(apiBase);
    const expectedPath = `${base.pathname.replace(/\/+$/, "")}/contratos/${contractId}/exportacao-pdf`;
    if (typeof autorizacao !== "string" || !autorizacao.startsWith(`${expectedPath}?`)) {
      return NextResponse.json({ mensagem: "Autorize novamente o download do documento." }, { status: 401 });
    }
    const access = await fetch(`${base.origin}${autorizacao}`, {
      headers: { Accept: "application/json" },
      redirect: "error",
      cache: "no-store",
      signal: AbortSignal.timeout(10_000),
    });
    if (!access.ok) {
      return NextResponse.json({ mensagem: "Não foi possível acessar o contrato. Verifique sua sessão e tente novamente." }, { status: access.status });
    }
    if (rendering) {
      return NextResponse.json({ mensagem: "Há um PDF sendo gerado. Tente novamente em instantes." }, { status: 429 });
    }
    rendering = true;
    acquired = true;
    browser = await puppeteer.launch({
      headless: true,
      executablePath: process.env.PUPPETEER_EXECUTABLE_PATH || undefined,
      args: process.env.PUPPETEER_EXECUTABLE_PATH ? ["--no-sandbox", "--disable-setuid-sandbox", "--disable-dev-shm-usage"] : [],
      timeout: 20_000,
    });
    const page = await browser.newPage();
    await page.setJavaScriptEnabled(false);
    await page.setRequestInterception(true);
    page.on("request", (resource) => {
      if (resource.url().startsWith("data:")) void resource.continue();
      else void resource.abort();
    });
    await page.setContent(html, { waitUntil: "load", timeout: 15_000 });
    await page.addStyleTag({ content: "@media print { .documento-pilha { height: auto !important; min-height: 0 !important; } .documento-conteudo { overflow: visible !important; } }" });
    const pdf = await page.pdf({ format: "A4", printBackground: true, preferCSSPageSize: true, timeout: 20_000 });
    return new Response(new Uint8Array(pdf), {
      headers: { "Content-Type": "application/pdf", "Content-Disposition": 'attachment; filename="documento.pdf"', "Cache-Control": "private, no-store" },
    });
  } catch (error) {
    console.error("Falha na exportação do PDF:", error instanceof Error ? error.message : "Erro desconhecido");
    return NextResponse.json({ mensagem: "Não foi possível gerar o PDF. Tente novamente." }, { status: 500 });
  } finally {
    try { await browser?.close(); } finally { if (acquired) rendering = false; }
  }
}
