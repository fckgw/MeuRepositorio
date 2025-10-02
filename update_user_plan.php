<?php
require_once 'session_check.php';
if ($user['role'] !== 'admin') { die("Acesso negado."); }
require_once 'db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['user_id'], $_POST['plan_id'])) {
    $user_id_to_update = $_POST['user_id'];
    $new_plan_id = $_POST['plan_id'];

    $stmt = $conn->prepare("UPDATE users SET plan_id = ? WHERE id = ?");
    $stmt->bind_param("ii", $new_plan_id, $user_id_to_update);
    
    if ($stmt->execute()) {
        $_SESSION['admin_message'] = "Plano do utilizador atualizado com sucesso.";
        $_SESSION['admin_status'] = 'success';

        $stmt_user = $conn->prepare("SELECT u.email, u.username, p.plan_name, p.space_gb FROM users u JOIN storage_plans p ON p.id = ? WHERE u.id = ?");
        $stmt_user->bind_param("ii", $new_plan_id, $user_id_to_update);
        $stmt_user->execute();
        $user_to_notify = $stmt_user->get_result()->fetch_assoc();

        if ($user_to_notify) {
            $to = $user_to_notify['email'];
            $username = $user_to_notify['username'];
            $plan_name = $user_to_notify['plan_name'];
            $space = $user_to_notify['space_gb'] > 0 ? $user_to_notify['space_gb'] . " GB" : "Ilimitado";
            $subject = "O seu Plano foi Atualizado - " . APP_NAME;
            $body = "Olá {$username},\n\nO seu plano no " . APP_NAME . " foi atualizado para: {$plan_name}.\n\n"
                  . "O seu novo limite de armazenamento é: {$space}.\n\n"
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