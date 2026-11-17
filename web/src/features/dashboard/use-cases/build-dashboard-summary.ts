import type { Imovel } from "@/features/imoveis/data/imovel-types";
import type { Contrato } from "@/features/contratos/data/contrato-types";
import type { Inquilino } from "@/features/inquilinos/data/inquilino-types";

export interface DashboardMetrics {
  activeContracts: number;
  vacantProperties: number;
  registeredTenants: number;
  registeredSmartContracts: number;
  monthlyRevenue: number;
}

export interface PortfolioSlice {
  contrato: string;
  quantidade: number;
  fill: string;
}

export interface DashboardMovement {
  id: number;
  type: string;
  user: string;
  info: string;
}

const ACTIVE_CONTRACT_STATUSES = ["Vigente", "Validado"] as const;

export function isActiveContract(contract: Contrato): boolean {
  return (ACTIVE_CONTRACT_STATUSES as readonly string[]).includes(
    contract.status,
  );
}

export function buildDashboardMetrics(
  properties: Imovel[],
  contracts: Contrato[],
  tenants: Inquilino[],
): DashboardMetrics {
  const activeContracts = contracts.filter(isActiveContract);

  return {
    activeContracts: activeContracts.length,
    vacantProperties: properties.filter(
      (property) => property.status === "Disponível",
    ).length,
    registeredTenants: tenants.length,
    registeredSmartContracts: contracts.filter(
      (contract) => contract.statusBlockchain === "Registrado",
    ).length,
    monthlyRevenue: activeContracts.reduce(
      (total, contract) => total + (contract.valorMensal ?? 0),
      0,
    ),
  };
}

export function buildPortfolioSlices(
  properties: Imovel[],
  contracts: Contrato[],
): PortfolioSlice[] {
  return [
    {
      contrato: "Alugados",
      quantidade: properties.filter(
        (property) => property.status === "Alugado",
      ).length,
      fill: "#10B981",
    },
    {
      contrato: "Disponíveis",
      quantidade: properties.filter(
        (property) => property.status === "Disponível",
      ).length,
      fill: "#F59E0B",
    },
    {
      contrato: "Em Negociação",
      quantidade: contracts.filter(
        (contract) =>
          contract.status === "Rascunho" ||
          contract.status === "Pendente" ||
          contract.status === "Minuta",
      ).length,
      fill: "#8b5cf6",
    },
    {
      contrato: "Encerrados",
      quantidade: contracts.filter(
        (contract) =>
          contract.status === "Encerrado" || contract.status === "Cancelado",
      ).length,
      fill: "#ef4444",
    },
  ];
}

export function buildDashboardMovements(
  contracts: Contrato[],
  limit = 5,
): DashboardMovement[] {
  return [...contracts]
    .sort((a, b) => b.criadoEm.localeCompare(a.criadoEm))
    .slice(0, limit)
    .map((contract, index) => ({
      id: index + 1,
      type: contract.status,
      user: "Sistema",
      info: `Contrato nº ${contract.numero} — ${contract.imovelTitulo ?? "sem imóvel"}`,
    }));
}
