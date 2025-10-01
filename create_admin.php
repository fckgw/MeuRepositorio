<?php
// Força a exibição de todos os erros para um diagnóstico claro.
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Inclui os ficheiros de configuração e de ligação à base de dados.
require_once 'config.php';
require_once 'db.php';

echo "<h1>Script de Criação de Administrador</h1>";

// --- Definições do Administrador ---
$admin_user = 'Administrator'; // Pode alterar se quiser
$admin_email = 'souzafelipe@bdsoft.com.br'; // Use o seu email principal aqui
$admin_pass = 'Fckgw!151289'; // A sua palavra-passe

// --- Verificação de Segurança: Verifica se o utilizador já existe ---
$stmt = $conn->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
$stmt->bind_param("ss", $admin_user, $admin_email);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows > 0) {
    die("<h2 style='color:orange;'>AVISO: O utilizador 'admin' ou o email '$admin_email' já existe na base de dados.</h2><p>Nenhuma ação foi tomada. Pode apagar este ficheiro com segurança.</p>");
}
$stmt->close();

// --- Criação do Utilizador ---
echo "<p>A criar o utilizador '<strong>{$admin_user}</strong>'...</p>";

// Hash da palavra-passe - NUNCA guarde palavras-passe em texto simples!
$hashed_pass = password_hash($admin_pass, PASSWORD_DEFAULT);
$admin_role = 'admin';
// Para administradores, o trial nunca expira.
$trial_ends = date('Y-m-d H:i:s', strtotime('+20 years'));

// Prepara e executa a inserção na base de dados.
$stmt = $conn->prepare("INSERT INTO users (username, email, password, role, trial_ends_at) VALUES (?, ?, ?, ?, ?)");
$stmt->bind_param("sssss", $admin_user, $admin_email, $hashed_pass, $admin_role, $trial_ends);

if ($stmt->execute()) {
    $admin_id = $stmt->insert_id;
    echo "<p style='color:green;'><strong>Sucesso!</strong> Utilizador administrador criado com o ID: {$admin_id}.</p>";

    // --- Criação da Pasta FTP ---
    echo "<p>A criar a pasta pessoal no FTP...</p>";
    $user_folder = FTP_PARENT_DIR . '/user_' . $admin_id;
    
    $conn_id = ftp_connect(FTP_SERVER);
    if ($conn_id && ftp_login($conn_id, FTP_USER, FTP_PASS)) {
        ftp_pasv($conn_id, true);
        if (@ftp_mkdir($conn_id, $user_folder)) {
            echo "<p style='color:green;'><strong>Sucesso!</strong> Pasta '{$user_folder}' criada no FTP.</p>";
        } else {
            echo "<p style='color:orange;'>AVISO: Não foi possível criar a pasta no FTP. Verifique se o diretório pai '<strong>" . FTP_PARENT_DIR . "</strong>' existe e tem permissões de escrita (geralmente 755).</p>";
        }
        ftp_close($conn_id);
    } else {
         echo "<p style='color:red;'>ERRO: Falha na ligação FTP. A pasta do utilizador não foi criada.</p>";
    }

} else {
    echo "<h2 style='color:red;'>ERRO FATAL!</h2>";
    echo "<p>Não foi possível inserir o utilizador na base de dados: " . $stmt->error . "</p>";
}
$stmt->close();
$conn->close();

echo "<hr>";
echo "<div style='background-color:#fffbe6; border:1px solid #ffe58f; padding: 15px; margin-top:20px; border-radius: 8px;'>";
echo "<h2 style='color:red;'>AÇÃO NECESSÁRIA E MUITO IMPORTANTE!</h2>";
echo "<p>Por razões de segurança, <strong>APAGUE ESTE FICHEIRO (create_admin.php)</strong> do seu servidor agora mesmo!</p>";
echo "</div>";
?>