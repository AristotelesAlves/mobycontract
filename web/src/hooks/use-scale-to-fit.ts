"use client";

import { useEffect, useRef, useState } from "react";

const ESCALA_MINIMA = 0.25;

export function useScaleToFit(
  larguraDesejadaPixels: number,
  folgaPixels = 24,
) {
  const referenciaDaArea = useRef<HTMLDivElement | null>(null);
  const [escala, definirEscala] = useState(1);

  useEffect(() => {
    const area = referenciaDaArea.current;
    if (!area) return;

    const medirEscala = () => {
      const larguraDisponivel = area.clientWidth - folgaPixels;
      definirEscala(
        Math.min(
          1,
          Math.max(ESCALA_MINIMA, larguraDisponivel / larguraDesejadaPixels),
        ),
      );
    };

    const observadorDaArea = new ResizeObserver(medirEscala);
    observadorDaArea.observe(area);
    medirEscala();

    return () => observadorDaArea.disconnect();
  }, [larguraDesejadaPixels, folgaPixels]);

  return { referenciaDaArea, escala };
}
