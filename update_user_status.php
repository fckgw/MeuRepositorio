<?php
require_once 'session_check.php';
if ($user['role'] !== 'admin') { die("Acesso negado."); }
require_once 'db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['user_id'], $_POST['status'])) {
    $user_id_to_update = $_POST['user_id'];
    $new_status = $_POST['status'];

    if ($new_status !== 'active' && $new_status !== 'blocked') { /* ... erro ... */ }

    $stmt = $conn->prepare("UPDATE users SET status = ? WHERE id = ?");
    $stmt->bind_param("si", $new_status, $user_id_to_update);
    
    if ($stmt->execute()) {
        $_SESSION['admin_message'] = "Estado do utilizador atualizado com sucesso.";
        $_SESSION['admin_status'] = 'success';
        
        $stmt_user = $conn->prepare("SELECT email, username FROM users WHERE id = ?");
        $stmt_user->bind_param("i", $user_id_to_update);
        $stmt_user->execute();
        $user_to_notify = $stmt_user->get_result()->fetch_assoc();

        if ($user_to_notify) {
            $to = $user_to_notify['email'];
            $username = $user_to_notify['username'];
            $subject = "Atualização de Estado da sua Conta - " . APP_NAME;
            $status_text = $new_status == 'active' ? 'ATIVA' : 'BLOQUEADA';
            $body = "Olá {$username},\n\nO estado da sua conta no " . APP_NAME . " foi alterado para: {$status_text}.\n\n"
                  . "Se a sua conta foi bloqueada, poderá não conseguir aceder ao serviço. Por favor, contacte o suporte para mais informações.\n\n"
                  . "Obrigado,\nA Equipa BDSoft";
                  
            // --- CORREÇÃO APLICADA AQUI ---
            $headers = "From: no-reply@" . parse_url(BASE_URL, PHP_URL_HOST) . "\r\n"
                     . "Bcc: seu-email-de-admin@bdsoft.com.br" . "\r\n"
                     . "Reply-To: no-reply@" . parse_url(BASE_URL, PHP_URL_HOST) . "\r\n"
                     . "Content-Type: text/plain; charset=UTF-8\r\n"
                     . "MIME-Version: 1.0\r\n"
                     . "X-Mailer: PHP/" . phpversion();

            @mail($to, $subject, $body, $headers);
        }
    } else { /* ... erro ... */ }
}
header('Location: admin.php');
exit();
?>