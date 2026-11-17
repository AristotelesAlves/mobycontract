import { ImoveisView } from "@/features/imoveis/views/imoveis-view"

export default async function Page() {
  await new Promise((resolve) => setTimeout(resolve, 1000))

  return <ImoveisView />
}
