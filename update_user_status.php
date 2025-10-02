<?php
require_once 'session_check.php';
if ($user['role'] !== 'admin') { die("Acesso negado."); }
require_once 'db.php';
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['user_id'], $_POST['status'])) {
    $user_id_to_update = $_POST['user_id'];
    $new_status = $_POST['status'];
    if ($new_status !== 'active' && $new_status !== 'blocked') { /* ... */ }
    $stmt = $conn->prepare("UPDATE users SET status = ? WHERE id = ?");
    $stmt->bind_param("si", $new_status, $user_id_to_update);
    if ($stmt->execute()) {
        $_SESSION['admin_message'] = "Estado do utilizador atualizado com sucesso.";
        $_SESSION['admin_status'] = 'success';
        // ... (lógica de envio de email) ...
    } else {
        $_SESSION['admin_message'] = "Erro ao atualizar o estado do utilizador.";
        $_SESSION['admin_status'] = 'error';
    }
} else {
    $_SESSION['admin_message'] = "Requisição inválida.";
    $_SESSION['admin_status'] = 'error';
}
header('Location: user_management.php');
exit();
?>