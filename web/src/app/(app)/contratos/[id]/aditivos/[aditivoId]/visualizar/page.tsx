import VisualizarAditivoPage from "@/features/contratos/views/visualizar-aditivo-page";

interface IPageProps {
  params: Promise<{ id: string; aditivoId: string }>;
}

export default async function Page(props: IPageProps) {
  const { id, aditivoId } = await props.params;
  return <VisualizarAditivoPage id={id} aditivoId={aditivoId} />;
}