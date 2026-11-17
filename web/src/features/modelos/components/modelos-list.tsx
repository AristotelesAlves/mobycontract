"use client"

import { DotsThree, MagnifyingGlass, Plus } from "@phosphor-icons/react"
import { Badge } from "@/components/ui/badge"
import { Button } from "@/components/ui/button"
import { Dialog } from "@/components/ui/dialog"
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
    Card,
    CardDescription,
    CardTitle,
} from "@/components/ui/card"
import { Input } from "@/components/ui/input"
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from "@/components/ui/table"
import { TablePagination } from "@/components/ui/table-pagination"
import { ModalCloneModelo } from "./modal-clone-modelo"
import { ModalNovoModelo } from "./modal-novo-modelo"
import type { IModeloClonar } from "../data/modelo-types"
import { useModelList } from "../hooks/use-model-list"
import { useDocumentTemplates } from "../hooks/use-document-templates"

interface ModelosListProps {
    modelos?: IModeloClonar[]
}

export function ModelosList({ modelos }: ModelosListProps) {
    const templates = useDocumentTemplates()
    const list = useModelList(modelos ?? templates)

    return (
        <div className="flex flex-col gap-4">
            <Card className="p-4">
                <div className="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                    <div>
                        <CardDescription>
                            Modelos de Documentos
                        </CardDescription>
                        <CardTitle>
                            Gerencie e versione os modelos da sua organização.
                        </CardTitle>
                    </div>
                    <Button className="gap-2 whitespace-nowrap" onClick={list.openCreationModal}>
                        <Plus className="h-4 w-4" />
                        Novo Modelo
                    </Button>
                </div>
            </Card>

            <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-2">
                <div className="relative w-full sm:max-w-md">
                    <MagnifyingGlass className="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-muted-foreground" />
                    <Input
                        placeholder="Buscar por código ou título do modelo..."
                        className="pl-9"
                        value={list.searchTerm}
                        onChange={(event) => list.changeSearchTerm(event.target.value)}
                    />
                </div>

                <div className="flex items-center gap-2 flex-wrap">
                    <Button
                        size="sm"
                        variant={list.typeFilter === "todos" ? "default" : "outline"}
                        onClick={() => list.changeTypeFilter("todos")}
                        className="text-xs h-8"
                    >
                        Todos ({list.counts.todos})
                    </Button>
                    <Button
                        size="sm"
                        variant={list.typeFilter === "Contrato" ? "default" : "outline"}
                        onClick={() => list.changeTypeFilter("Contrato")}
                        className="text-xs h-8"
                    >
                        Contrato ({list.counts.contratos})
                    </Button>
                    <Button
                        size="sm"
                        variant={list.typeFilter === "Aditivo" ? "default" : "outline"}
                        onClick={() => list.changeTypeFilter("Aditivo")}
                        className="text-xs h-8"
                    >
                        Aditivo ({list.counts.aditivos})
                    </Button>
                    {list.counts.distratos > 0 && (
                        <Button
                            size="sm"
                            variant={list.typeFilter === "Distrato" ? "default" : "outline"}
                            onClick={() => list.changeTypeFilter("Distrato")}
                            className="text-xs h-8"
                        >
                            Distrato ({list.counts.distratos})
                        </Button>
                    )}
                    {list.counts.laudos > 0 && (
                        <Button
                            size="sm"
                            variant={list.typeFilter === "Laudo" ? "default" : "outline"}
                            onClick={() => list.changeTypeFilter("Laudo")}
                            className="text-xs h-8"
                        >
                            Laudo ({list.counts.laudos})
                        </Button>
                    )}
                </div>
            </div>

            <div className="rounded-xl border border-border bg-card shadow-sm overflow-hidden">
        <div className="w-full overflow-x-auto">

                <Table className="w-full">
                        <TableHeader>
                            <TableRow>
                                <TableHead>Código</TableHead>
                                <TableHead>Título</TableHead>
                                <TableHead className="text-center">Tipo</TableHead>
                                <TableHead className="text-center">Versão</TableHead>
                                <TableHead className="text-center">Status</TableHead>
                                <TableHead className="text-right">Ações</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            {list.models.length === 0 ? (
                                <TableRow>
                                    <TableCell colSpan={6} className="text-center py-12 text-muted-foreground">
                                        Nenhum modelo encontrado para os filtros selecionados.
                                    </TableCell>
                                </TableRow>
                            ) : (
                                list.models.map((modelo) => (
                                    <TableRow
                                        key={modelo.codigo}
                                        className="hover:bg-muted/50 transition-colors"
                                    >
                                        <TableCell className="font-semibold text-foreground">#{modelo.codigo}</TableCell>
                                        <TableCell className="font-medium">{modelo.titulo}</TableCell>
                                        <TableCell className="text-center text-xs">
                                            {modelo.tipo}
                                        </TableCell>
                                        <TableCell className="text-center text-xs">
                                            v{modelo.versao}
                                        </TableCell>
                                        <TableCell className="text-center">
                                            {modelo.status ? (
                                                <Badge className="text-xs">Ativo</Badge>
                                            ) : (
                                                <Badge variant="secondary" className="text-xs">
                                                    Inativo
                                                </Badge>
                                            )}
                                        </TableCell>
                                        <TableCell className="text-right" onClick={(e) => e.stopPropagation()}>
                                            <DropdownMenu>
                                                <DropdownMenuTrigger
                                                    render={
                                                        <Button variant="ghost" className="h-8 w-8 p-0">
                                                            <span className="sr-only">Abrir menu de ações</span>
                                                            <DotsThree className="h-5 w-5" weight="bold" />
                                                        </Button>
                                                    }
                                                />
                                                <DropdownMenuContent align="end">
                                                    <DropdownMenuGroup>
                                                        <DropdownMenuLabel>Ações</DropdownMenuLabel>
                                                        <DropdownMenuSeparator />
                                                        <DropdownMenuItem
                                                            onClick={() => list.openEditor(modelo)}
                                                            className="cursor-pointer gap-2"
                                                        >
                                                            Visualizar
                                                        </DropdownMenuItem>
                                                        <DropdownMenuItem
                                                            onClick={() => list.openCloneModal(modelo)}
                                                            className="cursor-pointer gap-2"
                                                        >
                                                            Clonar
                                                        </DropdownMenuItem>
                                                        <DropdownMenuItem
                                                            onClick={() => list.openEditor(modelo)}
                                                            disabled={modelo.podeAlterar === false}
                                                            title={modelo.podeAlterar === false ? "Modelo vinculado a contrato. Clone uma nova versão para alterar." : undefined}
                                                            className="cursor-pointer gap-2"
                                                        >
                                                            Editar Modelo
                                                        </DropdownMenuItem>
                                                    </DropdownMenuGroup>
                                                </DropdownMenuContent>
                                            </DropdownMenu>
                                        </TableCell>
                                    </TableRow>
                                ))
                            )}
                        </TableBody>
                            </Table>
        </div>

        {list.filteredCount > 0 && (
                <div className="flex justify-between items-center p-3 border-t border-border">
                    <TablePagination
                        currentPage={list.page}
                        totalItems={list.filteredCount}
                        pageSize={list.pageSize}
                        onPageChange={list.goToPage}
                        itemLabel="modelos"
                    />
                </div>
            )}
      </div>

            <Dialog
                open={list.cloneModalOpen}
                onOpenChange={list.closeCloneModal}
            >
                {list.selectedModel && (
                    <ModalCloneModelo
                        modelo={list.selectedModel}
                        onClose={list.closeCloneModal}
                    />
                )}
            </Dialog>

            <Dialog
                open={list.creationModalOpen}
                onOpenChange={list.closeCreationModal}
            >
                <ModalNovoModelo onClose={list.closeCreationModal} />
            </Dialog>
        </div>
    )
}
