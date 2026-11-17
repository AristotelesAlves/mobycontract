import { downloadDocumentPdf } from "@/lib/download-document-pdf";
import { getVistoria, getVistoriaPhoto } from "./vistoria-service";
import { vistoriaReportHtml, type VistoriaReportPhoto } from "../use-cases/vistoria-report";

async function reportPhotoDataUrl(blob: Blob, characterBudget: number): Promise<string> {
  const bitmap = await createImageBitmap(blob);
  try {
    const canvas = document.createElement("canvas");
    const context = canvas.getContext("2d");
    if (!context) throw new Error("Não foi possível preparar as fotos para o relatório.");
    let scale = Math.min(1, 1280 / Math.max(bitmap.width, bitmap.height));
    let dataUrl: string;
    do {
      canvas.width = Math.max(1, Math.round(bitmap.width * scale));
      canvas.height = Math.max(1, Math.round(bitmap.height * scale));
      context.fillStyle = "#ffffff";
      context.fillRect(0, 0, canvas.width, canvas.height);
      context.drawImage(bitmap, 0, 0, canvas.width, canvas.height);
      dataUrl = canvas.toDataURL("image/jpeg", 0.75);
      scale *= 0.75;
    } while (dataUrl.length > characterBudget && Math.max(canvas.width, canvas.height) > 1);
    if (dataUrl.length > characterBudget) throw new Error("A quantidade de fotos excede o limite deste relatório.");
    return dataUrl;
  } finally {
    bitmap.close();
  }
}

export async function downloadVistoriaReport(contratoId: string, vistoriaId: string): Promise<void> {
  const vistoria = await getVistoria(contratoId, vistoriaId);
  const photos: VistoriaReportPhoto[] = [];
  const budget = Math.floor(1_200_000 / Math.max(1, vistoria.fotos.length));
  for (const photo of vistoria.fotos) {
    const blob = await getVistoriaPhoto(contratoId, vistoriaId, photo.id);
    photos.push({ id: photo.id, dataUrl: await reportPhotoDataUrl(blob, budget) });
  }
  await downloadDocumentPdf(vistoriaReportHtml(vistoria, photos), contratoId, `vistoria-${vistoria.numero || vistoria.id}`);
}
