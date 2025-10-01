<?php
// Força a exibição de todos os erros
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h1>Teste de Ligação à Base de Dados</h1>";

// 1. Tenta carregar as configurações
echo "<p>A carregar 'config.php'...</p>";
require_once 'config.php';
echo "<p style='color:green;'>'config.php' carregado com sucesso.</p>";

// 2. Tenta estabelecer a ligação usando as constantes do config.php
echo "<p>A tentar ligar a '" . DB_HOST . "' com o utilizador '" . DB_USER . "'...</p>";

// O @ suprime o aviso padrão do PHP para que possamos mostrar a nossa própria mensagem.
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// 3. Verifica o resultado da ligação
if ($conn->connect_error) {
    echo "<h2 style='color:red;'>FALHA NA LIGAÇÃO!</h2>";
    echo "<p>O servidor retornou o seguinte erro: <strong>" . $conn->connect_error . "</strong></p>";
    echo "<p><strong>Ações a tomar:</strong></p>";
    echo "<ol>";
    echo "<li>Verifique se as credenciais (DB_USER, DB_PASS, DB_NAME) no ficheiro <strong>config.php</strong> estão absolutamente corretas.</li>";
    echo "<li>Vá ao seu cPanel -> Bases de Dados MySQL e confirme o nome da base de dados, o nome de utilizador e se o utilizador foi adicionado à base de dados com todas as permissões.</li>";
    echo "</ol>";
} else {
    echo "<h2 style='color:green;'>LIGAÇÃO À BASE DE DADOS BEM-SUCEDIDA!</h2>";
    echo "<p>O seu sistema consegue comunicar com a base de dados. O problema do erro 500 está noutro ficheiro.</p>";
    $conn->close();
}
?>