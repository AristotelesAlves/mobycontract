import test from 'node:test';
import assert from 'node:assert/strict';
import { cpfValido, dataIsoValida } from '../src/lib/documentos.ts';
import { parseValorPtBr } from '../src/lib/numeros.ts';
import { gerarHtmlContrato } from '../src/features/contratos/use-cases/generate-contract-html.ts';
import { validateContractDraft } from '../src/features/contratos/use-cases/validate-contract-draft.ts';

test('CPF valida dígitos e rejeita sequências repetidas', () => {
  assert.equal(cpfValido('529.982.247-25'), true);
  assert.equal(cpfValido('52998224724'), false);
  assert.equal(cpfValido('11111111111'), false);
});
test('datas não aceitam normalização de dia inexistente', () => {
  assert.equal(dataIsoValida('2024-02-29'), true);
  assert.equal(dataIsoValida('2026-02-29'), false);
  assert.equal(dataIsoValida('2026-04-31'), false);
});
test('moeda preserva centavos e rejeita pontuação inválida', () => {
  assert.equal(parseValorPtBr('R$ 1.500,25'), 1500.25);
  assert.equal(parseValorPtBr('1500.25'), 1500.25);
  assert.equal(parseValorPtBr('1.5,25'), null);
  assert.equal(parseValorPtBr('-1'), null);
});
test('HTML exportado usa snapshot e escapa dados pessoais', () => {
  const contrato = {
    numero: '2026/6001', valorMensal: 1500, dataInicio: '2026-10-20', dataFim: '', inquilinoNome: 'Histórico',
    snapshot: { versao: 1, capturadoEm: '2026-10-20T00:00:00Z', imovel: { titulo: 'Imóvel histórico', valor_aluguel: 1500 }, modelo: { conteudo_html: '<p>{{cliente.nome}} {{cliente.cpf}} {{contrato.valorMensal}}</p>' }, inquilino: { nome: '<script>roubar()</script>', cpf: '52998224725' } },
  };
  const html = gerarHtmlContrato(contrato, { conteudoHtml: '<p>Modelo alterado</p>' });
  assert.ok(html.startsWith('<!doctype html>'));
  assert.ok(html.includes('52998224725'));
  assert.ok(html.includes('&lt;script&gt;'));
  assert.ok(!html.includes('<script>roubar'));
  assert.ok(!html.includes('Modelo alterado'));
  assert.ok(html.includes('Content-Security-Policy'));
});
test('modelo sem garantia dispensa caução e comprovante', () => {
  const draft = { partyMode: 'existente', existingTenantId: '1', guaranteeType: 'Sem garantia', guarantors: [], attachmentStatus: 'idle', termType: 'Indeterminado', startDate: '2026-10-20', endDate: '' };
  const contexto = { hasProperty: true, hasTemplate: true, hasLocador: true };
  assert.doesNotThrow(() => validateContractDraft(draft, contexto));
  assert.throws(() => validateContractDraft({ ...draft, guaranteeType: 'Caução' }, contexto), /obrigatórios/);
  assert.doesNotThrow(() => validateContractDraft({ ...draft, guaranteeType: 'Caução', depositAmount: '1500', attachmentStatus: 'done' }, contexto));
});


test('aditivo recupera valores e cláusulas recebidos da API na edição', async () => {
  const { addendumDraftFromRecord } = await import('../src/features/contratos/use-cases/addendum-draft.ts');
  const draft = addendumDraftFromRecord({
    titulo: 'Reajuste', data: '2026-10-20', motivos: ['Valor', 'Cláusula'],
    dadosExtras: [
      { rotulo: 'Novo Valor Mensal', valor: '2.450,50' },
      { rotulo: 'Cláusula Modificada', valor: 'Nova redação da cláusula 5.' },
    ],
  });
  assert.equal(draft.novoValor, '2.450,50');
  assert.equal(draft.clausulaModificada, 'Nova redação da cláusula 5.');
});

test('documento do aditivo inclui alterações estruturadas e escapa conteúdo', async () => {
  const { addendumChangesHtml } = await import('../src/features/contratos/use-cases/addendum-document.ts');
  const html = addendumChangesHtml({ dadosExtras: [
    { rotulo: 'Novo Valor Mensal', valor: '2.450,50' },
    { rotulo: 'Cláusula Modificada', valor: '<script>indevido()</script>' },
    { rotulo: 'Nova Data de Término', valor: '2027-10-20' },
  ] });
  assert.ok(html.includes('2.450,50'));
  assert.ok(html.includes('20/10/2027'));
  assert.ok(html.includes('&lt;script&gt;'));
  assert.ok(!html.includes('<script>'));
});

test('visualização preenche partes do modelo padrão e preserva imóvel do snapshot', () => {
  const contrato = {
    numero: '2026/6002', valorMensal: 1500, dataInicio: '2026-10-20',
    imovelTitulo: 'Título atual', imovelEndereco: undefined,
    snapshot: {
      imovel: { titulo: 'Casa histórica', endereco: 'Rua histórica, 10', cidade: 'Fortaleza', matricula: '123', valor_aluguel: 1500 },
      locador: { nome: 'Locador <histórico>' },
      inquilino: { nome: 'Inquilino histórico', cpf: '52998224725' },
      modelo: { conteudo_html: '<p>{{ locador.nome }} / {{ locatario.nome }} / {{cliente.cpf}} / {{imovel.titulo}} / {{imovel.endereco}} / {{imovel.cidade}} / {{imovel.matricula}} / {{imovel.valor}}</p>' },
    },
  };
  const html = gerarHtmlContrato(contrato);
  assert.ok(html.includes('Locador &lt;histórico&gt;'));
  assert.ok(html.includes('Inquilino histórico'));
  assert.ok(html.includes('Casa histórica'));
  assert.ok(html.includes('Rua histórica, 10'));
  assert.ok(html.includes('Fortaleza'));
  assert.ok(html.includes('123'));
  assert.ok(html.includes('1.500,00'));
  assert.ok(!html.includes('{{'));
  assert.ok(!html.includes('Título atual'));
});


test('minuta antiga recupera locador das assinaturas e prioriza o snapshot', () => {
  const contrato = {
    numero: '6', dataInicio: '2026-10-20',
    assinaturas: [{ papel: 'Locador', nome: 'Locador <cadastrado>' }],
  };
  const modelo = { conteudoHtml: '<p>{{locador.nome}}</p>' };
  assert.ok(gerarHtmlContrato(contrato, modelo).includes('Locador &lt;cadastrado&gt;'));
  contrato.snapshot = {
    locador: { nome: 'Locador original' }, imovel: {}, inquilino: {},
    modelo: { conteudo_html: modelo.conteudoHtml },
  };
  const html = gerarHtmlContrato(contrato, modelo);
  assert.ok(html.includes('Locador original'));
  assert.ok(!html.includes('cadastrado'));
});

test('exportação usa a sessão em cookie sem expor token ao servidor de PDF', async (t) => {
  const { downloadDocumentPdf } = await import('../src/lib/download-document-pdf.ts');
  const calls = [];
  t.mock.method(globalThis, 'fetch', async (url, options) => {
    calls.push({ url, options });
    return calls.length === 1
      ? Response.json({ dados: { autorizacao: '/api/v1/contratos/6/exportacao-pdf?signature=teste' } })
      : new Response('%PDF-1.4 teste', { headers: { 'Content-Type': 'application/pdf' } });
  });
  const oldWindow = Object.getOwnPropertyDescriptor(globalThis, 'window');
  const oldDocument = Object.getOwnPropertyDescriptor(globalThis, 'document');
  const link = { click() {}, remove() {} };
  Object.defineProperty(globalThis, 'window', { configurable: true, value: {} });
  Object.defineProperty(globalThis, 'document', { configurable: true, value: { createElement: () => link, body: { appendChild() {} } } });
  try {
    await downloadDocumentPdf('<p>Contrato</p>', '6', 'contrato-6');
    assert.ok(calls[0].url.endsWith('/contratos/6/exportacao-pdf'));
    assert.equal(calls[0].options.credentials, 'include');
    assert.equal(calls[0].options.headers.Authorization, undefined);
    assert.equal(calls[1].url, '/documentos/pdf');
    assert.equal(calls[1].options.headers.Authorization, undefined);
    assert.equal(JSON.parse(calls[1].options.body).autorizacao, '/api/v1/contratos/6/exportacao-pdf?signature=teste');
    assert.equal(link.download, 'contrato-6.pdf');
  } finally {
    for (const [name, descriptor] of [['window', oldWindow], ['document', oldDocument]]) {
      if (descriptor) Object.defineProperty(globalThis, name, descriptor);
      else delete globalThis[name];
    }
  }
});

test('download aceita formatação compacta e configurações vazias do modelo', () => {
  const contrato = {
    numero: '7', dataInicio: '2026-10-20',
    snapshot: {
      imovel: {}, inquilino: {},
      modelo: {
        conteudo_html: '<h1>Contrato</h1><p>Texto do contrato</p>',
        tipografia: { corpo: 12, entrelinha: 1.5 },
        margens: { superior: 30, direita: 20, inferior: 25, esquerda: 15 },
      },
    },
  };
  const html = gerarHtmlContrato(contrato);
  assert.ok(html.includes('font-size: 12pt'));
  assert.ok(html.includes('margin: 30mm 20mm 25mm 15mm'));
  assert.ok(!html.includes('undefined'));
  assert.ok(!html.includes('NaN'));
  contrato.snapshot.modelo.tipografia = {};
  contrato.snapshot.modelo.margens = {};
  const padrao = gerarHtmlContrato(contrato);
  assert.ok(padrao.includes('font-size: 11pt'));
  assert.ok(padrao.includes('margin: 25mm 20mm 25mm 20mm'));
});

test('histórico financeiro preserva cada taxa, multa e juros com valor e motivo', async () => {
  const { historicoEncargos, derivarHistoricoContrato } = await import('../src/features/contratos/use-cases/contract-life-events.ts');
  const parcelas = [{ id: '8', numero: 2026, referencia: '2026/03', encargos: [
    { id: '1', tipo: 'taxa', valor: 20, motivo: 'Taxa administrativa', criadoEm: '2026-10-20T10:00:00Z' },
    { id: '2', tipo: 'multa', valor: 30, motivo: 'Atraso', criadoEm: '2026-10-20T11:00:00Z' },
    { id: '3', tipo: 'juros', valor: 5, motivo: 'Juros do período', criadoEm: '2026-10-20T12:00:00Z' },
  ] }];
  const events = historicoEncargos(parcelas);
  assert.deepEqual(events.map(e => e.evento), ['Encargo: Taxa', 'Encargo: Multa', 'Encargo: Juros']);
  assert.ok(events[0].detalhe.includes('2026/03'));
  assert.ok(events[0].detalhe.includes('20,00'));
  assert.ok(events[0].detalhe.includes('Taxa administrativa'));
  assert.equal(derivarHistoricoContrato({ parcelas }).length, 3);
  assert.deepEqual(historicoEncargos([{ id: 'sem-encargos' }]), []);
});

test('lançamento de encargo mapeia a parcela retornada pela API', async t => {
  const { addInstallmentCharge } = await import('../src/features/contratos/services/contract-service.ts');
  t.mock.method(globalThis, 'fetch', async () => Response.json({ dados: {
    id: 8, contratoId: 2, referencia: '2026/03', status: 'Pendente', valor: 100,
    juros: 30, taxasAdicionais: 20, totalDaParcela: 150,
    encargos: [{ id: 1, tipo: 'taxa', valor: 20, motivo: 'Taxa administrativa', criadoEm: '2026-10-20T10:00:00Z' }],
  } }));
  const parcela = await addInstallmentCharge('2', '8', { tipo: 'taxa', valor: 20, motivo: 'Taxa administrativa' });
  assert.equal(parcela.contratoId, '2');
  assert.equal(parcela.referencia, '2026/03');
  assert.equal(parcela.valorTotal, 150);
  assert.equal(parcela.encargos[0].id, '1');
});

test('histórico inclui encargos de parcelas além da primeira página da API', async t => {
  const { listContractInstallments } = await import('../src/features/contratos/services/contract-service.ts');
  const chamadas = [];
  t.mock.method(globalThis, 'fetch', async url => {
    const pagina = Number(new URL(url).searchParams.get('pagina'));
    chamadas.push(pagina);
    return Response.json({ dados: { totalPaginas: 2, pagina, itens: [{
      id: pagina, contratoId: 2, referencia: `2026/0${pagina}`, status: 'Pendente', valor: 100,
      encargos: [{ id: pagina, tipo: 'taxa', valor: 10, motivo: 'Encargo salvo', criadoEm: '2026-10-20T10:00:00Z' }],
    }] } });
  });
  const parcelas = await listContractInstallments('2');
  assert.deepEqual(chamadas, [1, 2]);
  assert.equal(parcelas.length, 2);
  assert.equal(parcelas[1].encargos[0].id, '2');
});

test('recibo usa o valor pago registrado e identifica contrato, parcela e emissão', async () => {
  const { paymentReceiptHtml } = await import('../src/features/contratos/use-cases/payment-receipt.ts');
  const contract = { id: '2', numero: '2026/0002', inquilinoNome: '<script>Nome</script>', imovelTitulo: 'Casa', imovelEndereco: 'Rua A, 10' };
  const installment = { id: '8', contratoId: '2', numero: 2026, referencia: '2026/03', status: 'pago', valor: 1000, juros: 25, taxasAdicionais: 5, valorPago: 900, dataPagamento: '2026-10-20', dataVencimento: '2026-10-05' };
  const html = paymentReceiptHtml(contract, installment, new Date('2026-10-20T15:00:00Z'));
  assert.ok(html.includes('Recibo de pagamento'));
  assert.ok(html.includes('2026/03'));
  assert.ok(html.includes('900,00'));
  assert.ok(html.includes('20/10/2026'));
  assert.ok(html.includes('12:00:00'));
  assert.ok(html.includes('parcela #8'));
  assert.ok(html.includes('&lt;script&gt;Nome'));
  assert.ok(!html.includes('<script>Nome'));
  assert.throws(() => paymentReceiptHtml(contract, { ...installment, status: 'pendente' }), /pagas/);
  assert.throws(() => paymentReceiptHtml(contract, { ...installment, contratoId: '3' }), /deste contrato/);
  assert.throws(() => paymentReceiptHtml(contract, { ...installment, valorPago: undefined }), /valor registrado/);
});

test('relatório inclui checklist e fotos, sem omitir anexos nem expor chave de acesso', async () => {
  const { vistoriaReportHtml } = await import('../src/features/vistorias/use-cases/vistoria-report.ts');
  const vistoria = { id: '1', contratoId: '2', contratoNumero: '2026/0002', numero: '001', tipo: 'Entrada', status: 'Em andamento', data: '2026-10-20', vistoriador: 'Maria', observacoes: '<script>observação</script>', chaveAcesso: 'segredo-nao-exportar', itens: [{ ambiente: 'Sala', item: 'Parede', estado: 'Regular', observacao: 'Rachadura' }], fotos: [{ id: '9', nomeAnexo: 'sala.png', descricao: 'Parede da sala' }] };
  const photo = { id: '9', dataUrl: 'data:image/png;base64,aGVsbG8=' };
  const html = vistoriaReportHtml(vistoria, [photo]);
  for (const value of ['Relatório de vistoria', 'Em andamento', 'Rachadura', 'sala.png', photo.dataUrl, '&lt;script&gt;observação']) assert.ok(html.includes(value));
  assert.ok(!html.includes(vistoria.chaveAcesso));
  assert.throws(() => vistoriaReportHtml(vistoria, []), /todas as fotos/);
  assert.throws(() => vistoriaReportHtml(vistoria, [{ id: '9', dataUrl: 'javascript:alert(1)' }]), /todas as fotos/);
  assert.ok(vistoriaReportHtml({ ...vistoria, fotos: [], itens: [] }, []).includes('Nenhuma foto anexada'));
});

test('recibo de caução usa valor cadastrado sem inventar data de pagamento', async () => {
  const { depositReceiptHtml } = await import('../src/features/contratos/use-cases/payment-receipt.ts');
  const contract = { id: '2', numero: '2026/0002', garantia: 'Caução', valorCaucao: 4500, dataInicio: '2026-10-20', inquilinoNome: '<Locatário>', imovelTitulo: 'Casa', imovelEndereco: 'Rua A' };
  const html = depositReceiptHtml(contract, new Date('2026-10-20T15:00:00Z'));
  assert.ok(html.includes('Recibo de caução'));
  assert.ok(html.includes('4.500,00'));
  assert.ok(html.includes('&lt;Locatário&gt;'));
  assert.ok(html.includes('contrato #2'));
  assert.ok(!html.includes('Data do pagamento'));
  assert.throws(() => depositReceiptHtml({ ...contract, garantia: 'Fiador' }), /não possui/);
  for (const valorCaucao of [undefined, 0, -1, NaN]) assert.throws(() => depositReceiptHtml({ ...contract, valorCaucao }), /valor de caução/);
});

test('abrir modelo usa ID e recupera conteúdo e configurações pela API', async t => {
  const { buildModelEditorParams } = await import('../src/features/modelos/use-cases/query-model-list.ts');
  const { getModelTemplate, updateModelTemplate } = await import('../src/features/modelos/services/modelos-service.ts');
  const recurso = { id: 5, codigo: 1005, titulo: 'Contrato salvo', tipo: 'Contrato', ativo: true, versao: 3, garantia: 'Caução', conteudoHtml: '<p>Conteúdo salvo</p>', margens: { superior: 30, direita: 20, inferior: 25, esquerda: 15 }, tipografia: { corpo: 14, entrelinha: 2 } };
  const chamadas = [];
  t.mock.method(globalThis, 'fetch', async (url, options) => { chamadas.push({ url, options }); return Response.json({ dados: recurso }); });
  const modelo = await getModelTemplate('5');
  assert.equal(modelo.uuid, '5');
  assert.equal(modelo.conteudoHtml, recurso.conteudoHtml);
  assert.deepEqual(modelo.margens, recurso.margens);
  assert.deepEqual(modelo.tipografia, recurso.tipografia);
  assert.equal(buildModelEditorParams(modelo), 'uuid=5');
  const payload = { titulo: modelo.titulo, tipo: modelo.tipo, versao: modelo.versao, conteudoHtml: '<p>Texto atualizado</p>', garantia: modelo.garantia, margens: recurso.margens, tipografia: recurso.tipografia };
  await updateModelTemplate(modelo.uuid, payload);
  assert.equal(chamadas[1].options.method, 'PUT');
  assert.ok(chamadas[1].url.endsWith('/modelos/5'));
  assert.deepEqual(JSON.parse(chamadas[1].options.body), payload);
});

test('cadastro de inquilino sem endereço aceita profissão e mantém documentos mascarados válidos', async () => {
  const { validateTenantRegistration, buildTenantPayload } = await import('../src/features/inquilinos/use-cases/validate-tenant-registration.ts');
  const { formatTaxId, formatPhone } = await import('../src/lib/formatters.ts');
  const draft = { nome: 'Inquilino', cpf: formatTaxId('52998224725'), rg: '', email: 'inquilino@example.test', telefone: formatPhone('85999991234'), dataNascimento: '1990-01-01', estadoCivil: 'Solteiro(a)', profissao: 'Pesquisador de robótica', renda: '0,00', endereco: '' };
  assert.equal(draft.cpf, '529.982.247-25');
  assert.equal(draft.telefone, '(85) 99999-1234');
  assert.doesNotThrow(() => validateTenantRegistration(draft, { todayISO: '2026-10-20' }));
  const payload = buildTenantPayload(draft);
  assert.equal(payload.cpf, '52998224725');
  assert.equal(payload.profissao, draft.profissao);
  assert.equal(payload.endereco, undefined);
  const contratoDraft = { partyMode: 'novo', newTenant: { ...draft, documento: draft.cpf }, guaranteeType: 'Sem garantia', guarantors: [], attachmentStatus: 'idle', termType: 'Indeterminado', startDate: '2026-10-20', endDate: '' };
  assert.doesNotThrow(() => validateContractDraft(contratoDraft, { hasProperty: true, hasTemplate: true, hasLocador: true }));
  assert.throws(() => validateContractDraft({ ...contratoDraft, newTenant: { ...contratoDraft.newTenant, renda: '' } }, { hasProperty: true, hasTemplate: true, hasLocador: true }));
});

test('erro de validação do imóvel mantém as mensagens de cada campo da API', async () => {
  const { HttpError } = await import('../src/core/errors/http-error.ts');
  const { propertyErrorsFromApi } = await import('../src/features/imoveis/use-cases/build-property-from-form.ts');
  const error = new HttpError(422, '/imoveis/5', { mensagem: 'Verifique os campos informados.', campos: { tipo: ['Tipo inválido.'], area: ['Informe a área.'], nomeProprietario: ['Nome muito longo.'] } });
  assert.deepEqual(propertyErrorsFromApi(error), { tipo: 'Tipo inválido.', area: 'Informe a área.', nomeProprietario: 'Nome muito longo.' });
  assert.deepEqual(propertyErrorsFromApi(new HttpError(500, '/imoveis/5', null)), {});
});
