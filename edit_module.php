<?php
require_once 'session_check.php';
if ($user['role'] !== 'admin') { die("Acesso negado."); }
require_once 'db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && !empty($_POST['module_id']) && !empty(trim($_POST['module_name']))) {
    $module_id = $_POST['module_id'];
    $module_name = trim($_POST['module_name']);
    $module_path = trim($_POST['module_path']);
    $icon_svg = $_POST['icon_svg'] ?? null;

    $stmt = $conn->prepare("UPDATE modules SET module_name = ?, module_path = ?, icon_svg = ? WHERE id = ?");
    $stmt->bind_param("sssi", $module_name, $module_path, $icon_svg, $module_id);

    if ($stmt->execute()) {
        $_SESSION['admin_message'] = "Módulo '{$module_name}' atualizado com sucesso.";
        $_SESSION['admin_status'] = 'success';
    } else {
        $_SESSION['admin_message'] = "Erro ao atualizar o módulo.";
        $_SESSION['admin_status'] = 'error';
    }
} else {
    $_SESSION['admin_message'] = "Dados inválidos.";
    $_SESSION['admin_status'] = 'error';
}

header('Location: module_management.php');
exit();
?>