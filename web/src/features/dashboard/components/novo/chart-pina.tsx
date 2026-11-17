"use client"

import * as React from "react"
import { Label, Pie, PieChart, ResponsiveContainer } from "recharts"

import {
    ChartContainer,
    ChartTooltip,
    ChartTooltipContent,
    type ChartConfig,
} from "@/components/ui/chart"

export type ChartPieData = {
    contrato: string;
    quantidade: number;
    fill: string;
}

interface ChartPieDonutTextProps {
    data: ChartPieData[];
}
const chartConfig = {
    quantidade: {
        label: "Total",
    },
    alugados: {
        label: "Imóveis Alugados",
        color: "#10B981",
    },
    vagos: {
        label: "Imóveis Vagos",
        color: "#F59E0B",
    },
    encerrados: {
        label: "Contratos Encerrados",
        color: "#EF4444",
    },
    negociacao: {
        label: "Em Negociação",
        color: "#8B5CF6",
    },
} satisfies ChartConfig

export function ChartPieDonutText({ data }: ChartPieDonutTextProps) {
    const totalContratos = React.useMemo(() => {
        return data.reduce((acc, item) => acc + item.quantidade, 0)
    }, [data])

    return (
        <div className="w-full h-full flex items-center justify-center px-6 pb-6">
            <div className="flex flex-col sm:flex-row items-center justify-center gap-8 w-full">
                <div className="w-[180px] h-[180px] relative flex-shrink-0">
                    <ChartContainer
                        config={chartConfig}
                        className="w-full h-full absolute inset-0"
                    >
                        <ResponsiveContainer width="100%" height="100%">
                            <PieChart>
                                <ChartTooltip
                                    cursor={false}
                                    content={
                                        <ChartTooltipContent
                                            indicator="dot"
                                            hideIndicator={false}
                                            hideLabel
                                        />
                                    }
                                />

                                <Pie
                                    data={data}
                                    dataKey="quantidade"
                                    nameKey="contrato"
                                    innerRadius={55}
                                    outerRadius={75}
                                    paddingAngle={3}
                                    cornerRadius={6}
                                    stroke="transparent"
                                    strokeWidth={0}
                                >
                                    <Label
                                        content={({ viewBox }) => {
                                            if (!viewBox || !("cx" in viewBox) || !("cy" in viewBox))
                                                return null

                                            return (
                                                <text
                                                    x={viewBox.cx}
                                                    y={viewBox.cy}
                                                    textAnchor="middle"
                                                    dominantBaseline="middle"
                                                >
                                                    <tspan
                                                        x={viewBox.cx}
                                                        y={viewBox.cy - 6}
                                                        className="fill-foreground text-3xl font-bold"
                                                    >
                                                        {totalContratos}
                                                    </tspan>

                                                    <tspan
                                                        x={viewBox.cx}
                                                        y={viewBox.cy + 22}
                                                        className="fill-muted-foreground text-xs"
                                                    >
                                                        Total
                                                    </tspan>
                                                </text>
                                            )
                                        }}
                                    />
                                </Pie>
                            </PieChart>
                        </ResponsiveContainer>
                    </ChartContainer>
                </div>
                
                {/* Side Legend */}
                <div className="flex flex-col gap-4 w-full sm:w-auto min-w-[150px]">
                    {data.map((item) => (
                        <div key={item.contrato} className="flex items-center justify-between gap-4">
                            <div className="flex items-center gap-2">
                                <span 
                                    className="w-3 h-3 rounded-full" 
                                    style={{ backgroundColor: item.fill }} 
                                />
                                <span className="text-sm font-medium text-muted-foreground">
                                    {item.contrato}
                                </span>
                            </div>
                            <span className="text-sm font-bold text-foreground">
                                {item.quantidade}
                            </span>
                        </div>
                    ))}
                </div>
            </div>
        </div>
    )
}
