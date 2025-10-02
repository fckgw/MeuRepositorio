<?php
require_once 'session_check.php';
if ($user['role'] !== 'admin') { die('Acesso negado.'); }
require_once 'db.php';

$userId = $_GET['user_id'] ?? 0;
if (empty($userId)) {
    header('Content-Type: application/json');
    echo json_encode(['status' => 'error', 'message' => 'ID do utilizador em falta.']);
    exit();
}

$stmt = $conn->prepare("SELECT module_id FROM user_modules WHERE user_id = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
$modules = [];
while ($row = $result->fetch_assoc()) {
    $modules[] = $row['module_id'];
}

header('Content-Type: application/json');
echo json_encode(['status' => 'success', 'modules' => $modules]);
exit();
?>