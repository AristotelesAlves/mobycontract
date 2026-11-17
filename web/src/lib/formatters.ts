export function formatCurrency(valor: number): string {
  return valor.toLocaleString("pt-BR", {
    style: "currency",
    currency: "BRL",
  });
}

export function formatDate(dataISO: string): string {
  if (!dataISO) return "—";
  const partes = dataISO.split("-");
  if (partes.length !== 3) return dataISO;
  return `${partes[2]}/${partes[1]}/${partes[0]}`;
}

export function booleanToText(valor: boolean): string {
  return valor ? "Sim" : "Não";
}

export function formatTaxId(v: string) {
  v = v.replace(/\D/g, "").slice(0, 14);
  if (v.length <= 11) {
    v = v.replace(/(\d{3})(\d)/, "$1.$2");
    v = v.replace(/(\d{3})(\d)/, "$1.$2");
    v = v.replace(/(\d{3})(\d{1,2})$/, "$1-$2");
  } else {
    v = v.replace(/^(\d{2})(\d)/, "$1.$2");
    v = v.replace(/^(\d{2})\.(\d{3})(\d)/, "$1.$2.$3");
    v = v.replace(/\.(\d{3})(\d)/, ".$1/$2");
    v = v.replace(/(\d{4})(\d)/, "$1-$2");
  }
  return v;
}

export function formatPostalCode(v: string) {
  v = v.replace(/\D/g, "").slice(0, 8);
  v = v.replace(/^(\d{5})(\d)/, "$1-$2");
  return v;
}
export function formatPhone(valor: string): string {
  const digitos = valor.replace(/\D/g, "");
  if (valor.trim().startsWith("+") || digitos.length > 11) return valor.slice(0, 30);
  if (digitos.length < 3) return digitos;
  const local = digitos.slice(2);
  const tamanhoPrefixo = local.length > 8 ? 5 : 4;
  return `(${digitos.slice(0, 2)}) ${local.slice(0, tamanhoPrefixo)}${local.length > tamanhoPrefixo ? `-${local.slice(tamanhoPrefixo)}` : ""}`;
}
