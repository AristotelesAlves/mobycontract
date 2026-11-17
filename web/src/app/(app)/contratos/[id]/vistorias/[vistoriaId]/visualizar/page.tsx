import { VistoriaDetailsView } from "@/features/vistorias/views/vistoria-details-view";

interface IVisualizarVistoriaPageProps {
  params: Promise<{ id: string; vistoriaId: string }>;
}

export default async function Page(props: IVisualizarVistoriaPageProps) {
  const { id, vistoriaId } = await props.params;

  return <VistoriaDetailsView contratoId={id} vistoriaId={vistoriaId} />;
}
