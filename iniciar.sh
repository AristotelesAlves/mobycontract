#!/usr/bin/env bash

set -Eeuo pipefail

erro() {
  printf 'ERRO: %s\n' "$1" >&2
  exit 1
}

printf '\n=== MobyContract: inicialização local ===\n\n'

command -v docker >/dev/null 2>&1 || erro 'Docker não foi encontrado. Instale e inicie o Docker.'
docker compose version >/dev/null 2>&1 || erro 'Docker Compose não está disponível. Atualize a instalação do Docker.'
[[ -f app/.env ]] || erro 'app/.env não foi encontrado. Copie app/.env.example para app/.env e preencha os valores locais antes de continuar.'

printf 'Subindo os serviços...\n'
docker compose up --build -d

printf 'Aplicando migrations do banco de dados...\n'
docker compose exec -T api php artisan migrate --force

printf '\nSistema iniciado com sucesso.\nAcesse: http://localhost:8000\nPara acompanhar os logs: docker compose logs -f\n'
