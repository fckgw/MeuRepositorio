<?php
// 1. Verifica se o administrador está autenticado
require_once 'session_check.php';
if ($user['role'] !== 'admin') {
    die("Acesso negado. Apenas administradores podem executar esta ação.");
}

// 2. Inclui a configuração da base de dados
require_once 'db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['user_id'])) {
    $user_id_to_notify = $_POST['user_id'];

    // 3. Busca os dados do utilizador-alvo na base de dados
    $stmt = $conn->prepare("SELECT email, username, trial_ends_at FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id_to_notify);
    $stmt->execute();
    $user_to_notify = $stmt->get_result()->fetch_assoc();

    if ($user_to_notify) {
        $to = $user_to_notify['email'];
        $username = $user_to_notify['username'];
        $trial_ends_at = $user_to_notify['trial_ends_at'];
        $trial_days = TRIAL_DAYS;
        
        // 4. Monta o email
        $subject = "Bem-vindo ao " . APP_NAME . " (Reenvio de Informações)";
        $body = "Olá {$username},\n\n"
              . "Segue uma cópia das informações da sua conta no " . APP_NAME . ".\n\n"
              . "Credenciais de Acesso:\n"
              . "Email: {$to}\n\n"
              . "O seu período de trial de {$trial_days} dias termina em: " . date('d/m/Y', strtotime($trial_ends_at)) . ".\n\n"
              . "Obrigado,\nA Equipa BDSoft";
              
        // 5. Monta os cabeçalhos do email, incluindo a cópia oculta (Bcc)
        $headers = "From: no-reply@" . parse_url(BASE_URL, PHP_URL_HOST) . "\r\n"
                 . "Bcc: seu-email-de-admin@bdsoft.com.br" . "\r\n" // <-- LEMBRE-SE DE ALTERAR ESTE EMAIL
                 . "Reply-To: no-reply@" . parse_url(BASE_URL, PHP_URL_HOST) . "\r\n"
                 . "Content-Type: text/plain; charset=UTF-8\r\n"
                 . "MIME-Version: 1.0\r\n"
                 . "X-Mailer: PHP/" . phpversion();
        
        // 6. Tenta enviar o email e define a mensagem de feedback
        if (@mail($to, $subject, $body, $headers)) {
            $_SESSION['admin_message'] = "Email de boas-vindas reenviado com sucesso para {$to}.";
            $_SESSION['admin_status'] = 'success';
        } else {
            $_SESSION['admin_message'] = "Falha ao reenviar o email. Verifique as configurações de email do servidor.";
            $_SESSION['admin_status'] = 'error';
        }
    } else {
        $_SESSION['admin_message'] = "Utilizador com o ID {$user_id_to_notify} não foi encontrado.";
        $_SESSION['admin_status'] = 'error';
    }
} else {
    $_SESSION['admin_message'] = "Requisição inválida.";
    $_SESSION['admin_status'] = 'error';
}

// --- CORREÇÃO APLICADA AQUI ---
// Redireciona de volta para a página de gestão de utilizadores
header('Location: user_management.php');
exit();
?>