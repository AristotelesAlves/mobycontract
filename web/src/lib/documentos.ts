export function cpfValido(valor: string): boolean {
  const cpf = valor.replace(/\D/g, "");
  if (cpf.length !== 11 || /^(\d)\1+$/.test(cpf)) return false;
  for (const quantidade of [9, 10]) {
    let soma = 0;
    for (let posicao = 0; posicao < quantidade; posicao++) soma += Number(cpf[posicao]) * (quantidade + 1 - posicao);
    const resto = soma % 11;
    if (Number(cpf[quantidade]) !== (resto < 2 ? 0 : 11 - resto)) return false;
  }
  return true;
}

export function dataIsoValida(valor: string): boolean {
  if (!/^\d{4}-\d{2}-\d{2}$/.test(valor)) return false;
  const data = new Date(`${valor}T00:00:00Z`);
  return !Number.isNaN(data.getTime()) && data.toISOString().slice(0, 10) === valor;
}
