import { Spinner } from "@phosphor-icons/react/dist/ssr";

export default function GlobalLoading() {
  return (
    <div className="flex h-[100dvh] w-full flex-col items-center justify-center bg-background">
      <div className="flex flex-col items-center gap-4 text-primary animate-in fade-in zoom-in duration-500">
        <Spinner size={48} className="animate-spin" />
        <p className="text-sm font-medium text-muted-foreground animate-pulse">Carregando...</p>
      </div>
    </div>
  );
}
