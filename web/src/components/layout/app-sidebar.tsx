"use client"

import * as React from "react"

import { NavMain } from "./nav-main"
import { NavUser } from "./nav-user"
import { TeamSwitcher } from "./team-switcher"
import {
  Sidebar,
  SidebarContent,
  SidebarFooter,
  SidebarHeader,
  SidebarRail,
} from "@/components/ui/sidebar"
import { TerminalIcon, RobotIcon, BookOpenIcon, MapTrifoldIcon, CameraIcon } from "@phosphor-icons/react"

const data = {
  teams: {
    name: "MobiContract",
    logoUrl: "/logo/mobiContract.svg",
    plan: "O ALUGUEL DO FUTURO",
  },

  navMain: [
    {
      title: "Dashboard",
      url: "#",
      icon: <TerminalIcon />,
    },
    {
      title: "Imóveis",
      url: "#",
      icon: <MapTrifoldIcon />,
      items: [
        { title: "Listar Imóveis", url: "/imoveis" },
        { title: "Cadastrar Imóvel", url: "/imoveis/novo" },
      ],
    },
    {
      title: "Inquilinos",
      url: "#",
      icon: <RobotIcon />,
      items: [
        { title: "Listar Inquilinos", url: "/inquilinos" },
        { title: "Cadastrar Inquilino", url: "/inquilinos/novo" },
      ],
    },
    {
      title: "Contratos",
      url: "#",
      icon: <BookOpenIcon />,
      items: [
        { title: "Listar Contratos", url: "/contratos" },
        { title: "Criar Contrato", url: "/contratos/novo" },
        { title: "Verificar Contrato", url: "/contratos/verificar" },
        { title: "Modelos de Contrato", url: "/modelos" },
      ],
    },
    {
      title: "Vistorias",
      url: "#",
      icon: <CameraIcon />,
      items: [
        { title: "Central de Vistorias", url: "/vistorias" },
      ],
    },
  ],
}

export function AppSidebar({ ...props }: React.ComponentProps<typeof Sidebar>) {
  return (
    <Sidebar collapsible="icon" {...props}>
      <SidebarHeader>
        <TeamSwitcher teams={data.teams} />
      </SidebarHeader>
      <SidebarContent>
        <NavMain items={data.navMain} />
      </SidebarContent>
      <SidebarFooter>
        <NavUser />
      </SidebarFooter>
      <SidebarRail />
    </Sidebar>
  )
}
