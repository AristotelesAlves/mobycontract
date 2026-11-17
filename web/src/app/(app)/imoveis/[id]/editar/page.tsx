import { NovoImovelView } from "@/features/imoveis/views/novo-imovel-view";

export default async function EditarImovelPage({
  params,
}: {
  params: Promise<{ id: string }>;
}) {
  const { id } = await params;
  return <NovoImovelView propertyId={id} />;
}
