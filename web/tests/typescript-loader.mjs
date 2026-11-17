import { readFile } from 'node:fs/promises';
import { existsSync } from 'node:fs';
import { fileURLToPath, pathToFileURL } from 'node:url';
import ts from 'typescript';

const raiz = new URL('../src/', import.meta.url);
export async function resolve(specifier, context, nextResolve) {
  const url = specifier.startsWith('@/') ?
    new URL(specifier.slice(2), raiz) :
    specifier.startsWith('.') ?
      new URL(specifier, context.parentURL) :
      null;

  for (const sufixo of url ? ['', '.ts', '/index.ts'] : []) {
    const candidato = fileURLToPath(url) + sufixo;
    if (existsSync(candidato) && candidato.endsWith('.ts')) {
      return { url: pathToFileURL(candidato).href, shortCircuit: true };
    }
  }
  return nextResolve(specifier, context);
}
export async function load(url, context, nextLoad) {
  if (url.endsWith('.ts')) {
    const source = await readFile(new URL(url), 'utf8');
    return { format: 'module', source: ts.transpileModule(source, { compilerOptions: { module: ts.ModuleKind.ESNext, target: ts.ScriptTarget.ES2022 } }).outputText, shortCircuit: true };
  }
  return nextLoad(url, context);
}
