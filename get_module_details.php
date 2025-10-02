<?php
require_once 'session_check.php';
if ($user['role'] !== 'admin') { die('Acesso negado.'); }
require_once 'db.php';

$moduleId = $_GET['module_id'] ?? 0;
if (empty($moduleId)) {
    header('Content-Type: application/json');
    echo json_encode(['status' => 'error', 'message' => 'ID do módulo em falta.']);
    exit();
}

$stmt = $conn->prepare("SELECT id, module_name, module_path, icon_svg FROM modules WHERE id = ?");
$stmt->bind_param("i", $moduleId);
$stmt->execute();
$result = $stmt->get_result()->fetch_assoc();

if ($result) {
    header('Content-Type: application/json');
    echo json_encode(['status' => 'success', 'data' => $result]);
} else {
    header('Content-Type: application/json');
    echo json_encode(['status' => 'error', 'message' => 'Módulo não encontrado.']);
}
exit();
?>