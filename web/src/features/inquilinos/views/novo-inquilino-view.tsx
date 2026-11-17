"use client";

import { useRouter } from "next/navigation";
import { Check } from "@phosphor-icons/react";
import { Button } from "@/components/ui/button";
import {
  Card,
  CardContent,
  CardDescription,
  CardHeader,
  CardTitle,
} from "@/components/ui/card";
import { Input } from "@/components/ui/input";
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from "@/components/ui/select";
import { ProfissaoInput } from "../components/profissao-input";
import { MARITAL_STATUS_OPTIONS } from "../data/marital-status-options";
import { useTenantRegistration } from "../hooks/use-tenant-registration";

const MARITAL_STATUS_VALUES: string[] = [...MARITAL_STATUS_OPTIONS];

export function NovoInquilinoView() {
  const router = useRouter();
  const registration = useTenantRegistration(() =>
    router.push("/inquilinos"),
  );
  const { draft, fieldErrors } = registration;

  return (
    <div className="flex flex-col gap-4">
      <Card>
        <CardHeader>
          <CardTitle>Cadastro completo de inquilino</CardTitle>
          <CardDescription>
            Esses dados são obrigatórios para a celebração de um contrato de
            locação.
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
                placeholder="Nome do inquilino"
                value={draft.nome}
                onChange={(event) =>
                  registration.patchField("nome", event.target.value)
                }
              />
            </Field>

            <Field label="CPF *" error={fieldErrors.cpf}>
              <Input
                placeholder="000.000.000-00" inputMode="numeric" maxLength={14}
                value={draft.cpf}
                onChange={(event) =>
                  registration.patchField("cpf", event.target.value)
                }
              />
            </Field>

            <Field label="RG" error={fieldErrors.rg}>
              <Input
                placeholder="Opcional"
                value={draft.rg}
                onChange={(event) =>
                  registration.patchField("rg", event.target.value)
                }
              />
            </Field>

            <Field label="E-mail *" error={fieldErrors.email}>
              <Input
                placeholder="email@exemplo.com"
                value={draft.email}
                onChange={(event) =>
                  registration.patchField("email", event.target.value)
                }
              />
            </Field>

            <Field label="Telefone *" error={fieldErrors.telefone}>
              <Input
                placeholder="(00) 00000-0000" inputMode="tel" maxLength={15}
                value={draft.telefone}
                onChange={(event) =>
                  registration.patchField("telefone", event.target.value)
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
                  registration.patchField("dataNascimento", event.target.value)
                }
              />
            </Field>

            <Field label="Estado civil *" error={fieldErrors.estadoCivil}>
              <Select
                value={draft.estadoCivil}
                onValueChange={(value) => {
                  if (value != null) {
                    registration.patchField("estadoCivil", value);
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
              <ProfissaoInput value={draft.profissao} onChange={(value) => registration.patchField("profissao", value)} />
            </Field>

            <Field label="Renda mensal (R$) *" error={fieldErrors.renda}>
              <Input
                type="text"
                inputMode="decimal"
                placeholder="1.500,00"
                value={draft.renda}
                onChange={(event) =>
                  registration.patchField("renda", event.target.value)
                }
              />
            </Field>


          </div>

          {registration.submitError && (
            <p className="text-destructive text-sm">
              {registration.submitError}
            </p>
          )}

          <div className="flex justify-end gap-2">
            <Button variant="outline" onClick={() => router.push("/inquilinos")}>
              Cancelar
            </Button>
            <Button
              onClick={() => void registration.register()}
              disabled={registration.submitting}
              className="gap-2"
            >
              <Check size={16} />
              {registration.submitting
                ? "Cadastrando inquilino..."
                : "Cadastrar inquilino"}
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
