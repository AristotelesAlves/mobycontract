import { EditarAditivoPage } from "@/features/contratos/views/editar-aditivo-page";

export default async function EditarAditivo({
  params,
}: {
  params: Promise<{ id: string; aditivoId: string }>;
}) {
  const { id, aditivoId } = await params;
  return <EditarAditivoPage idContrato={id} idAditivo={aditivoId} />;
}
