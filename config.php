<?php
/**
 * Arquivo de Configuração do Sistema - Versão Final Corrigida
 */

// --- CONFIGURAÇÕES DE URL E CAMINHOS ---

/**
 * URL Base da Aplicação (IMPORTANTE: sem a barra no final)
 */
define('BASE_URL', 'https://meurepositorio.bdsoft.com.br');

/**
 * CAMINHO PÚBLICO (Caminho relativo à URL base para acesso via web)
 * Usado para exibir imagens no preview e construir links.
 */
define('PUBLIC_UPLOADS_PATH', 'DriverBDSoft');


// --- CONFIGURAÇÕES DO SERVIDOR FTP ---

define('FTP_SERVER', 'localhost');
define('FTP_USER', 'souzafelipe@bdsoft.com.br');
define('FTP_PASS', 'Fckgw!151289');

/**
 * CAMINHO FÍSICO FTP (Caminho completo no servidor a partir da raiz do login FTP)
 * Usado pelas funções PHP para manipular os arquivos diretamente no servidor.
 */
define('FTP_UPLOAD_DIR', 'meurepositorio.bdsoft.com.br/DriverBDSoft');


// --- CONFIGURAÇÕES GERAIS ---

// Defina o espaço total do seu drive em Gigabytes (GB)
define('TOTAL_SPACE_GB', 1);


// --- CONFIGURAÇÕES DO BANCO DE DADOS MYSQL ---

define('DB_SERVER', 'localhost');
define('DB_USERNAME', 'feli0499_root');
define('DB_PASSWORD', 'BDSoft@1020');
define('DB_NAME', 'feli0499_meurepositorio');

// É uma boa prática omitir a tag de fechamento ?>