"use client"

import Image from "next/image"
import { SidebarMenu, SidebarMenuItem } from "@/components/ui/sidebar"

export function TeamSwitcher({
  teams,
}: {
  teams: {
    name: string
    logoUrl: string
    plan: string
  }
}) {
  const activeTeam = teams

  if (!activeTeam) {
    return null
  }
  return (
    <SidebarMenu>
      <SidebarMenuItem>
        <div className="flex gap-2">  
          <div className="flex aspect-square size-8 items-center justify-center rounded-lg">
            <Image width={32} height={32} src={activeTeam.logoUrl} alt={activeTeam.name} className="aspect-square size-full rounded-lg" />
          </div>
          <div className="grid flex-1 text-left text-sm leading-tight">
            <span className="truncate font-medium">{activeTeam.name}</span>
            <span className="truncate text-xs">{activeTeam.plan}</span>
          </div>
        </div>
      </SidebarMenuItem>
    </SidebarMenu>
  )
}
