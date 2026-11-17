# MobyContract

Backend Laravel para gestão de imóveis, inquilinos, contratos, aditivos, vistorias e parcelas.

O ambiente Docker executa a API com Nginx, PHP-FPM, worker de filas, PostgreSQL e Redis para cache e filas.

## Execução local

Requisito: Docker Engine com Docker Compose v2. Execute na raiz do repositório.

Se `app/.env` ainda não existir:

```bash
cp app/.env.example app/.env
```

Preencha `DB_PASSWORD` e `POSTGRES_PASSWORD` com a mesma senha. Para gerar a chave do Laravel:

```bash
docker compose build app
docker compose run --rm --no-deps app php artisan key:generate --show
```

Copie a chave exibida para `APP_KEY` em `app/.env`. Depois:

```bash
docker compose up --build -d
```

Com o `.env` configurado, também é possível executar `bash iniciar.sh` no Linux/WSL ou `iniciar.bat` no Windows.

API: `http://localhost:8000/api/v1`. Saúde: `http://localhost:8000/up`.

Os dados persistem em volumes Docker. O código PHP e suas dependências são incluídos na imagem; após alterar o código, execute `docker compose up --build -d`.

O entrypoint definido no Dockerfile aplica as migrations e prepara o cliente OAuth
automaticamente antes de iniciar o PHP-FPM, sem duplicar clientes existentes.
As chaves do Passport são geradas na primeira inicialização da API e persistidas,
junto aos demais arquivos de estado do Laravel, no volume `estado-app`.

## Frontend no Docker

O frontend Next.js fica em `web/` e sobe junto com os demais serviços em
`docker compose up --build -d`. Acesse `http://localhost:8000`.

Para reconstruir e iniciar somente o frontend com suas dependências:

```bash
docker compose up --build -d web nginx
```

A imagem inclui Chromium para exportar PDFs e executa com usuário sem privilégios.
O Nginx encaminha `/` para o Next.js e `/api` para o Laravel. No navegador,
a API usa `/api/v1`, no mesmo endereço do frontend. Para outro endereço,
defina `NEXT_PUBLIC_BASE_URI_API` no ambiente ou no `.env` da raiz antes do build;
essa variável é incorporada ao JavaScript durante a compilação. Dentro do
container, a exportação de PDFs usa `BASE_URI_API=http://nginx/api/v1`.

Para construir a imagem separadamente, execute na raiz:

```bash
docker build -t mobycontract/web:local ./web
```

## Estrutura

- `app/`: API Laravel, migrations, testes e configurações de ambiente.
- `docker/`: Dockerfile PHP e configurações de infraestrutura.
- `nginx/`: configuração HTTP da API.
- `docker-compose.yml`: ambiente local executado a partir da raiz.
- `web/`: frontend Next.js e Dockerfile com build em etapas.

## Testes e qualidade

O serviço opcional `cli` contém as dependências de desenvolvimento:

```bash
docker compose build cli
docker compose run --rm cli php artisan test --compact
docker compose run --rm cli php vendor/bin/phpstan analyse --memory-limit=1G
docker compose run --rm cli php vendor/bin/pint --test
```

O banco de testes é `mobycontract_teste`, separado do banco principal.
