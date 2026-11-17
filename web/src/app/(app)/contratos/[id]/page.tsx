import {ContratoDetalhe} from "@/features/contratos/views/contrato-detalhe-page"



interface IContratoDetalhePageProps {
  params: Promise<{ id: string }>;
}

export default async function page(props: IContratoDetalhePageProps) {
  const { id } = await props.params;
  return <ContratoDetalhe idInicial={id} />;
}