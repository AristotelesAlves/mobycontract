import { NovoImovelView } from "@/features/imoveis/views/novo-imovel-view"

export default async function Page() {
  await new Promise((resolve) => setTimeout(resolve, 1000))

  return <NovoImovelView />
}
