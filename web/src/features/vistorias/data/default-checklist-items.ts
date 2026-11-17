import type { VistoriaItem } from "./vistoria-types";
// Cada aplicação cria uma lista independente para edição no formulário.
export function itensPadrao(): VistoriaItem[] {
    return ["Sala", "Cozinha", "Banheiro", "Quarto"].flatMap((ambiente) => ["Paredes", "Piso", "Portas", "Janelas", "Instalações elétricas"].map((item) => ({ ambiente, item, estado: "Bom" })));
}
