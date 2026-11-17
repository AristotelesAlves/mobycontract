import { VistoriaRegistrationView } from "@/features/vistorias/views/vistoria-registration-view";

interface IEditarVistoriaPageProps {
  params: Promise<{ id: string; vistoriaId: string }>;
}

export default async function Page(props: IEditarVistoriaPageProps) {
  const { id, vistoriaId } = await props.params;

  return <VistoriaRegistrationView contratoId={id} vistoriaId={vistoriaId} />;
}
