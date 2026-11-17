"use client";

import React from "react";
import {
  Pagination,
  PaginationContent,
  PaginationEllipsis,
  PaginationItem,
  PaginationLink,
  PaginationNext,
  PaginationPrevious,
} from "@/components/ui/pagination";

export interface TablePaginationProps {
  currentPage: number;
  totalItems: number;
  pageSize: number;
  onPageChange: (page: number) => void;
  className?: string;
  itemLabel?: string;
}

export function TablePagination({
  currentPage,
  totalItems,
  pageSize,
  onPageChange,
  className,
  itemLabel = "registros",
}: TablePaginationProps) {
  const totalPages = Math.max(1, Math.ceil(totalItems / pageSize));
  const safePage = Math.min(Math.max(1, currentPage), totalPages);
  const startItem = totalItems === 0 ? 0 : (safePage - 1) * pageSize + 1;
  const endItem = Math.min(safePage * pageSize, totalItems);

  function handlePageChange(newPage: number) {
    if (newPage >= 1 && newPage <= totalPages && newPage !== safePage) {
      onPageChange(newPage);
    }
  }

  const renderPageItems = () => {
    if (totalPages <= 5) {
      return Array.from({ length: totalPages }, (_, i) => i + 1).map((p) => (
        <PaginationItem key={p}>
          <PaginationLink
            href="#"
            isActive={p === safePage}
            onClick={(e) => {
              e.preventDefault();
              handlePageChange(p);
            }}
            className="cursor-pointer"
          >
            {p}
          </PaginationLink>
        </PaginationItem>
      ));
    }

    const items = [];
    items.push(
      <PaginationItem key={1}>
        <PaginationLink
          href="#"
          isActive={safePage === 1}
          onClick={(e) => {
            e.preventDefault();
            handlePageChange(1);
          }}
          className="cursor-pointer"
        >
          1
        </PaginationLink>
      </PaginationItem>
    );

    if (safePage > 3) {
      items.push(
        <PaginationItem key="ellipsis-start">
          <PaginationEllipsis />
        </PaginationItem>
      );
    }

    const start = Math.max(2, safePage - 1);
    const end = Math.min(totalPages - 1, safePage + 1);

    for (let p = start; p <= end; p++) {
      items.push(
        <PaginationItem key={p}>
          <PaginationLink
            href="#"
            isActive={p === safePage}
            onClick={(e) => {
              e.preventDefault();
              handlePageChange(p);
            }}
            className="cursor-pointer"
          >
            {p}
          </PaginationLink>
        </PaginationItem>
      );
    }

    if (safePage < totalPages - 2) {
      items.push(
        <PaginationItem key="ellipsis-end">
          <PaginationEllipsis />
        </PaginationItem>
      );
    }

    items.push(
      <PaginationItem key={totalPages}>
        <PaginationLink
          href="#"
          isActive={safePage === totalPages}
          onClick={(e) => {
            e.preventDefault();
            handlePageChange(totalPages);
          }}
          className="cursor-pointer"
        >
          {totalPages}
        </PaginationLink>
      </PaginationItem>
    );

    return items;
  };

  const labelText =
    totalItems === 0
      ? `Nenhum ${itemLabel}`
      : totalItems <= pageSize
      ? `Mostrando ${totalItems} de ${totalItems} ${itemLabel}`
      : `Mostrando ${startItem} a ${endItem} de ${totalItems} ${itemLabel}`;

  return (
    <div
      className={`flex flex-col sm:flex-row items-center justify-between gap-4 w-full ${className ?? ""}`}
    >
      <p className="text-sm text-muted-foreground whitespace-nowrap">
        {labelText}
      </p>

      <Pagination className="mx-0 w-auto justify-end">
        <PaginationContent>
          <PaginationItem>
            <PaginationPrevious
              href="#"
              onClick={(e) => {
                e.preventDefault();
                handlePageChange(safePage - 1);
              }}
              className={
                safePage <= 1
                  ? "pointer-events-none opacity-40 cursor-not-allowed"
                  : "cursor-pointer"
              }
              aria-disabled={safePage <= 1}
            />
          </PaginationItem>

          {renderPageItems()}

          <PaginationItem>
            <PaginationNext
              href="#"
              onClick={(e) => {
                e.preventDefault();
                handlePageChange(safePage + 1);
              }}
              className={
                safePage >= totalPages
                  ? "pointer-events-none opacity-40 cursor-not-allowed"
                  : "cursor-pointer"
              }
              aria-disabled={safePage >= totalPages}
            />
          </PaginationItem>
        </PaginationContent>
      </Pagination>
    </div>
  );
}
