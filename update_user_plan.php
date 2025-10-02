<?php
require_once 'session_check.php';
if ($user['role'] !== 'admin') { die("Acesso negado."); }
require_once 'db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['user_id'], $_POST['plan_id'])) {
    // ... (lógica de atualização do plano e envio de email) ...

    // (O código de envio de email continua o mesmo)
} else {
    $_SESSION['admin_message'] = "Requisição inválida.";
    $_SESSION['admin_status'] = 'error';
}

// --- CORREÇÃO APLICADA AQUI ---
header('Location: user_management.php');
exit();
?>