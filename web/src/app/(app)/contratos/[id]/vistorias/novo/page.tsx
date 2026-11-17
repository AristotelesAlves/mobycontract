import { VistoriaRegistrationView } from "@/features/vistorias/views/vistoria-registration-view";

interface INovaVistoriaPageProps {
  params: Promise<{ id: string }>;
}

export default async function Page(props: INovaVistoriaPageProps) {
  const { id } = await props.params;

  return <VistoriaRegistrationView contratoId={id} />;
}
