'use client'

import { useState } from "react"
import { useRouter } from "next/navigation"
import { Input } from "@/components/ui/input"
import { Button } from "@/components/ui/button"
import {
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle
} from "@/components/ui/dialog"
import { IModeloClonar } from "../data/modelo-types"

export function ModalCloneModelo({
    modelo,
    onClose
}: {
    modelo: IModeloClonar
    onClose: () => void
}) {
    const router = useRouter()
    const novoTituloInicial = `${modelo.titulo} - V${modelo.versao + 1}`
    const [novoTitulo, setNovoTitulo] = useState(novoTituloInicial)

    function clonarModelo() {
        const parametros = new URLSearchParams({
            titulo: novoTitulo.trim() || novoTituloInicial,
            tipo: modelo.tipo,
            conteudoHtml: modelo.conteudoHtml,
        })
        parametros.set("versao", String(modelo.versao + 1))
        if (modelo.garantia) parametros.set("garantia", modelo.garantia)

        onClose()
        router.push(`/modelos/novo?${parametros.toString()}`)
    }

    return (
        <DialogContent>
            <DialogHeader>
                <DialogTitle>Clonar modelo</DialogTitle>

                <DialogDescription>
                    O modelo atual será inativado e será criado um novo modelo
                    na versão <strong>V{modelo.versao + 1}</strong>.
                </DialogDescription>
            </DialogHeader>
            <div className="space-y-4 py-4">
                <div>
                    <p className="text-sm font-medium mb-2">Título atual</p>
                    <p className="text-sm text-muted-foreground">
                        {modelo.titulo}
                    </p>
                </div>

                <div className="space-y-2">
                    <label className="text-sm font-medium">
                        Novo título (opcional)
                    </label>

                    <Input
                        value={novoTitulo}
                        onChange={(e) => setNovoTitulo(e.target.value)}
                    />
                </div>
            </div>
            <DialogFooter>
                <Button
                    variant="outline"
                    onClick={onClose}
                >
                    Não
                </Button>
                <Button
                    onClick={clonarModelo}
                >
                    Sim, clonar
                </Button>
            </DialogFooter>
        </DialogContent>
    )
}
