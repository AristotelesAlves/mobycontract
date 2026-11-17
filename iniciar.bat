@echo off
setlocal EnableExtensions

echo.
echo === MobyContract: inicializacao local ===
echo.

where docker >nul 2>&1
if errorlevel 1 (
    echo ERRO: Docker nao foi encontrado. Instale e inicie o Docker Desktop.
    exit /b 1
)

docker compose version >nul 2>&1
if errorlevel 1 (
    echo ERRO: Docker Compose nao esta disponivel. Atualize o Docker Desktop.
    exit /b 1
)

if not exist "app\.env" (
    echo ERRO: app\.env nao foi encontrado.
    echo Copie app\.env.example para app\.env e preencha os valores locais antes de continuar.
    exit /b 1
)

echo Subindo os servicos...
docker compose up --build -d
if errorlevel 1 (
    echo ERRO: nao foi possivel iniciar os containers.
    exit /b 1
)

echo Sistema iniciado com sucesso.
echo Acesse: http://localhost:8000
echo Para acompanhar os logs: docker compose logs -f
endlocal
