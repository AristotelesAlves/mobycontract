"use client";

import { useRouter } from "next/navigation";
import { Check, SpinnerGap } from "@phosphor-icons/react";
import { Button } from "@/components/ui/button";
import {
  CardContent,
  CardDescription,
  CardHeader,
  CardTitle,
} from "@/components/ui/card";

import { useCreateContract } from "../hooks/use-create-contract";
import { ContractImovelSection } from "../components/contract-imovel-section";
import { ContractInquilinoSection } from "../components/contract-inquilino-section";
import { ContractFinanceiroSection } from "../components/contract-financeiro-section";
import { ContractGarantiaSection } from "../components/contract-garantia-section";
import { ContractMultasSection } from "../components/contract-multas-section";

export function CreateContractView() {
  const router = useRouter();
  const hookData = useCreateContract();
  const { submit: salvar } = hookData;

  return (
    <div className="flex flex-col gap-4 w-full pb-10">
      <CardHeader>
        <CardTitle className="text-xl">Novo contrato</CardTitle>
        <CardDescription>
          Defina todos os parâmetros jurídicos, financeiros e garantias do seu contrato de locação.
        </CardDescription>
      </CardHeader>
      <CardContent className="pt-6">
        {hookData.dadosError && (
          <div className="mb-6 rounded-md border border-destructive/40 bg-destructive/5 px-4 py-3 text-sm text-destructive flex items-center justify-between gap-3">
            <span>{hookData.dadosError}</span>
            <Button variant="outline" size="sm" onClick={hookData.reload}>
              Tentar novamente
            </Button>
          </div>
        )}
        <div className="w-full space-y-10">
          <ContractImovelSection data={hookData} />
          <ContractInquilinoSection data={hookData} />
          
          <hr className="my-8" />
          <ContractFinanceiroSection data={hookData} />

          <hr className="my-8" />
          <ContractGarantiaSection data={hookData} />

          <hr className="my-8" />
          <ContractMultasSection data={hookData} />
        </div>

        <div className="flex justify-end gap-2 pt-6 mt-8 border-t">
          {hookData.submitError && (
            <p className="text-destructive text-sm mr-auto self-center max-w-lg">
              {hookData.submitError}
            </p>
          )}
          <Button variant="outline" onClick={() => router.push("/contratos")}>
            Cancelar
          </Button>
          <Button onClick={() => void salvar()} disabled={hookData.submitting}>
            {hookData.submitting ? (
              <SpinnerGap size={16} className="mr-2 animate-spin" />
            ) : (
              <Check size={16} className="mr-2" />
            )}
            {hookData.submitting ? "Criando contrato..." : "Finalizar e Criar Contrato"}
          </Button>
        </div>
      </CardContent>
    </div>
  );
}
