import { ImovelDetalheView } from "@/features/imoveis/views/imovel-detalhe-view";

export default async function ImovelPage({ params }: { params: Promise<{ id: string }> }) {
  const { id } = await params;
  return <ImovelDetalheView idInicial={id} />;
}