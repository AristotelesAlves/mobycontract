import { InquilinoDetalhe } from "@/features/inquilinos/components/inquilino-detalhe";

export default async function InquilinoDetalhePage({ params }: { params: Promise<{ id: string }> }) {
  const { id } = await params;
  return <InquilinoDetalhe idInicial={id} />;
}
