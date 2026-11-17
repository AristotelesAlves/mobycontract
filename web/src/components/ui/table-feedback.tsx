"use client";

import {
  ArrowClockwise,
  MagnifyingGlass,
  WarningCircle,
} from "@phosphor-icons/react";
import { Button } from "@/components/ui/button";
import { Skeleton } from "@/components/ui/skeleton";
import { TableCell, TableRow } from "@/components/ui/table";

export function TableLoadingRows({
  rows = 5,
}: {
  rows?: number;
}) {
  return (
    <>
      {Array.from({ length: rows }, (_, rowIndex) => (
        <TableRow key={rowIndex}>
          {Array.from({ length: 5 }, (_, columnIndex) => (
            <TableCell key={columnIndex} className="py-4">
              <Skeleton className="h-4 w-full max-w-[160px]" />
            </TableCell>
          ))}
        </TableRow>
      ))}
    </>
  );
}

export function ListErrorState({
  message,
  onRetry,
}: {
  message: string;
  onRetry: () => void;
}) {
  return (
    <TableRow>
      <TableCell colSpan={7} className="py-10">
        <div className="flex flex-col items-center gap-3 text-center">
          <WarningCircle size={28} className="text-destructive" />
          <p className="text-sm text-destructive">{message}</p>
          <Button variant="outline" size="sm" className="gap-2" onClick={onRetry}>
            <ArrowClockwise size={14} />
            Tentar novamente
          </Button>
        </div>
      </TableCell>
    </TableRow>
  );
}

export function ListEmptyState({ message }: { message: string }) {
  return (
    <TableRow>
      <TableCell colSpan={7} className="py-10 text-center text-muted-foreground">
        <div className="flex flex-col items-center gap-2">
          <MagnifyingGlass size={24} className="opacity-50" />
          <p className="text-sm">{message}</p>
        </div>
      </TableCell>
    </TableRow>
  );
}
