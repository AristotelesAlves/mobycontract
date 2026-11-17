import { NovoAditivoPage } from "@/features/contratos/views/novo-aditivo-page";

interface INovoAditivoPageProps {
  params: Promise<{ id: string }>;
}

export default async function page(props: INovoAditivoPageProps) {
  const { id } = await props.params;
  return <NovoAditivoPage idInicial={id} />;
}