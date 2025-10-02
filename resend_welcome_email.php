<?php
require_once 'session_check.php';
if ($user['role'] !== 'admin') { die("Acesso negado."); }
require_once 'db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['user_id'])) {
    $user_id_to_notify = $_POST['user_id'];
    $stmt = $conn->prepare("SELECT email, username, trial_ends_at FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id_to_notify);
    $stmt->execute();
    $user_to_notify = $stmt->get_result()->fetch_assoc();

    if ($user_to_notify) {
        $to = $user_to_notify['email'];
        $username = $user_to_notify['username'];
        $trial_ends_at = $user_to_notify['trial_ends_at'];
        $trial_days = TRIAL_DAYS;
        
        $subject = "Bem-vindo ao " . APP_NAME . " (Reenvio de Informações)";
        $body = "Olá {$username},\n\nSegue uma cópia das informações da sua conta no " . APP_NAME . ".\n\n"
              . "Credenciais de Acesso:\n"
              . "Email: {$to}\n\n"
              . "O seu período de trial de {$trial_days} dias termina em: " . date('d/m/Y', strtotime($trial_ends_at)) . ".\n\n"
              . "Obrigado,\nA Equipa BDSoft";
              
        // --- CORREÇÃO APLICADA AQUI ---
        $headers = "From: no-reply@" . parse_url(BASE_URL, PHP_URL_HOST) . "\r\n"
                 . "Bcc: seu-souzafelipe@bdsoft.com.br" . "\r\n"
                 . "Reply-To: no-reply@" . parse_url(BASE_URL, PHP_URL_HOST) . "\r\n"
                 . "Content-Type: text/plain; charset=UTF-8\r\n"
                 . "MIME-Version: 1.0\r\n"
                 . "X-Mailer: PHP/" . phpversion();
        
        if (@mail($to, $subject, $body, $headers)) {
            $_SESSION['admin_message'] = "Email de boas-vindas reenviado para {$to}.";
            $_SESSION['admin_status'] = 'success';
        } else {
            $_SESSION['admin_message'] = "Falha ao reenviar o email.";
            $_SESSION['admin_status'] = 'error';
        }
    } else {
        $_SESSION['admin_message'] = "Utilizador não encontrado.";
        $_SESSION['admin_status'] = 'error';
    }
}
header('Location: admin.php');
exit();
?>