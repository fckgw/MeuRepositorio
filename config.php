<?php
/**
 * Ficheiro de Configuração do Sistema - Versão Final Corrigida
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
define('PUBLIC_PARENT_PATH', 'DriverBDSoft');


// --- CONFIGURAÇÕES DA BASE DE DADOS ---

// VERIFIQUE ESTES DADOS COM ATENÇÃO NO SEU CPANEL
define('DB_HOST', 'localhost');
define('DB_USER', 'feli0499_root'); // Utilizador da base de dados
define('DB_PASS', 'BDSoft@1020');   // Palavra-passe da base de dados
define('DB_NAME', 'feli0499_meurepositorio'); // Nome da base de dados


// --- CONFIGURAÇÕES DO FTP ---

define('FTP_SERVER', 'localhost');
define('FTP_USER', 'souzafelipe@bdsoft.com.br'); // Utilizador da conta FTP
define('FTP_PASS', 'Fckgw!151289'); // Palavra-passe da conta FTP

/**
 * CAMINHO FÍSICO FTP (Caminho completo no servidor a partir da raiz do login FTP)
 * Este é o caminho que as funções PHP usarão para manipular os ficheiros.
 * ESTA LINHA FOI CORRIGIDA.
 */
define('FTP_PARENT_DIR', '/meurepositorio.bdsoft.com.br/DriverBDSoft');


// --- CONFIGURAÇÕES DA APLICAÇÃO ---

define('APP_NAME', 'BDSoft Driver');
define('TRIAL_DAYS', 7);
define('TOTAL_SPACE_GB', 1); // Limite de espaço por utilizador
?>