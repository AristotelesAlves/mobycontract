"use client"

import { Fragment } from "react"
import { useSelectedLayoutSegments } from "next/navigation"
import type { ReactNode } from "react"
import { AppSidebar } from "@/components/layout/app-sidebar"
import { ModeToggle } from "@/components/ui/mode-toggle"
import { Separator } from "@/components/ui/separator"
import {
  SidebarInset,
  SidebarProvider,
  SidebarTrigger,
} from "@/components/ui/sidebar"
import {
  Breadcrumb,
  BreadcrumbItem,
  BreadcrumbLink,
  BreadcrumbList,
  BreadcrumbPage,
  BreadcrumbSeparator,
} from "@/components/ui/breadcrumb"
import { ProtectedRoute } from "@/components/auth/protected-route"
import { FeedbackProvider } from "@/components/ui/feedback-provider"

interface Crumb {
  label: string
  href: string
}

const breadcrumbLabels: Record<string, string> = {
  dashboard: "Dashboard Imobiliário",
  modelos: "Modelos",
  novo: "Novo",
  imoveis: "Imóveis",
  inquilinos: "Inquilinos",
  contratos: "Contratos",
  auditoria: "Auditoria",
}

function capitalize(segment: string) {
  return segment.charAt(0).toUpperCase() + segment.slice(1)
}

function AppLayoutContent({ children }: { children: ReactNode }) {
  const segments = useSelectedLayoutSegments().filter(
    (segment) => !segment.startsWith("("),
  )
  const breadcrumbs: Crumb[] = segments.map((segment, index) => ({
    label: breadcrumbLabels[segment] ?? capitalize(segment),
    href: `/${segments.slice(0, index + 1).join("/")}`,
  }))

  return (
    <SidebarProvider>
      <AppSidebar />
      <SidebarInset className="max-h-screen overflow-auto">
        <header className="flex justify-between h-16 w-full shrink-0 items-center gap-2 px-4 transition-[width,height] ease-linear group-has-data-[collapsible=icon]/sidebar-wrapper:h-12 z-50 bg-background border-b">
          <div className="flex items-center gap-2 px-4">
            <SidebarTrigger className="-ml-1" />
            <Separator
              orientation="vertical"
              className="mr-2 data-[orientation=vertical]:h-4"
            />
            <Breadcrumb>
              <BreadcrumbList>
                {breadcrumbs.map((crumb, index) => {
                  const isLast = index === breadcrumbs.length - 1
                  return (
                    <Fragment key={crumb.label}>
                      {index > 0 && (
                        <BreadcrumbSeparator className="hidden md:block" />
                      )}
                      <BreadcrumbItem
                        className={isLast ? undefined : "hidden md:block"}
                      >
                        {isLast ? (
                          <BreadcrumbPage>{crumb.label}</BreadcrumbPage>
                        ) : (
                          <BreadcrumbLink href={crumb.href}>
                            {crumb.label}
                          </BreadcrumbLink>
                        )}
                      </BreadcrumbItem>
                    </Fragment>
                  )
                })}
              </BreadcrumbList>
            </Breadcrumb>
          </div>
          <div className="flex items-center px-4">
            <ModeToggle />
          </div>
        </header>
        <div className="flex flex-1 flex-col gap-4 px-4 pt-2 h-full overflow-y-auto!">
          {children}
        </div>
      </SidebarInset>
    </SidebarProvider>
  )
}

export default function AppLayout({ children }: { children: ReactNode }) {
  return (
    <ProtectedRoute>
      <FeedbackProvider>
        <AppLayoutContent>{children}</AppLayoutContent>
      </FeedbackProvider>
    </ProtectedRoute>
  )
}
