"use client";

import {
  SealCheck,
  Warning,
  WarningCircle,
} from "@phosphor-icons/react";
import { Button } from "@/components/ui/button";
import {
  Dialog,
  DialogContent,
  DialogDescription,
  DialogFooter,
  DialogHeader,
  DialogTitle,
} from "@/components/ui/dialog";

interface IModalConfirmarBlockchainProps {
  aberto: boolean;
  aoFechar: () => void;
  aoConfirmar: () => void;
}

export function ModalConfirmarBlockchain({
  aberto,
  aoFechar,
  aoConfirmar,
}: IModalConfirmarBlockchainProps) {
  return (
    <Dialog open={aberto} onOpenChange={(open) => !open && aoFechar()}>
      <DialogContent className="max-w-md">
        <DialogHeader>
          <DialogTitle className="flex items-center gap-2">
            <div className="flex h-10 w-10 items-center justify-center rounded-full bg-destructive/10">
              <Warning size={20} className="text-destructive" />
            </div>
            Registrar na blockchain
          </DialogTitle>
          <DialogDescription>
            Esta ação é <strong className="text-foreground">irreversível</strong>.
          </DialogDescription>
        </DialogHeader>

        <div className="space-y-3 text-sm">
          <p>
            Ao registrar o smart contract na blockchain, os dados ficarão
            gravados de forma <strong className="text-foreground">imutável</strong> e
            não poderá ser alterado ou removido.
          </p>
          <div className="rounded-md border border-destructive/20 bg-destructive/5 p-3">
            <div className="flex items-start gap-2">
              <WarningCircle size={16} className="mt-0.5 shrink-0 text-destructive" />
              <div className="space-y-1 text-xs">
                <p className="font-medium text-destructive">Atenção:</p>
                <ul className="text-muted-foreground list-inside list-disc space-y-0.5">
                  <li>O hash do arquivo assinado será registrado permanentemente</li>
                  <li>Não será possível desfazer esta operação</li>
                  <li>Qualquer pessoa poderá verificar o registro pelo hash</li>
                </ul>
              </div>
            </div>
          </div>
          <p className="text-muted-foreground text-xs">
            Certifique-se de que todos os dados do contrato estão corretos antes
            de prosseguir.
          </p>
        </div>

        <DialogFooter>
          <Button variant="outline" onClick={aoFechar}>
            Cancelar
          </Button>
          <Button variant="destructive" onClick={aoConfirmar}>
            <SealCheck size={16} />
            Confirmar registro
          </Button>
        </DialogFooter>
      </DialogContent>
    </Dialog>
  );
}
