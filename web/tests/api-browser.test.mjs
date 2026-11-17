import test from 'node:test';
import assert from 'node:assert/strict';

test('navegador usa a API na mesma origem sem expor o endereço Docker', async (t) => {
  const previous = process.env.NEXT_PUBLIC_BASE_URI_API;
  const windowDescriptor = Object.getOwnPropertyDescriptor(globalThis, 'window');
  delete process.env.NEXT_PUBLIC_BASE_URI_API;
  Object.defineProperty(globalThis, 'window', { configurable: true, value: {} });
  const calls = [];
  t.mock.method(globalThis, 'fetch', async (url, options) => {
    calls.push({ url, options });
    return Response.json({ dados: [] });
  });
  try {
    const { api } = await import('../src/services/api/base-api.ts');
    await api.get('/contratos');
    assert.equal(calls[0].url, '/api/v1/contratos');
    assert.equal(calls[0].options.credentials, 'include');
  } finally {
    if (previous === undefined) delete process.env.NEXT_PUBLIC_BASE_URI_API;
    else process.env.NEXT_PUBLIC_BASE_URI_API = previous;
    if (windowDescriptor) Object.defineProperty(globalThis, 'window', windowDescriptor);
    else delete globalThis.window;
  }
});
