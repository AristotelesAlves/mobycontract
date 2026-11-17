# MobyContract

Sistema web desenvolvido como parte de um Trabalho de Conclusão de Curso (TCC), complementando um projeto de blockchain por meio do consumo de sua API. A solução blockchain pode atender a diferentes nichos; neste projeto, o setor imobiliário foi escolhido para demonstrar sua aplicação na gestão de imóveis e contratos.

## Iniciar

Requisitos: Docker com Compose v2 e portas **8000** e **3000** livres. Execute na raiz do projeto.

**1. Crie o arquivo de configuração** (se ainda não existir):

```bash
cp app/.env.example app/.env
cp web/.env.example web/.env.local
```

No Windows:

```bat
copy app\.env.example app\.env
copy web\.env.example web\.env.local
```

Edite `app/.env`: preencha `DB_PASSWORD` e `POSTGRES_PASSWORD` com **a mesma senha**. Mantenha `DB_HOST=db` e os demais valores do exemplo.

O `web/.env.local` configura o frontend executado fora do Docker. No Docker, as URLs da API já são definidas pelo Compose.

**2. Prepare a rede:**

```bash
docker network inspect mobycontract-integracao >/dev/null 2>&1 || docker network create mobycontract-integracao
```

**3. Gere a chave do Laravel** (somente se `APP_KEY` estiver vazia):

```bash
docker compose build app
docker compose run --rm --no-deps app php artisan key:generate --show
```

Copie a chave exibida (`base64:...`) para `APP_KEY` em `app/.env`. O comando não salva no arquivo local; o Compose também não gera essa chave automaticamente.

**4. Suba os serviços:**

```bash
docker compose up --build -d
docker compose ps
```

Aguarde os serviços ficarem saudáveis. As migrations e a configuração do Passport são executadas automaticamente.

**5. Crie os dados de demonstração** (primeira execução local):

```bash
docker compose exec app php artisan db:seed
```

Acesse **http://localhost:8000/login**:

- E-mail: `admin@mobycontract.com`
- Senha: `senha1234`

O seeder cria dados de exemplo e redefine as contas de demonstração quando repetido. Use apenas no ambiente local.

## Comandos úteis

```bash
# Iniciar novamente
docker compose up -d

# Reconstruir após alterar código
docker compose up --build -d

# Aplicar alterações no app/.env à API e à fila
docker compose up -d --force-recreate app queue

# Ver logs
docker compose logs --tail 100 app db nginx

# Parar, preservando os dados
docker compose down
```

API: `http://localhost:8000/api/v1`. Frontend direto: `http://localhost:3000`.

## Se der erro

- **Banco unhealthy:** confira `POSTGRES_PASSWORD` e os logs de `db`. As credenciais de um banco já criado não mudam apenas editando o `.env`.
- **INTERNAL_ERROR no login:** confira `APP_KEY` e leia o log abaixo.
- **Porta ocupada:** libere 8000/3000 ou ajuste as portas no Compose.

```bash
docker compose exec app sh -c 'tail -n 100 storage/logs/*.log'
```

O registro na blockchain exige o serviço externo `registry` na rede `mobycontract-integracao` e os valores `BLOCKCHAIN_*` configurados no `app/.env`. Ele não é necessário para fazer login.
