<?php
require_once 'session_check.php';
if ($user['role'] !== 'admin') {
    header('Content-Type: application/json');
    echo json_encode(['status' => 'error', 'message' => 'Acesso negado.']);
    exit();
}
require_once 'db.php';

$sql = "SELECT m.module_name, COUNT(um.user_id) as user_count 
        FROM modules m
        LEFT JOIN user_modules um ON m.id = um.module_id
        GROUP BY m.id
        ORDER BY m.module_name";

$result = $conn->query($sql);
$data = ['labels' => [], 'values' => []];

while ($row = $result->fetch_assoc()) {
    $data['labels'][] = $row['module_name'];
    $data['values'][] = (int)$row['user_count'];
}

header('Content-Type: application/json');
echo json_encode(['status' => 'success', 'data' => $data]);
exit();
?>