import test from 'node:test';
import assert from 'node:assert/strict';

test('chamadas no servidor preferem o endereço interno à URL pública', async (t) => {
  const previousInternal = process.env.BASE_URI_API;
  const previousPublic = process.env.NEXT_PUBLIC_BASE_URI_API;
  process.env.BASE_URI_API = 'http://nginx/api/v1';
  process.env.NEXT_PUBLIC_BASE_URI_API = '/api/v1';
  const calls = [];
  t.mock.method(globalThis, 'fetch', async (url) => {
    calls.push(url);
    return Response.json({ dados: [] });
  });
  try {
    const { api } = await import('../src/services/api/base-api.ts');
    await api.get('/contratos');
    assert.deepEqual(calls, ['http://nginx/api/v1/contratos']);
  } finally {
    for (const [key, value] of [['BASE_URI_API', previousInternal], ['NEXT_PUBLIC_BASE_URI_API', previousPublic]]) {
      if (value === undefined) delete process.env[key];
      else process.env[key] = value;
    }
  }
});
