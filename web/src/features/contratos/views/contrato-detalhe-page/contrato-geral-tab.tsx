"use client";

import type { Contrato } from "../../data/contrato-types";
import { ContratoDetalhesCard } from "./contrato-detalhes-card";
import { ContratoHistoricoTable } from "./contrato-historico-table";
import { derivarHistoricoContrato } from "../../use-cases/contract-life-events";

interface IContratoGeralTabProps {
  contrato: Contrato;
}

export function ContratoGeralTab({ contrato }: IContratoGeralTabProps) {
  const eventos = derivarHistoricoContrato(contrato);

  return (
    <div className="animate-in fade-in duration-500 flex flex-col gap-8">
      <ContratoDetalhesCard contrato={contrato} />
      <ContratoHistoricoTable eventos={eventos} />
    </div>
  );
}
