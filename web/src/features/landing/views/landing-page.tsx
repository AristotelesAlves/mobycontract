import { ValidarContrato } from "../components/validar-contrato"
import Link from "next/link"
import Image from "next/image"
import {
  ArrowRight,
  Buildings,
  CheckCircle,
  ClockCounterClockwise,
  Cube,
  Fingerprint,
  Hash,
  Lightning,
  LinkSimple,
  SealCheck,
  ShieldCheck,
  Signature,
} from "@phosphor-icons/react/dist/ssr"

import { buttonVariants } from "@/components/ui/button"
import { cn } from "@/lib/utils"

const recursos = [
  {
    icon: Hash,
    title: "Hash imutável",
    text: "A camada blockchain permite registrar evidências por hash. A verificação depende do documento e do registro correspondente.",
  },
  {
    icon: ClockCounterClockwise,
    title: "Trilha de auditoria",
    text: "Quem criou, quem assinou, quem alterou e quando. Histórico das operações registradas no sistema.",
  },
  {
    icon: Signature,
    title: "Etapas de assinatura",
    text: "Acompanhe a confirmação documental e as etapas registradas pela equipe responsável.",
  },
  {
    icon: Fingerprint,
    title: "Verificação pública",
    text: "Consulte o reconhecimento de um contrato com CPF e número, sem receber seus dados pessoais.",
  },
  {
    icon: Buildings,
    title: "Modelos reutilizáveis",
    text: "Locação, compromisso de compra e venda, comodato: monte o modelo uma vez e use com variáveis.",
  },
  {
    icon: Lightning,
    title: "Visão financeira",
    text: "Consulte parcelas, vencimentos e pagamentos registrados nos contratos.",
  },
]

const passos = [
  {
    n: "01",
    title: "Monte o contrato",
    text: "Use um modelo com variáveis (imóvel, partes, valores) ou escreva do zero no editor.",
  },
  {
    n: "02",
    title: "Preserve o histórico",
    text: "Dados do imóvel, das partes e do modelo são preservados ao criar um contrato.",
  },
  {
    n: "03",
    title: "Consulte o contrato",
    text: "Use CPF e número para consultar reconhecimento e situação, com acesso limitado.",
  },
]

const numeros = [
  { valor: "CPF", label: "consulta pública limitada" },
  { valor: "Fotos", label: "arquivos privados" },
  { valor: "Dados", label: "histórico preservado" },
  { valor: "Gestão", label: "parcelas e pagamentos" },
]

const planos = [
  {
    nome: "Essencial",
    preco: "R$ 149",
    periodo: "/mês",
    resumo: "Para quem administra a própria carteira de imóveis.",
    itens: [
      "Até 25 contratos registrados por mês",
      "Modelos com variáveis e editor",
      "Dashboard e alertas de vencimento",
      "Verificação pública por hash",
    ],
    cta: "Começar agora",
    destaque: false,
  },
  {
    nome: "Profissional",
    preco: "R$ 389",
    periodo: "/mês",
    resumo: "Para imobiliárias que assinam contrato toda semana.",
    itens: [
      "Até 150 contratos registrados por mês",
      "Etapas de assinatura das partes",
      "Trilha de auditoria completa",
      "Até 10 usuários da equipe",
      "Exportação de comprovantes on-chain",
    ],
    cta: "Testar 14 dias",
    destaque: true,
  },
  {
    nome: "Corporativo",
    preco: "Sob consulta",
    periodo: "",
    resumo: "Para redes, incorporadoras e operações com SLA.",
    itens: [
      "Contratos ilimitados",
      "Usuários e permissões sem limite",
      "API e integração com o seu ERP",
      "Rede blockchain dedicada ou privada",
      "Suporte com SLA e onboarding assistido",
    ],
    cta: "Falar com o time",
    destaque: false,
  },
]

interface LandingPageProps {
  currentYear: number;
}

export function LandingPage({ currentYear }: LandingPageProps) {
  return (
    <div className="min-h-screen bg-background text-foreground">
      <header className="sticky top-0 z-50 border-b border-border/60 bg-background/80 backdrop-blur-md">
        <nav className="mx-auto flex h-16 max-w-6xl items-center justify-between px-6">
          <Link href="/" className="flex items-center gap-2 font-semibold tracking-tight">
            <span className="flex size-8 items-center justify-center rounded-lg bg-zinc-900 ring-1 ring-zinc-700">
              <Image
                src="/logo/mobiContract.svg"
                alt="Logo MobiContract"
                width={28}
                height={28}
                className="size-6"
              />
            </span>
            MobiContract
          </Link>
          <div className="hidden items-center gap-8 text-xs text-muted-foreground md:flex">
            <a href="#como-funciona" className="transition-colors hover:text-foreground">
              Como funciona
            </a>
            <a href="#recursos" className="transition-colors hover:text-foreground">
              Recursos
            </a>
            <a href="#seguranca" className="transition-colors hover:text-foreground">
              Segurança
            </a>
            <a href="#planos" className="transition-colors hover:text-foreground">
              Planos
            </a>
          </div>
          <Link href="/dashboard" className={cn(buttonVariants(), "h-9 px-4")}>
            Abrir dashboard
            <ArrowRight className="size-3.5" />
          </Link>
        </nav>
      </header>

      <section className="relative overflow-hidden border-b border-border/60">
        <div className="pointer-events-none absolute inset-0 [mask-image:radial-gradient(60%_50%_at_50%_0%,black,transparent)] bg-[linear-gradient(to_right,var(--border)_1px,transparent_1px),linear-gradient(to_bottom,var(--border)_1px,transparent_1px)] bg-[size:56px_56px]" />
        <div className="pointer-events-none absolute -top-40 left-1/2 size-[38rem] -translate-x-1/2 rounded-full bg-emerald-500/15 blur-[120px]" />

        <div className="relative mx-auto grid max-w-6xl gap-16 px-6 py-24 lg:grid-cols-[1.1fr_0.9fr] lg:items-center lg:py-32">
          <div>
            <span className="inline-flex items-center gap-2 rounded-full border border-border bg-card/60 px-3 py-1 text-[11px] text-muted-foreground">
              <span className="size-1.5 animate-pulse rounded-full bg-emerald-500" />
              O aluguel do futuro · registro on-chain ativo
            </span>

            <h1 className="mt-6 text-4xl leading-[1.05] font-semibold tracking-tight text-balance sm:text-5xl lg:text-6xl">
              Contratos de aluguel que{" "}
              <span className="bg-gradient-to-r from-emerald-400 to-cyan-400 bg-clip-text text-transparent">
                preservam
              </span>{" "}
              o histórico.
            </h1>

            <p className="mt-6 max-w-xl text-sm leading-relaxed text-muted-foreground sm:text-base">
              Organize imóveis, locatários e contratos, preserve os dados utilizados na criação e acompanhe documentos e pagamentos em um só lugar.
            </p>

            <div className="mt-9 flex flex-wrap items-center gap-3">
              <Link href="/dashboard" className={cn(buttonVariants(), "h-11 px-6 text-sm")}>
                Ir para o dashboard
                <ArrowRight className="size-4" />
              </Link>
              <a
                href="#como-funciona"
                className={cn(buttonVariants({ variant: "outline" }), "h-11 px-6 text-sm")}
              >
                Ver como funciona
              </a>
            </div>

            <ul className="mt-10 flex flex-wrap gap-x-6 gap-y-2 text-xs text-muted-foreground">
              {["Dados históricos preservados", "Etapas de assinatura das partes", "Histórico de operações"].map(
                (item) => (
                  <li key={item} className="flex items-center gap-1.5">
                    <CheckCircle className="size-3.5 text-emerald-500" weight="fill" />
                    {item}
                  </li>
                ),
              )}
            </ul>
          </div>

          <div className="relative">
            <div className="absolute -inset-4 rounded-3xl bg-gradient-to-br from-emerald-500/20 via-transparent to-cyan-500/20 blur-2xl" />
            <div className="relative rounded-2xl border border-border bg-card p-6 shadow-2xl">
              <div className="flex items-center justify-between text-[11px] text-muted-foreground">
                <span>CONTRATO DE LOCAÇÃO · AP-301</span>
                <span className="inline-flex items-center gap-1 rounded-full bg-emerald-500/10 px-2 py-0.5 text-emerald-500">
                  <SealCheck className="size-3" weight="fill" />
                  Íntegro
                </span>
              </div>

              <p className="mt-5 text-[11px] tracking-wider text-muted-foreground">HASH DO DOCUMENTO</p>
              <p className="mt-1 break-all text-sm text-foreground">
                0x8f3a91c47b2ee0d6a15f8c0b3d94e77af21c19b5
              </p>

              <dl className="mt-6 grid grid-cols-2 gap-4 border-t border-border pt-5 text-xs">
                <div>
                  <dt className="text-muted-foreground">Bloco</dt>
                  <dd className="mt-1 font-medium">#1.284.930</dd>
                </div>
                <div>
                  <dt className="text-muted-foreground">Registro</dt>
                  <dd className="mt-1 font-medium">12/08/2026 14:32</dd>
                </div>
                <div>
                  <dt className="text-muted-foreground">Assinaturas</dt>
                  <dd className="mt-1 font-medium">2 de 2</dd>
                </div>
                <div>
                  <dt className="text-muted-foreground">Alterações</dt>
                  <dd className="mt-1 font-medium text-emerald-500">nenhuma</dd>
                </div>
              </dl>

              <div className="mt-6 flex items-center gap-1.5">
                {[0, 1, 2, 3, 4].map((i) => (
                  <div key={i} className="flex items-center gap-1.5">
                    <div
                      className={cn(
                        "size-7 rounded-md border border-border",
                        i === 3 ? "border-emerald-500/60 bg-emerald-500/15" : "bg-muted",
                      )}
                    />
                    {i < 4 && <LinkSimple className="size-3 text-muted-foreground" />}
                  </div>
                ))}
                <span className="ml-2 text-[11px] text-muted-foreground">cadeia verificada</span>
              </div>
            </div>
          </div>
        </div>
      </section>

      <section className="border-b border-border/60 bg-card/30">
        <dl className="mx-auto grid max-w-6xl grid-cols-2 gap-px px-6 py-12 sm:grid-cols-4">
          {numeros.map((n) => (
            <div key={n.label} className="px-2 text-center">
              <dt className="text-2xl font-semibold tracking-tight sm:text-3xl">{n.valor}</dt>
              <dd className="mt-1 text-xs text-muted-foreground">{n.label}</dd>
            </div>
          ))}
        </dl>
      </section>

      <section id="recursos" className="mx-auto max-w-6xl px-6 py-24">
        <p className="text-xs tracking-widest text-emerald-500">RECURSOS</p>
        <h2 className="mt-3 max-w-2xl text-3xl font-semibold tracking-tight text-balance sm:text-4xl">
          Tudo que um contrato precisa — menos a possibilidade de ser adulterado.
        </h2>

        <div className="mt-14 grid gap-px overflow-hidden rounded-2xl border border-border bg-border sm:grid-cols-2 lg:grid-cols-3">
          {recursos.map(({ icon: Icon, title, text }) => (
            <div key={title} className="group bg-background p-7 transition-colors hover:bg-card">
              <Icon
                className="size-6 text-muted-foreground transition-colors group-hover:text-emerald-500"
                weight="duotone"
              />
              <h3 className="mt-5 text-sm font-medium">{title}</h3>
              <p className="mt-2 text-xs leading-relaxed text-muted-foreground">{text}</p>
            </div>
          ))}
        </div>
      </section>

      <section id="como-funciona" className="border-y border-border/60 bg-card/30">
        <div className="mx-auto max-w-6xl px-6 py-24">
          <p className="text-xs tracking-widest text-emerald-500">COMO FUNCIONA</p>
          <h2 className="mt-3 max-w-2xl text-3xl font-semibold tracking-tight text-balance sm:text-4xl">
            Organize a criação e a consulta dos contratos.
          </h2>

          <ol className="mt-14 grid gap-10 md:grid-cols-3">
            {passos.map((p) => (
              <li key={p.n} className="relative border-t border-border pt-6">
                <span className="absolute -top-px left-0 h-px w-12 bg-emerald-500" />
                <span className="text-xs text-muted-foreground">{p.n}</span>
                <h3 className="mt-3 text-base font-medium">{p.title}</h3>
                <p className="mt-2 text-xs leading-relaxed text-muted-foreground">{p.text}</p>
              </li>
            ))}
          </ol>
        </div>
      </section>

      <section id="seguranca" className="mx-auto grid max-w-6xl gap-12 px-6 py-24 lg:grid-cols-2 lg:items-center">
        <div>
          <p className="text-xs tracking-widest text-emerald-500">SEGURANÇA</p>
          <h2 className="mt-3 text-3xl font-semibold tracking-tight text-balance sm:text-4xl">
            Documentos privados e consulta pública limitada.
          </h2>
          <p className="mt-6 text-sm leading-relaxed text-muted-foreground">
            A consulta pública informa reconhecimento e situação do contrato. A verificação de integridade de um arquivo exige uma comparação específica com o registro correspondente na blockchain.
          </p>
          <ul className="mt-8 space-y-3 text-sm">
            {[
              { icon: ShieldCheck, text: "Downloads de documentos exigem autenticação" },
              { icon: Fingerprint, text: "Dados pessoais omitidos da consulta pública" },
              { icon: Cube, text: "Limite de tentativas na consulta pública" },
            ].map(({ icon: Icon, text }) => (
              <li key={text} className="flex items-start gap-3">
                <Icon className="mt-0.5 size-4 shrink-0 text-emerald-500" weight="duotone" />
                <span className="text-muted-foreground">{text}</span>
              </li>
            ))}
          </ul>
        </div>

        <div className="rounded-2xl border border-border bg-card p-6">
          <p className="text-[11px] tracking-wider text-muted-foreground">EXEMPLO CONCEITUAL DE EVIDÊNCIA</p>
          <pre className="mt-4 overflow-x-auto rounded-lg bg-muted p-4 text-[11px] leading-relaxed text-muted-foreground">
{`{
  "documento": "0x8f3a91c4...21c19b5",
  "modelo":    "locacao-residencial-v3",
  "assinaturas": 2,
  "timestamp": 1786...
}`}
          </pre>
          <p className="mt-4 text-[11px] leading-relaxed text-muted-foreground">
            Exemplo ilustrativo; este quadro não representa uma transação verificada.
          </p>

          <p className="mt-6 border-t border-border pt-5 text-[11px] leading-relaxed text-muted-foreground">
            <span className="text-foreground">Guarde seu documento:</span> mantenha uma cópia do contrato assinado e consulte a equipe responsável para informações sobre sua formalização.
          </p>
        </div>
      </section>

      <section id="planos" className="border-t border-border/60 bg-card/30">
        <div className="mx-auto max-w-6xl px-6 py-24">
          <p className="text-xs tracking-widest text-emerald-500">PLANOS</p>
          <h2 className="mt-3 max-w-2xl text-3xl font-semibold tracking-tight text-balance sm:text-4xl">
            Planos ilustrativos do projeto.
          </h2>
          <p className="mt-4 max-w-xl text-sm text-muted-foreground">
            Valores e recursos apresentados nesta seção são ilustrativos. A contratação e a cobrança não estão disponíveis nesta versão.
          </p>

          <div className="mt-14 grid gap-6 lg:grid-cols-3 lg:items-start">
            {planos.map((plano) => (
              <div
                key={plano.nome}
                className={cn(
                  "relative flex h-full flex-col rounded-2xl border p-7",
                  plano.destaque
                    ? "border-emerald-500/50 bg-card shadow-xl lg:-mt-4 lg:pb-9"
                    : "border-border bg-background",
                )}
              >
                {plano.destaque && (
                  <span className="absolute -top-3 left-7 rounded-full bg-emerald-500 px-2.5 py-0.5 text-[10px] font-medium text-background">
                    Mais escolhido
                  </span>
                )}

                <h3 className="text-sm font-medium">{plano.nome}</h3>
                <p className="mt-1 text-xs text-muted-foreground">{plano.resumo}</p>

                <p className="mt-6 flex items-baseline gap-1">
                  <span className="text-3xl font-semibold tracking-tight">{plano.preco}</span>
                  <span className="text-xs text-muted-foreground">{plano.periodo}</span>
                </p>

                <ul className="mt-7 flex-1 space-y-2.5 border-t border-border pt-6 text-xs">
                  {plano.itens.map((item) => (
                    <li key={item} className="flex items-start gap-2">
                      <CheckCircle
                        className="mt-px size-3.5 shrink-0 text-emerald-500"
                        weight="fill"
                      />
                      <span className="text-muted-foreground">{item}</span>
                    </li>
                  ))}
                </ul>

                <Link
                  href="/dashboard"
                  className={cn(
                    buttonVariants({ variant: plano.destaque ? "default" : "outline" }),
                    "mt-8 h-10 w-full text-xs",
                  )}
                >
                  {plano.cta}
                  <ArrowRight className="size-3.5" />
                </Link>
              </div>
            ))}
          </div>

          <p className="mt-8 text-[11px] text-muted-foreground">
            Todos os planos incluem verificação pública ilimitada — conferir um hash é sempre de
            graça, para qualquer pessoa.
          </p>
        </div>
      </section>

      <section className="border-t border-border/60">
        <div className="relative mx-auto max-w-6xl overflow-hidden px-6 py-24 text-center">
          <div className="pointer-events-none absolute bottom-0 left-1/2 size-[30rem] -translate-x-1/2 translate-y-1/2 rounded-full bg-emerald-500/15 blur-[110px]" />
          <h2 className="relative text-3xl font-semibold tracking-tight text-balance sm:text-4xl">
            Comece registrando o próximo contrato.
          </h2>
          <p className="relative mx-auto mt-4 max-w-md text-sm text-muted-foreground">
            Acesse os cadastros, modelos, contratos e operações da sua equipe.
          </p>
          <Link
            href="/dashboard"
            className={cn(buttonVariants(), "relative mt-9 h-11 px-7 text-sm")}
          >
            Ir para o dashboard
            <ArrowRight className="size-4" />
          </Link>
        </div>
      </section>

      <ValidarContrato />

      <footer className="border-t border-border/60">
        <div className="mx-auto flex max-w-6xl flex-col items-center justify-between gap-4 px-6 py-8 text-xs text-muted-foreground sm:flex-row">
          <span className="flex items-center gap-2">
            <span className="flex size-6 items-center justify-center rounded-md bg-zinc-900">
              <Image
                src="/logo/mobiContract.svg"
                alt="Logo MobiContract"
                width={20}
                height={20}
                className="size-4"
              />
            </span>
            MobiContract · O aluguel do futuro
          </span>
          <span>TCC · {currentYear}</span>
        </div>
      </footer>
    </div>
  )
}
