<?php

declare(strict_types=1);
use App\Shared\Infrastructure\Blockchain\RegistradorBlockchainDeterministico;

return [

    /*
    | Parâmetros de negócio que o administrador ajusta sem mudança de código
    | (seção 15 de REGRAS_DE_NEGOCIO.md). O valor daqui é o padrão e só é lido
    | por método privado nomeado *Padrao() ou *Configuracao() (R-15.8).
    */

    'vistorias' => [
        'validade_link_dias' => (int) env('VISTORIA_LINKS_VALIDADE_DIAS', 7),
        'tentativas_senha' => (int) env('VISTORIA_TENTATIVAS_SENHA', 5),
        'tentativas_bloqueio_minutos' => (int) env('VISTORIA_TENTATIVAS_BLOQUEIO_MINUTOS', 15),
    ],

    'contratos' => [
        'dia_vencimento_padrao' => (int) env('CONTRATOS_DIA_VENCIMENTO_PADRAO', 5),
        'multa_rescisao_padrao' => (float) env('CONTRATOS_MULTA_RESCISAO_PADRAO', 3),
        'multa_atraso_padrao' => env('CONTRATOS_MULTA_ATRASO_PADRAO', '2.00'),
        'prazo_vigencia_meses' => (int) env('CONTRATOS_PRAZO_VIGENCIA_MESES', 12),
    ],

    'blockchain' => [
        'rede' => env('BLOCKCHAIN_REDE', 'Polygon Amoy Testnet'),
        'registry_url' => env('BLOCKCHAIN_REGISTRY_URL', 'http://localhost:4000'),
        'api_key' => env('BLOCKCHAIN_API_KEY', ''),
        'registrador' => env('BLOCKCHAIN_REGISTRADOR', RegistradorBlockchainDeterministico::class),
    ],

    'autenticacao' => [
        'duracao_cookie_minutos' => (int) env('AUTH_ACCESS_COOKIE_MINUTES', 60),
        'cookie_secure' => (bool) env('AUTH_COOKIE_SECURE', env('APP_ENV') === 'production'),
        'cookie_same_site' => env('AUTH_COOKIE_SAME_SITE', 'lax'),
    ],

    'arquivos' => [
        'diretorio_base' => env('ARQUIVOS_DIRETORIO_BASE', storage_path('app/uploads')),
        'tamanho_maximo_bytes' => (int) env('ARQUIVOS_TAMANHO_MAXIMO_BYTES', 26214400),
    ],

    'inquilinos' => [
        'idade_minima' => (int) env('INQUILINOS_IDADE_MINIMA', 18),
        'limite_compromisso_seguro' => (int) env('INQUILINOS_LIMITE_COMPROMISSEO_SEGURO', 30),
        'limite_alerta_compromisso' => (int) env('INQUILINOS_LIMITE_ALERTA_COMPROMISSO', 35),
    ],

    'listagem' => [
        'itens_por_pagina' => (int) env('LISTAGEM_ITENS_POR_PAGINA', 20),
        'tamanho_maximo_pagina' => (int) env('LISTAGEM_TAMANHO_MAXIMO_PAGINA', 100),
    ],

    'painel' => [
        'movimentacoes_exibidas' => (int) env('PAINEL_MOVIMENTACOES_EXIBIDAS', 5),
    ],

    'modelos' => [
        'largura_maxima_imagem' => (int) env('MODELOS_LARGURA_MAXIMA_IMAGEM', 900),
        'qualidade_imagem' => (float) env('MODELOS_QUALIDADE_IMAGEM', 0.7),
        'tamanho_maximo_imagem_bytes' => (int) env('MODELOS_TAMANHO_MAXIMO_IMAGEM', 10485760),
        'tamanho_maximo_documento_bytes' => (int) env('MODELOS_TAMANHO_MAXIMO_DOCUMENTO', 26214400),
        'pagina_a4_largura_mm' => 210,
        'pagina_a4_altura_mm' => 297,
        'margem_padrao' => ['top' => 25, 'right' => 20, 'bottom' => 25, 'left' => 20],
    ],

];
