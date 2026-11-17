export interface DomainErrorDetails {
  [key: string]: string | undefined;
}

export class DomainError extends Error {
  public code: string;
  public details: DomainErrorDetails;

  constructor(
    message: string,
    code = "DOMAIN_ERROR",
    details: DomainErrorDetails = {},
  ) {
    super(message);
    this.name = "DomainError";
    this.code = code;
    this.details = details;
  }
}