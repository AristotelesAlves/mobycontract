"use client";

import { useRouter } from "next/navigation";
import {
  ArrowClockwise,
  Check,
  WarningCircle,
} from "@phosphor-icons/react";
import { Button } from "@/components/ui/button";
import {
  Card,
  CardContent,
  CardDescription,
  CardHeader,
  CardTitle,
} from "@/components/ui/card";
import { Input } from "@/components/ui/input";
import { Skeleton } from "@/components/ui/skeleton";
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from "@/components/ui/select";
import { ProfissaoInput } from "../components/profissao-input";
import { MARITAL_STATUS_OPTIONS } from "../data/marital-status-options";
import { useTenantEdit } from "../hooks/use-tenant-edit";

const MARITAL_STATUS_VALUES: string[] = [...MARITAL_STATUS_OPTIONS];

export function EditarInquilinoView({ tenantId }: { tenantId: string }) {
  const router = useRouter();
  const edition = useTenantEdit(tenantId, () => router.push("/inquilinos"));
  const { draft, fieldErrors } = edition;

  if (edition.loading) {
    return (
      <div className="flex flex-col gap-4">
        <Card>
          <CardHeader>
            <Skeleton className="h-6 w-72" />
            <Skeleton className="h-4 w-96" />
          </CardHeader>
          <CardContent className="grid gap-4 md:grid-cols-2">
            {Array.from({ length: 10 }, (_, index) => (
              <Skeleton key={index} className="h-9 w-full" />
            ))}
          </CardContent>
        </Card>
      </div>
    );
  }

  if (edition.loadError) {
    return (
      <div className="flex flex-col gap-4">
        <Card>
          <CardContent className="py-12 flex flex-col items-center gap-3 text-center">
            <WarningCircle size={28} className="text-destructive" />
            <p className="text-sm text-destructive">{edition.loadError}</p>
            <Button
              variant="outline"
              size="sm"
              className="gap-2"
              onClick={edition.retry}
            >
              <ArrowClockwise size={14} /> Tentar novamente
            </Button>
          </CardContent>
        </Card>
      </div>
    );
  }

  return (
    <div className="flex flex-col gap-4">
      <Card>
        <CardHeader>
          <CardTitle>Editar dados do inquilino</CardTitle>
          <CardDescription>
            Atualize as informações do locatário. Alterações ficam registradas
            na auditoria.
          </CardDescription>
        </CardHeader>
        <CardContent className="space-y-6">
          <div className="grid gap-4 md:grid-cols-2">
            <Field
              label="Nome completo *"
              className="md:col-span-2"
              error={fieldErrors.nome}
            >
              <Input
                value={draft.nome}
                onChange={(event) =>
                  edition.patchField("nome", event.target.value)
                }
              />
            </Field>

            <Field label="CPF *" error={fieldErrors.cpf}>
              <Input
                placeholder="000.000.000-00" inputMode="numeric" maxLength={14}
                value={draft.cpf}
                onChange={(event) =>
                  edition.patchField("cpf", event.target.value)
                }
              />
            </Field>

            <Field label="RG" error={fieldErrors.rg}>
              <Input
                placeholder="Opcional"
                value={draft.rg}
                onChange={(event) =>
                  edition.patchField("rg", event.target.value)
                }
              />
            </Field>

            <Field label="E-mail *" error={fieldErrors.email}>
              <Input
                value={draft.email}
                onChange={(event) =>
                  edition.patchField("email", event.target.value)
                }
              />
            </Field>

            <Field label="Telefone *" error={fieldErrors.telefone}>
              <Input
                inputMode="tel"
                maxLength={15}
                placeholder="(00) 00000-0000"
                value={draft.telefone}
                onChange={(event) =>
                  edition.patchField("telefone", event.target.value)
                }
              />
            </Field>

            <Field
              label="Data de nascimento *"
              error={fieldErrors.dataNascimento}
            >
              <Input
                type="date"
                value={draft.dataNascimento}
                onChange={(event) =>
                  edition.patchField("dataNascimento", event.target.value)
                }
              />
            </Field>

            <Field label="Estado civil *" error={fieldErrors.estadoCivil}>
              <Select
                value={draft.estadoCivil}
                onValueChange={(value) => {
                  if (value != null) {
                    edition.patchField("estadoCivil", value);
                  }
                }}
              >
                <SelectTrigger className="w-full">
                  <SelectValue placeholder="Selecione" />
                </SelectTrigger>
                <SelectContent>
                  {MARITAL_STATUS_VALUES.map((option) => (
                    <SelectItem key={option} value={option}>
                      {option}
                    </SelectItem>
                  ))}
                </SelectContent>
              </Select>
            </Field>

            <Field label="Profissão" error={fieldErrors.profissao}>
              <ProfissaoInput value={draft.profissao} onChange={(value) => edition.patchField("profissao", value)} />
            </Field>

            <Field label="Renda mensal (R$) *" error={fieldErrors.renda}>
              <Input
                type="text"
                inputMode="decimal"
                placeholder="1.500,00"
                value={draft.renda}
                onChange={(event) =>
                  edition.patchField("renda", event.target.value)
                }
              />
            </Field>


          </div>

          {edition.submitError && (
            <p className="text-destructive text-sm">{edition.submitError}</p>
          )}

          <div className="flex justify-end gap-2">
            <Button variant="outline" onClick={() => router.push("/inquilinos")}>
              Cancelar
            </Button>
            <Button
              onClick={() => void edition.save()}
              disabled={edition.saving}
              className="gap-2"
            >
              <Check size={16} />
              {edition.saving ? "Salvando alterações..." : "Salvar alterações"}
            </Button>
          </div>
        </CardContent>
      </Card>
    </div>
  );
}

function Field({
  label,
  error,
  className,
  children,
}: {
  label: string;
  error?: string;
  className?: string;
  children: React.ReactNode;
}) {
  return (
    <div className={`space-y-2 ${className ?? ""}`}>
      <label className="text-sm font-medium">{label}</label>
      {children}
      {error && <p className="text-destructive text-xs">{error}</p>}
    </div>
  );
}
