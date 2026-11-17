import { EditarInquilinoView } from "@/features/inquilinos/views/editar-inquilino-view";

export default async function EditarInquilinoPage({
  params,
}: {
  params: Promise<{ id: string }>;
}) {
  const { id } = await params;
  return <EditarInquilinoView tenantId={id} />;
}
