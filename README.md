# MobyContract

MVP acadêmico para gestão de contratos de locação. A aplicação registra imóveis, inquilinos, contratos, aditivos e vistorias. Após a assinatura, o arquivo PDF é preservado no armazenamento da aplicação e seu hash SHA-256 é ancorado na camada blockchain para permitir a verificação posterior de integridade.

## O que o sistema faz

- Gerencia imóveis, inquilinos, contratos, aditivos, vistorias e parcelas.
- Controla acesso por sessão em cookie seguro, papéis e permissões da API.
- Gera, armazena e disponibiliza documentos do contrato.
- Calcula o SHA-256 do PDF assinado e o compara com a âncora registrada.
- Permite consultar uma transação e o hash documental correspondente.

A assinatura digital e a blockchain têm papéis complementares: a assinatura comprova autoria, integridade e manifestação de vontade no momento da assinatura; a blockchain registra a evidência do hash e facilita a auditoria de que o mesmo arquivo permaneceu íntegro depois desse momento.

## Arquitetura

```text
Navegador
    │
    ▼
Nginx ───────────► Next.js (web)
    │
    ├────────────► Laravel (API) ───► PostgreSQL / Redis / arquivos
    │                              │
    │                              ▼
    └────────────────────────► Registry Node.js ───► Anvil / contratos Solidity
```

O frontend usa Next.js e organiza o código por funcionalidade de negócio (`features/contratos`, `features/imoveis`, `features/inquilinos` e `features/vistorias`). Componentes visuais e utilitários permanecem compartilhados.

O backend Laravel segue um MVC adaptado para API: rota, middleware, request de validação, controller, DTO, serviço, repositório e modelo. Os módulos concentram o vocabulário de cada domínio e as dependências externas ficam em `Shared/Infrastructure`.

A camada blockchain possui contratos Solidity, um registry Node.js e portas de aplicação que separam regras de ancoragem da implementação EVM. No ambiente local, o ledger é Anvil; a configuração Besu/QBFT disponível no repositório é uma evolução para uma rede permissionada com validadores independentes.

## Estrutura do repositório

```text
app/          API Laravel, migrations, testes e armazenamento
web/          interface Next.js
blockchain/   contratos Solidity, registry Node.js e testes Hardhat
nginx/        proxy reverso e borda HTTP
```

## Execução local

Pré-requisitos: Docker Engine com Docker Compose v2.

Há inicializadores na raiz do projeto:

```bash
# Linux
chmod +x iniciar.sh
./iniciar.sh
```

No Windows, execute `iniciar.bat` pelo Explorador de Arquivos ou pelo Prompt de Comando. Os dois scripts exigem `app/.env`, sobem os containers e aplicam as migrations. Eles não recriam volumes nem apagam o banco de dados.

Execução manual equivalente:

1. Revise `app/.env`. Para um ambiente novo, use `app/.env.example` como referência e defina senhas e segredos próprios.
2. Suba os serviços:

   ```bash
   docker compose up --build -d
   ```

3. Aplique as migrations:

   ```bash
   docker compose exec api php artisan migrate --force
   ```

4. Acesse `http://localhost:8000`.

Os dados persistem nos volumes do Docker. Executar `docker compose up --build` reconstrói imagens quando necessário, mas não apaga volumes. Para remover também os dados persistidos seria preciso uma ação explícita sobre os volumes.

## Testes e qualidade

O PostgreSQL de testes é `mobycontract_teste`, separado do banco principal. A suíte da blockchain usa esse banco apenas para testar o registry e limpa a tabela de chaves de API antes de cada caso.

Backend:

```bash
docker compose exec api php artisan test --compact
docker compose exec api php vendor/bin/phpstan analyse --memory-limit=1G
docker compose exec api php vendor/bin/pint --test
```

Blockchain, com banco de testes na rede interna:

```bash
docker compose --profile test build blockchain-test
docker compose --profile test run --rm blockchain-test
```

O serviço `blockchain-test` usa a rede interna `rede-dados`, `HARDHAT_NETWORK=hardhat` e `mobycontract_teste`; portanto não grava na rede Anvil usada pela aplicação.

Frontend, no estágio de build que contém as dependências de desenvolvimento:

```bash
docker build --target build -t mobycontract/web-check ./web
docker run --rm mobycontract/web-check pnpm run lint
docker run --rm mobycontract/web-check pnpm run typecheck
docker run --rm mobycontract/web-check pnpm test
```

## Segurança e limites do MVP

O projeto inclui validação de entradas no backend, autorização por papel, escopo de recursos por usuário, proteção de sessão, limitação de tentativas, sanitização de conteúdo de modelos e cabeçalhos HTTP no proxy. Os controles precisam continuar sendo revisados em toda alteração relevante.

O ambiente Anvil é adequado para demonstrar o fluxo técnico e os testes, mas não é uma rede distribuída de produção. Um produto operacional exigiria nós independentes, governança, gestão externa de chaves, monitoramento, backup, processo de resposta a incidentes e consenso permissionado, como Besu com QBFT.
