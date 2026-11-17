import { VisualizarContratoView } from "@/features/contratos/views/visualizar-contrato-view";

export default async function VisualizarContratoPage({ params }: { params: Promise<{ id: string }> }) {
  const { id } = await params;
  return <VisualizarContratoView idInicial={id} />;
}
