<?php
require_once 'session_check.php';
if ($user['role'] !== 'admin') { die("Acesso negado."); }

require_once 'db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && !empty($_POST['module_id'])) {
    $module_id = $_POST['module_id'];

    // Para segurança, apagamos primeiro as associações deste módulo com utilizadores
    $stmt_assoc = $conn->prepare("DELETE FROM user_modules WHERE module_id = ?");
    $stmt_assoc->bind_param("i", $module_id);
    $stmt_assoc->execute();
    $stmt_assoc->close();
    
    // Depois, apagamos o módulo em si
    $stmt = $conn->prepare("DELETE FROM modules WHERE id = ?");
    $stmt->bind_param("i", $module_id);

    if ($stmt->execute()) {
        $_SESSION['admin_message'] = "Módulo apagado com sucesso.";
        $_SESSION['admin_status'] = 'success';
    } else {
        $_SESSION['admin_message'] = "Erro ao apagar o módulo: " . $stmt->error;
        $_SESSION['admin_status'] = 'error';
    }
} else {
    $_SESSION['admin_message'] = "ID do módulo em falta.";
    $_SESSION['admin_status'] = 'error';
}

header('Location: module_management.php');
exit();
?>