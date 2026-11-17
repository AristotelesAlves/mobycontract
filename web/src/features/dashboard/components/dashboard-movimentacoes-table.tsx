"use client"

import { Copy, DotsThree, Eye } from "@phosphor-icons/react/dist/ssr"
import { Button } from "@/components/ui/button"
import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuGroup,
  DropdownMenuItem,
  DropdownMenuLabel,
  DropdownMenuSeparator,
  DropdownMenuTrigger,
} from "@/components/ui/dropdown-menu"
import {
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from "@/components/ui/table"
import { Badge } from "@/components/ui/badge"

export interface MovimentacaoResumo {
  id: number
  type: string
  user: string
  info: string
}

interface DashboardMovimentacoesTableProps {
  movimentacoes: MovimentacaoResumo[]
}

function getBadgeVariant(type: string): "default" | "secondary" | "destructive" | "outline" {
  const lower = type.toLowerCase()
  if (lower.includes('blockchain') || lower.includes('registro')) return 'default'
  if (lower.includes('novo') || lower.includes('cadastrado')) return 'secondary'
  if (lower.includes('cancelado') || lower.includes('encerrado')) return 'destructive'
  return 'outline'
}

export function DashboardMovimentacoesTable({ movimentacoes }: DashboardMovimentacoesTableProps) {
  const displayItems = movimentacoes.slice(0, 5)

  return (
    <div className="w-full overflow-auto">
      <Table>
        <TableHeader>
          <TableRow>
            <TableHead>Ação Realizada</TableHead>
            <TableHead>Usuário</TableHead>
            <TableHead>Tipo</TableHead>
            <TableHead className="text-right">Opções</TableHead>
          </TableRow>
        </TableHeader>
        <TableBody>
          {displayItems.map((e) => (
            <TableRow key={e.id}>
              <TableCell className="font-medium max-w-[200px] truncate" title={e.info}>
                {e.info}
              </TableCell>
              <TableCell className="text-muted-foreground">{e.user}</TableCell>
              <TableCell>
                <Badge variant={getBadgeVariant(e.type)}>
                  {e.type}
                </Badge>
              </TableCell>
              <TableCell className="text-right">
                <DropdownMenu>
                  <DropdownMenuTrigger render={
                    <Button variant="ghost" className="h-8 w-8 p-0">
                      <span className="sr-only">Abrir menu</span>
                      <DotsThree className="h-5 w-5" weight="bold" />
                    </Button>
                  } />
                  <DropdownMenuContent align="end">
                    <DropdownMenuGroup>
                      <DropdownMenuLabel>Ações</DropdownMenuLabel>
                      <DropdownMenuSeparator />
                      <DropdownMenuItem className="cursor-pointer gap-2">
                        <Eye size={16} /> Ver detalhes
                      </DropdownMenuItem>
                      <DropdownMenuItem className="cursor-pointer gap-2">
                        <Copy size={16} /> Copiar ID
                      </DropdownMenuItem>
                    </DropdownMenuGroup>
                  </DropdownMenuContent>
                </DropdownMenu>
              </TableCell>
            </TableRow>
          ))}
        </TableBody>
      </Table>
    </div>
  )
}
