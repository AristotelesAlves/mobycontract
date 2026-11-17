"use client";

import React from "react";
import { TabsList, TabsTrigger } from "@/components/ui/tabs";
import { cn } from "@/lib/utils";

export interface PageTabsListProps extends React.ComponentProps<typeof TabsList> {
  actions?: React.ReactNode;
  containerClassName?: string;
}

export function PageTabsList({ className, containerClassName, actions, children, ...props }: PageTabsListProps) {
  return (
    <div className={cn("rounded-2xl border overflow-hidden border-border/70 bg-card/90 backdrop-blur-md p-2.5 shadow-sm flex flex-col lg:flex-row lg:items-center justify-between gap-3 w-full", containerClassName)}>
      <TabsList className={cn("bg-transparent p-0 overflow-hidden border-0 flex items-center gap-2 h-auto w-full lg:w-auto overflow-x-auto custom-scrollbar-hide", className)} {...props}>
        {children}
      </TabsList>
      {actions && (
        <div className="flex items-center gap-2.5 flex-wrap justify-end">
          {actions}
        </div>
      )}
    </div>
  );
}

export function PageTabsTrigger({ className, children, ...props }: React.ComponentProps<typeof TabsTrigger>) {
  return (
    <TabsTrigger
      className={cn(
        "flex-1 lg:flex-initial rounded-full px-4 py-2 text-xs font-medium transition-all text-muted-foreground hover:text-foreground hover:bg-muted/40 border border-border/40 bg-muted/20 data-active:border-emerald-500/50 data-active:bg-emerald-500/10 data-active:text-emerald-400 data-active:font-semibold data-active:shadow-xs flex items-center justify-center gap-2 cursor-pointer",
        className
      )}
      {...props}
    >
      {children}
    </TabsTrigger>
  );
}
