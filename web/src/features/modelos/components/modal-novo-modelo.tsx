'use client'

import { useState } from "react"
import { useRouter } from "next/navigation"
import { Input } from "@/components/ui/input"
import { limitChars } from "@/lib/utils"
import { Button } from "@/components/ui/button"
import {
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle
} from "@/components/ui/dialog"
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from "@/components/ui/select"

export function ModalNovoModelo({
    onClose
}: {
    onClose: () => void
}) {
    const router = useRouter()
    const [titulo, setTitulo] = useState("")
    const [tipo, setTipo] = useState("")
    const [garantia, setGarantia] = useState("")
    const [descricao, setDescricao] = useState("")
    const [errors, setErrors] = useState<{ titulo?: string; tipo?: string }>({})

    function handleSubmit() {
        const newErrors: { titulo?: string; tipo?: string } = {}

        if (!titulo.trim()) {
            newErrors.titulo = "O título é obrigatório"
        }
        if (!tipo) {
            newErrors.tipo = "O tipo é obrigatório"
        }

        setErrors(newErrors)

        if (Object.keys(newErrors).length > 0) return

        let url = `/modelos/novo?titulo=${encodeURIComponent(titulo)}&tipo=${encodeURIComponent(tipo)}&descricao=${encodeURIComponent(descricao)}`
        if ((tipo === "Contrato" || tipo === "Aditivo") && garantia) {
            url += `&garantia=${encodeURIComponent(garantia)}`
        }

        router.push(url)
    }

    return (
        <DialogContent>
            <DialogHeader>
                <DialogTitle>Novo modelo</DialogTitle>
                <DialogDescription>
                    Preencha as informações para criar um novo modelo de documento.
                </DialogDescription>
            </DialogHeader>
            <div className="space-y-4 py-4">
                <div className="space-y-2">
                    <label className="text-sm font-medium">Título</label>
                    <Input
                        placeholder="Digite o título do modelo"
                        value={titulo}
                        onChange={(e) => {
                            setTitulo(e.target.value)
                            if (errors.titulo) setErrors((prev) => ({ ...prev, titulo: undefined }))
                        }}
                    />
                    {errors.titulo && (
                        <p className="text-xs text-destructive">{errors.titulo}</p>
                    )}
                </div>

                <div className="flex flex-col gap-4 w-full">
                    <div className="space-y-2 w-full">
                        <label className="text-sm font-medium">Tipo</label>
                        <Select
                            value={tipo}
                            onValueChange={(value) => {
                                if (value) setTipo(value)
                                if (value && value !== "Contrato" && value !== "Aditivo") {
                                    setGarantia("")
                                }
                                if (errors.tipo) setErrors((prev) => ({ ...prev, tipo: undefined }))
                            }}
                        >
                            <SelectTrigger className="w-full">
                                <SelectValue placeholder="Selecione um tipo" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="Contrato">Contrato</SelectItem>
                                <SelectItem value="Aditivo">Aditivo</SelectItem>
                                <SelectItem value="Distrato">Distrato</SelectItem>
                                <SelectItem value="Laudo">Laudo</SelectItem>
                            </SelectContent>
                        </Select>
                        {errors.tipo && (
                            <p className="text-xs text-destructive">{errors.tipo}</p>
                        )}
                    </div>

                    {(tipo === "Contrato" || tipo === "Aditivo") && (
                        <div className="space-y-2 w-full animate-in fade-in duration-200">
                            <label className="text-sm font-medium">Garantia Relacionada</label>
                            <Select
                                value={garantia}
                                onValueChange={(value) => {
                                    if (value) setGarantia(value)
                                }}
                            >
                                <SelectTrigger className="w-full">
                                    <SelectValue placeholder="Opcional" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="Caução">Caução em dinheiro ou imóvel</SelectItem>
                                    <SelectItem value="Fiador">Fiador</SelectItem>
                                    <SelectItem value="Seguro-fiança">Seguro-fiança locatícia</SelectItem>
                                    <SelectItem value="Título de capitalização">Título de capitalização</SelectItem>
                                    <SelectItem value="Sem garantia">Sem garantia (Art. 42)</SelectItem>
                                </SelectContent>
                            </Select>
                        </div>
                    )}
                </div>

                <div className="space-y-2">
                    <label className="text-sm font-medium">Descrição</label>
                    <textarea
                        className="flex min-h-[120px] w-full rounded-md border border-input bg-transparent px-3 py-2 text-sm shadow-sm placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring resize-none"
                        placeholder="Digite uma descrição para o modelo"
                        maxLength={200}
                        value={descricao}
                        onChange={(e) => setDescricao(limitChars(e.target.value, 200))}
                    />
                    <p className="text-xs text-muted-foreground text-right">
                        {descricao.length}/200 caracteres
                    </p>
                </div>
            </div>
            <DialogFooter>
                <Button variant="outline" onClick={onClose}>
                    Cancelar
                </Button>
                <Button onClick={handleSubmit}>
                    Criar modelo
                </Button>
            </DialogFooter>
        </DialogContent>
    )
}
