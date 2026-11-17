"use client";

import { Suspense, useEffect, useState } from "react";
import { useRouter, useSearchParams } from "next/navigation";
import { FloppyDisk } from "@phosphor-icons/react/dist/ssr";
import { Button } from "@/components/ui/button";
import { Card, CardContent, CardHeader } from "@/components/ui/card";
import { useDocumentEditor } from "@/lib/tiptap/editor/use-document-editor";
import {
  DOCUMENT_DEFAULT_TYPOGRAPHY,
  normalizeDocumentTypography,
  type DocumentTypography,
} from "@/lib/tiptap/document-style";
import {
  DOCUMENT_DEFAULT_MARGINS,
  normalizeDocumentMargins,
  type DocumentMargins,
} from "@/lib/tiptap/document-geometry";
import { documentVariableCatalogPaths } from "@/lib/tiptap/document-variables";
import { DocumentToolbar } from "../components/documento/document-toolbar";
import { DocumentSettingsDialog } from "../components/documento/document-settings-dialog";
import {
  DocumentSaveTemplateDialog,
  type DocumentSaveData,
} from "../components/documento/document-save-template-dialog";
import { DocumentSurface } from "../components/documento/document-surface";
import { createModelTemplate, getModelTemplate, updateModelTemplate, type CriarModeloPayload } from "../services/modelos-service";
import type { IModeloClonar } from "../data/modelo-types";
import { getReadableErrorMessage } from "@/services/api/get-readable-error-message";
import {
  DOCUMENTO_VARIAVEIS_CATALOGO,
  DOCUMENTO_VARIAVEIS_DE_EXEMPLO,
} from "../data/document-variables-catalog";

const DOCUMENTO_TITULO_PADRAO = "Modelo de documento";

const DOCUMENTO_CAMINHOS_DE_VARIAVEIS_CONHECIDAS =
  documentVariableCatalogPaths(DOCUMENTO_VARIAVEIS_CATALOGO);

const DOCUMENTO_CONTEUDO_INICIAL = `
<p></p>
`;

const DOCUMENTO_TIPOS_VALIDOS = ["Contrato", "Aditivo", "Distrato"];

function ModeloEditor({ modelo }: { modelo?: IModeloClonar }) {
  const somenteLeitura = modelo?.podeAlterar === false;
  const parametrosDeBusca = useSearchParams();
  const router = useRouter();

  const tituloInicial =
    modelo?.titulo ?? parametrosDeBusca.get("titulo") ?? DOCUMENTO_TITULO_PADRAO;
  const conteudoHtmlInicial =
    modelo?.conteudoHtml ?? (parametrosDeBusca.get("conteudoHtml") || parametrosDeBusca.get("htmlContent") || DOCUMENTO_CONTEUDO_INICIAL);
  const initialVersionDeBusca = Number(parametrosDeBusca.get("versao") ?? 0);
  const initialVersion =
    modelo?.versao ?? (initialVersionDeBusca > 0 ? initialVersionDeBusca : 1);
  const tipoDeBusca = modelo?.tipo ?? parametrosDeBusca.get("tipo") ?? "";
  const initialType = DOCUMENTO_TIPOS_VALIDOS.includes(tipoDeBusca)
    ? tipoDeBusca
    : "";
  const initialGuarantee = modelo?.garantia ?? parametrosDeBusca.get("garantia") ?? "";

  const [documentMargins, setDocumentMargins] =
    useState<DocumentMargins>(() => normalizeDocumentMargins(modelo?.margens ?? DOCUMENT_DEFAULT_MARGINS));
  const [typography, setTypography] =
    useState<DocumentTypography>(() => normalizeDocumentTypography(modelo?.tipografia ?? DOCUMENT_DEFAULT_TYPOGRAPHY));
  const [dialogoDeSalvarAberto, definirDialogoDeSalvarAberto] =
    useState(false);

  const {
    editorInstance,
    geometry,
    pageCount,
  } = useDocumentEditor({
    initialContent: conteudoHtmlInicial,
    margins: documentMargins,
    typography: typography,
    knownVariablePaths: DOCUMENTO_CAMINHOS_DE_VARIAVEIS_CONHECIDAS,
    variableCatalog: DOCUMENTO_VARIAVEIS_CATALOGO,
  });
  useEffect(() => {
    editorInstance?.setEditable(!somenteLeitura);
  }, [editorInstance, somenteLeitura]);

  function aplicarConfiguracoesDoDocumento(
    margins: DocumentMargins,
    typography: DocumentTypography,
  ) {
    setDocumentMargins(margins);
    setTypography(typography);
  }

  function salvarModelo() {
    definirDialogoDeSalvarAberto(true);
  }

  async function confirmarSalvamentoDoModelo(
    data: DocumentSaveData,
  ): Promise<boolean> {
    const payload: CriarModeloPayload = {
      titulo: data.title,
      tipo: data.tipo,
      versao: data.versao,
      conteudoHtml: data.htmlContent,
      garantia: data.garantia,
      margens: {
        superior: geometry.margins.top,
        direita: geometry.margins.right,
        inferior: geometry.margins.bottom,
        esquerda: geometry.margins.left,
      },
      tipografia: {
        corpo: typography.bodyFontSizePoints,
        entrelinha: typography.lineHeight,
      },
    };
    if (modelo) await updateModelTemplate(modelo.uuid, payload);
    else await createModelTemplate(payload);

    definirDialogoDeSalvarAberto(false);
    router.push("/modelos");
    router.refresh();
    return true;
  }

  return (
    <div className="flex h-full min-h-0 w-full flex-col gap-3 pb-2">
      {somenteLeitura && <p role="status" className="rounded-lg border bg-muted/30 px-4 py-3 text-sm">Somente leitura: este modelo está vinculado a um contrato. Para alterar, clone uma nova versão na lista de modelos.</p>}
      <Card className="w-full shrink-0">
        <CardHeader className="py-3 border-b bg-muted/20">
          <div className="flex flex-col">
            <span className="text-xs text-muted-foreground uppercase tracking-wider font-semibold">
              {initialType || "Novo Modelo"} {initialVersion > 0 ? `• v${initialVersion}` : ""}
            </span>
            <h1 className="text-lg font-bold mt-1">{tituloInicial}</h1>
            {initialGuarantee && (
              <span className="text-sm text-muted-foreground mt-1">
                Garantia relacionada: <span className="font-medium text-foreground">{initialGuarantee}</span>
              </span>
            )}
          </div>
        </CardHeader>
        <CardContent className="flex flex-wrap items-center justify-between gap-3 pt-3">
          {!somenteLeitura && <DocumentToolbar
            editorInstance={editorInstance}
            typography={typography}
            variableCatalog={DOCUMENTO_VARIAVEIS_CATALOGO}
          />}

          <div className="flex flex-wrap items-center gap-2">
            <span className="text-muted-foreground hidden text-sm lg:inline">
              {pageCount}{" "}
              {pageCount === 1 ? "página" : "páginas"}
            </span>

            {!somenteLeitura && <Button onClick={salvarModelo}>
              <FloppyDisk size={20} />
              <span className="hidden sm:inline">Salvar modelo</span>
            </Button>}

            {!somenteLeitura && <DocumentSaveTemplateDialog
              open={dialogoDeSalvarAberto}
              onOpenChange={definirDialogoDeSalvarAberto}
              seed={{
                title: tituloInicial,
                type: initialType,
                version: initialVersion,
                guarantee: initialGuarantee,
              }}
              geometry={geometry}
              typography={typography}
              pageCount={pageCount}
              sampleValues={DOCUMENTO_VARIAVEIS_DE_EXEMPLO}
              getHtmlContent={() =>
                editorInstance?.view.dom.innerHTML ?? ""
              }
              onSave={confirmarSalvamentoDoModelo}
            />}

            {!somenteLeitura && <DocumentSettingsDialog
              margins={documentMargins}
              typography={typography}
              onApplySettings={aplicarConfiguracoesDoDocumento}
            />}
          </div>
        </CardContent>
      </Card>

      <Card className="min-h-0 flex-1 overflow-hidden p-0">
        <DocumentSurface
          editorInstance={editorInstance}
          geometry={geometry}
          typography={typography}
          pageCount={pageCount}
        />
      </Card>
    </div>
  );
}

function NovoModeloPage() {
  const params = useSearchParams();
  const id = params.get("uuid");
  const [resultado, setResultado] = useState<{ id: string; modelo?: IModeloClonar; erro?: string } | null>(null);
  const [tentativa, setTentativa] = useState(0);
  useEffect(() => {
    if (!id) return;
    let cancelado = false;
    setResultado(null);
    getModelTemplate(id).then((modelo) => {
      if (!cancelado) setResultado({ id, modelo });
    }).catch((erro) => {
      if (!cancelado) setResultado({ id, erro: getReadableErrorMessage(erro) });
    });
    return () => { cancelado = true; };
  }, [id, tentativa]);

  if (!id) return <ModeloEditor key={params.toString()} />;
  if (resultado?.id === id && resultado.erro) return <div role="alert" className="space-y-3"><p className="text-destructive">{resultado.erro}</p><Button onClick={() => setTentativa((value) => value + 1)}>Tentar novamente</Button></div>;
  if (resultado?.id !== id || !resultado.modelo) return <p role="status" className="text-sm text-muted-foreground">Carregando modelo salvo...</p>;
  return <ModeloEditor key={id} modelo={resultado.modelo} />;
}

export default function PaginaNovoModelo() {
  return (
    <Suspense fallback={null}>
      <NovoModeloPage />
    </Suspense>
  );
}
