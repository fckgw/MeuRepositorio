<?php
require_once 'session_check.php';
if ($user['role'] !== 'admin') { die("Acesso negado."); }
require_once 'db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && !empty($_POST['module_id'])) {
    $module_id = $_POST['module_id'];

    $stmt = $conn->prepare("DELETE FROM modules WHERE id = ?");
    $stmt->bind_param("i", $module_id);

    if ($stmt->execute()) {
        $_SESSION['admin_message'] = "Módulo apagado com sucesso.";
        $_SESSION['admin_status'] = 'success';
    } else {
        $_SESSION['admin_message'] = "Erro ao apagar o módulo. Verifique se não há utilizadores atribuídos a ele.";
        $_SESSION['admin_status'] = 'error';
    }
} else {
    $_SESSION['admin_message'] = "ID do módulo em falta.";
    $_SESSION['admin_status'] = 'error';
}

header('Location: module_management.php');
exit();
?>