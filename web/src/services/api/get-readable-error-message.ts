import { DomainError } from "@/core/errors/domain-error";
import { HttpError } from "@/core/errors/http-error";

const HTTP_STATUS_MESSAGES: Record<number, string> = {
  400: "Requisição inválida. Verifique os dados enviados.",
  401: "Você precisa estar logado para continuar.",
  403: "Você não tem permissão para realizar esta ação.",
  404: "O recurso solicitado não foi encontrado.",
  408: "A requisição demorou demais. Tente novamente.",
  409: "Este registro conflita com dados existentes.",
  422: "Os dados enviados não puderam ser processados.",
  429: "Muitas requisições. Aguarde um momento.",
  500: "O servidor encontrou um erro inesperado.",
  502: "O serviço está temporariamente indisponível.",
  503: "O serviço está temporariamente indisponível.",
  504: "O serviço demorou demais para responder.",
};

export function getReadableErrorMessage(error: unknown): string {
  if (error instanceof DomainError) {
    return error.message;
  }

  if (error instanceof HttpError && error.statusCode === 0) {
    return "Não foi possível alcançar o servidor. Verifique sua conexão e tente novamente.";
  }

  if (
    error instanceof HttpError &&
    error.body &&
    typeof error.body === "object" &&
    "mensagem" in error.body &&
    typeof (error.body as { mensagem: unknown }).mensagem === "string"
  ) {
    return (error.body as { mensagem: string }).mensagem;
  }

  if (error instanceof HttpError) {
    return HTTP_STATUS_MESSAGES[error.statusCode] ?? "Erro inesperado. Tente novamente.";
  }

  if (error instanceof Error && error.message.trim().length > 0) {
    return error.message;
  }

  return "Erro inesperado. Tente novamente.";
}