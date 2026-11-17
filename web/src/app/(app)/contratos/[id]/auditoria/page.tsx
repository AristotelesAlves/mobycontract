import { ContratoAuditoria } from "@/features/contratos/components/contrato-auditoria";

interface ContratoAuditoriaRouteProps {
  params: Promise<{ id: string }>;
}

export default async function ContratoAuditoriaRoute(props: ContratoAuditoriaRouteProps) {
  const { id } = await props.params;
  return <ContratoAuditoria idInicial={id} />;
}
