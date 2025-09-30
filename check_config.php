<?php
// Habilita a exibição de erros para vermos o problema exato
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h1>Teste de Configuração</h1>";
echo "<p>Tentando carregar o arquivo 'config.php'...</p>";

// Tenta carregar o arquivo de configuração
require_once 'config.php';

echo "<p style='color:green; font-weight:bold;'>Sucesso! O arquivo 'config.php' foi carregado corretamente.</p>";
echo "<p>O servidor FTP configurado é: " . FTP_SERVER . "</p>";
?>