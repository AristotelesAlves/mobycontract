"use client";

import { useCallback, useEffect, useState } from "react";
import { useRouter } from "next/navigation";
import { Camera, MagnifyingGlass } from "@phosphor-icons/react";
import { Badge } from "@/components/ui/badge";
import { Button } from "@/components/ui/button";
import { Card, CardContent } from "@/components/ui/card";
import { Input } from "@/components/ui/input";
import {
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from "@/components/ui/table";
import { TablePagination } from "@/components/ui/table-pagination";
import {
  ListEmptyState,
  ListErrorState,
  TableLoadingRows,
} from "@/components/ui/table-feedback";
import { listContracts } from "@/features/contratos/services/contract-service";
import type { ContratoStatus } from "@/features/contratos/data/contrato-types";
import { getReadableErrorMessage } from "@/services/api/get-readable-error-message";

const PAGE_SIZE = 10;

const COR_STATUS_CONTRATO: Partial<Record<ContratoStatus, "default" | "secondary" | "destructive" | "outline">> = {
  Rascunho: "outline",
  Minuta: "outline",
  Pendente: "secondary",
  Validado: "secondary",
  Vigente: "default",
  Encerrado: "secondary",
  Cancelado: "destructive",
};

export function VistoriasView() {
  const router = useRouter();

  const [contratos, setContratos] = useState<
    Awaited<ReturnType<typeof listContracts>>["itens"]
  >([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState("");
  const [reloadKey, setReloadKey] = useState(0);
  const [searchTerm, setSearchTerm] = useState("");
  const [page, setPage] = useState(1);
  const [filteredCount, setFilteredCount] = useState(0);

  const retry = useCallback(() => {
    setReloadKey((current) => current + 1);
  }, []);

  useEffect(() => {
    let cancelado = false;

    async function carregar() {
      setLoading(true);
      setError("");
      try {
        const resultado = await listContracts({
          texto: searchTerm,
          pagina: page,
          tamanhoPagina: PAGE_SIZE,
          ordenarPor: "created_at",
          direcao: "desc",
        });
        if (cancelado) return;
        setContratos(resultado.itens);
        setFilteredCount(resultado.total);
        if (resultado.pagina && resultado.pagina !== page) {
          setPage(resultado.pagina);
        }
      } catch (erro) {
        if (cancelado) return;
        setContratos([]);
        setFilteredCount(0);
        setError(getReadableErrorMessage(erro));
      } finally {
        if (!cancelado) setLoading(false);
      }
    }

    void carregar();
    return () => {
      cancelado = true;
    };
  }, [searchTerm, page, reloadKey]);

  return (
    <div className="flex flex-col gap-4">
      <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div>
          <h1 className="text-xl font-semibold tracking-tight flex items-center gap-2">
            <Camera size={20} className="text-primary" />
            Vistorias
          </h1>
          <p className="text-sm text-muted-foreground">
            As vistorias pertencem a um contrato. Selecione o contrato para
            gerenciar (criar, agendar, concluir) as vistorias de entrada,
            periódicas e de retirada.
          </p>
        </div>
      </div>

      <div className="flex sm:justify-end">
        <div className="relative w-full sm:max-w-xs">
          <MagnifyingGlass className="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
          <Input
            placeholder="Buscar contrato..."
            className="pl-9"
            value={searchTerm}
            onChange={(evento) => {
              setSearchTerm(evento.target.value);
              setPage(1);
            }}
          />
        </div>
      </div>

      <Card>
        <CardContent className="p-0 overflow-hidden">
          <div className="w-full overflow-x-auto">
            <Table>
              <TableHeader>
                <TableRow>
                  <TableHead>Contrato</TableHead>
                  <TableHead>Imóvel</TableHead>
                  <TableHead>Inquilino</TableHead>
                  <TableHead className="text-center">Status</TableHead>
                  <TableHead className="text-right">Vistorias</TableHead>
                </TableRow>
              </TableHeader>
              <TableBody>
                {loading ? (
                  <TableLoadingRows rows={5} />
                ) : error ? (
                  <ListErrorState message={error} onRetry={retry} />
                ) : contratos.length === 0 ? (
                  <ListEmptyState message="Nenhum contrato encontrado. Crie um contrato para registrar vistorias." />
                ) : (
                  contratos.map((contrato) => (
                    <TableRow
                      key={contrato.id}
                      className="hover:bg-muted/50 transition-colors"
                    >
                      <TableCell className="font-semibold">
                        {contrato.numero}
                      </TableCell>
                      <TableCell className="text-sm text-muted-foreground max-w-[240px] truncate">
                        {contrato.imovelTitulo ?? "—"}
                      </TableCell>
                      <TableCell className="text-sm">
                        {contrato.inquilinoNome ?? "—"}
                      </TableCell>
                      <TableCell className="text-center">
                        <Badge
                          variant={COR_STATUS_CONTRATO[contrato.status] ?? "secondary"}
                          className="text-xs"
                        >
                          {contrato.status}
                        </Badge>
                      </TableCell>
                      <TableCell className="text-right">
                        <Button
                          size="sm"
                          variant="outline"
                          className="h-8 gap-2 text-xs"
                          onClick={() =>
                            router.push(`/contratos/${contrato.id}?aba=vistorias`)
                          }
                        >
                          <Camera size={14} />
                          Vistorias
                        </Button>
                      </TableCell>
                    </TableRow>
                  ))
                )}
              </TableBody>
            </Table>
          </div>

          {!loading && !error && filteredCount > 0 && (
            <div className="flex justify-between items-center p-3 border-t border-border">
              <TablePagination
                currentPage={page}
                totalItems={filteredCount}
                pageSize={PAGE_SIZE}
                onPageChange={setPage}
                itemLabel="contratos"
              />
            </div>
          )}
        </CardContent>
      </Card>
    </div>
  );
}
