"use client";

import { useState } from "react";
import { useRouter, useSearchParams } from "next/navigation";
import { LockSimple, EnvelopeSimple, WarningCircle } from "@phosphor-icons/react";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import {
  Card,
  CardContent,
  CardHeader,
  CardTitle,
} from "@/components/ui/card";
import { useAuth } from "@/lib/auth-context";
import { getReadableErrorMessage } from "@/services/api/get-readable-error-message";
import Image from "next/image";

export default function LoginPage() {
  const router = useRouter();
  const searchParams = useSearchParams();
  const { login, isLoading } = useAuth();

  const [email, setEmail] = useState("");
  const [senha, setSenha] = useState("");
  const [erro, setErro] = useState("");
  const [carregando, setCarregando] = useState(false);

  const callbackUrl = searchParams.get("callbackUrl") || "/dashboard";

  async function handleSubmit(event: React.FormEvent) {
    event.preventDefault();
    setErro("");
    setCarregando(true);

    try {
      await login(email, senha);
      router.push(callbackUrl);
      router.refresh();
    } catch (error: unknown) {
      setErro(getReadableErrorMessage(error));
    } finally {
      setCarregando(false);
    }
  }

  return (
    <div className="flex min-h-screen items-center justify-center p-4">
      <Card className="w-full max-w-md">
        <CardHeader className="text-center">
          <Image src="/logo/mobiContract.png" alt="Logo" width={100} height={100} className="mx-auto mb-4" />
          <CardTitle className="flex items-center justify-center gap-2 text-2xl font-bold">
            Bem-vindo Ao MobiContract
          </CardTitle>
        </CardHeader>

        <CardContent>
          <form onSubmit={handleSubmit} className="space-y-4">
            <div className="space-y-2">
              <label htmlFor="email" className="text-sm font-medium">
                E-mail
              </label>
<div className="relative">
              <EnvelopeSimple className="absolute left-3 top-1/2 -translate-y-1/2 size-4 text-muted-foreground" />
              <Input
                  id="email"
                  type="email"
                  placeholder="seu@email.com"
                  value={email}
                  onChange={(e) => setEmail(e.target.value)}
                  className="pl-9"
                  required
                  autoComplete="email"
                  disabled={carregando}
                />
              </div>
            </div>

            <div className="space-y-2">
              <label htmlFor="senha" className="text-sm font-medium">
                Senha
              </label>
              <div className="relative">
                <LockSimple className="absolute left-3 top-1/2 -translate-y-1/2 size-4 text-muted-foreground" />
                <Input
                  id="senha"
                  type="password"
                  placeholder="••••••••"
                  value={senha}
                  onChange={(e) => setSenha(e.target.value)}
                  className="pl-9"
                  required
                  autoComplete="current-password"
                  disabled={carregando}
                />
              </div>
            </div>

            {erro && (
              <div className="flex items-center gap-2 text-sm text-destructive bg-destructive/10 p-3 rounded-md">
                <WarningCircle size={16} />
                {erro}
              </div>
            )}

            <Button type="submit" className="w-full" disabled={carregando || isLoading}>
              {carregando || isLoading ? "Entrando..." : "Entrar"}
            </Button>
          </form>

          <p className="mt-6 text-center text-xs text-muted-foreground">
            © copyright by MobiContract - 2026
          </p>
        </CardContent>
      </Card>
    </div>
  );
}