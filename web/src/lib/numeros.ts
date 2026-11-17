/**
 * Normalização de valores numéricos digitados pelo usuário no padrão pt-BR.
 *
 * Campos de dinheiro/área aceitam os formatos abaixo e viram `number` confiável
 * — sem essa conversão, "1.500,00" quebra ao passar por `Number()` e o cadastro
 * parece "salvo" para o usuário, mas a validação nunca deixa o POST acontecer.
 *
 * Aceita:
 *  - "1500"        → 1500
 *  - "1500.50"     → 1500.5   (ponto como decimal, quando não for separador de milhar)
 *  - "1.500,50"    → 1500.5   (ponto de milhar + vírgula decimal)
 *  - "1.500"       → 1500     (ponto com 3 casas após = milhar)
 *  - "R$ 1.500,50" → 1500.5
 */
export function parseValorPtBr(input: string): number | null {
  if (!input) return null;

  const texto = input.replace(/R\$/gi, "").replace(/\s/g, "");
  if (!texto) return null;

  let normalizado = texto;
  const temVirgula = normalizado.includes(",");

  if (
    temVirgula &&
    !/^(?:\d+|\d{1,3}(?:\.\d{3})+),\d{1,2}$/.test(normalizado)
  ) {
    return null;
  }

  if (temVirgula) {
    // pt-BR explícito: ponto é milhar, vírgula é decimal
    normalizado = normalizado.replace(/\./g, "").replace(/,(?=\d*$)/, ".");
  }

  const partes = normalizado.split(".");
  if (!temVirgula && partes.length > 2) {
    // "1.234.567" → milhares sem decimal
    normalizado = partes.join("");
  } else if (!temVirgula && partes.length === 2 && partes[1].length === 3) {
    // "1.500" → por convenção pt-BR tratamos 3 casas após o ponto como milhar
    normalizado = partes.join("");
  }

  if (!/^\d+(\.\d+)?$/.test(normalizado)) return null;
  const valor = Number(normalizado);
  return Number.isFinite(valor) ? valor : null;
}
