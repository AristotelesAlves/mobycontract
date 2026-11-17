export class HttpError extends Error {
  public readonly statusCode: number;
  public readonly endpoint: string;
  public readonly body: unknown;

  constructor(statusCode: number, endpoint: string, body: unknown, message?: string) {
    super(message ?? `Request to ${endpoint} failed with status ${statusCode}`);
    this.name = "HttpError";
    this.statusCode = statusCode;
    this.endpoint = endpoint;
    this.body = body;
  }
}