import { HttpError } from "@/core/errors/http-error";

/**
 * URL base da API do backend. Usa, nesta ordem:
 *  1. valor injetado no construtor (testes);
 *  2. no servidor: BASE_URI_API, com fallback local na porta 8000;
 *  3. no navegador: NEXT_PUBLIC_BASE_URI_API, com fallback /api/v1.
 *
 * NUNCA apontar para o json-server (mock) por padrão: um fallback silencioso
 * fazia cadastros irem para um db.json local em vez do banco de verdade.
 */
const DEFAULT_BASE_URI_API = "/api/v1";

export interface BaseApiRequestOptions {
  query?: Record<string, string | number | boolean | undefined>;
  headers?: Record<string, string>;
  body?: unknown;
  signal?: AbortSignal;
  skipAuth?: boolean;
}

function buildEndpoint(path: string, query?: BaseApiRequestOptions["query"]): string {
  const isAbsoluteUrl = path.startsWith("http://") || path.startsWith("https://");
  const normalizedPath = isAbsoluteUrl || path.startsWith("/") ? path : `/${path}`;
  if (!query) return normalizedPath;

  const searchParams = new URLSearchParams();
  for (const [key, value] of Object.entries(query)) {
    if (value !== undefined) searchParams.append(key, String(value));
  }

  const queryString = searchParams.toString();
  return queryString ? `${normalizedPath}?${queryString}` : normalizedPath;
}

async function parseResponseBody(response: Response): Promise<unknown> {
  const contentType = response.headers.get("content-type") ?? "";
  if (!contentType.includes("application/json")) {
    return response.status === 204 ? null : response.text();
  }
  return response.json();
}

class BaseApi {
  private readonly baseUri: string;

  constructor(baseUri?: string) {
    const definido =
      baseUri ?? (typeof window === "undefined"
        ? process.env.BASE_URI_API ?? "http://localhost:8000/api/v1"
        : process.env.NEXT_PUBLIC_BASE_URI_API);
    this.baseUri = (definido ?? DEFAULT_BASE_URI_API).replace(/\/+$/, "");
    if (!definido) {
      console.warn(
        `[base-api] NEXT_PUBLIC_BASE_URI_API não definida; usando fallback ${this.baseUri}`,
      );
    }
  }

  private async request<TResponse>(
    method: string,
    path: string,
    options: BaseApiRequestOptions = {},
  ): Promise<TResponse> {
    const endpoint = buildEndpoint(path, options.query);
    const url = endpoint.startsWith("http") ? endpoint : `${this.baseUri}${endpoint}`;

    const headers: Record<string, string> = {
      ...(options.body instanceof FormData ? {} : { "Content-Type": "application/json" }),
      Accept: "application/json",
      ...options.headers,
    };

    let response: Response;
    try {
      response = await fetch(url, {
        method,
        headers,
        credentials: "include",
        body: options.body === undefined ? undefined : options.body instanceof FormData ? options.body : JSON.stringify(options.body),
        signal: options.signal,
      });
    } catch (networkError) {
      throw new HttpError(0, endpoint, networkError, "Network request failed");
    }

    const parsedBody = await parseResponseBody(response);

    if (!response.ok) {
      throw new HttpError(response.status, endpoint, parsedBody);
    }

    return parsedBody as TResponse;
  }

  async arquivo(path: string): Promise<Blob> {
    const response = await fetch(`${this.baseUri}${path}`, {
      headers: { Accept: "application/json" },
      credentials: "include",
    });
    if (!response.ok) throw new HttpError(response.status, path, await parseResponseBody(response));
    const blob = await response.blob();
    if (!blob.size || /application\/json|text\/html/.test(blob.type)) {
      throw new HttpError(502, path, null, "O servidor não retornou um arquivo válido.");
    }
    return blob;
  }

  get<TResponse>(path: string, options?: BaseApiRequestOptions): Promise<TResponse> {
    return this.request<TResponse>("GET", path, options);
  }

  post<TResponse>(path: string, body?: unknown, options?: BaseApiRequestOptions): Promise<TResponse> {
    return this.request<TResponse>("POST", path, { ...options, body });
  }

  put<TResponse>(path: string, body?: unknown, options?: BaseApiRequestOptions): Promise<TResponse> {
    return this.request<TResponse>("PUT", path, { ...options, body });
  }

  patch<TResponse>(path: string, body?: unknown, options?: BaseApiRequestOptions): Promise<TResponse> {
    return this.request<TResponse>("PATCH", path, { ...options, body });
  }

  delete<TResponse>(path: string, options?: BaseApiRequestOptions): Promise<TResponse> {
    return this.request<TResponse>("DELETE", path, options);
  }
}

export const api = new BaseApi();
