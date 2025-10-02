<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
if (!isset($_SESSION['user_id'])) { header('Location: login.php'); exit(); }
require_once 'db.php';
$stmt = $conn->prepare(
    "SELECT u.id, u.username, u.email, u.role, u.trial_ends_at, u.status, p.plan_name, p.space_gb 
     FROM users u 
     LEFT JOIN storage_plans p ON u.plan_id = p.id 
     WHERE u.id = ?"
);
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
if (!$user) { session_destroy(); header('Location: login.php'); exit(); }

// Impede o acesso se a conta estiver bloqueada
if ($user['status'] === 'blocked') {
    session_destroy();
    die("A sua conta foi bloqueada. Por favor, contacte o suporte.");
}

if ($user['role'] !== 'admin' && strtotime($user['trial_ends_at']) < time()) {
    die("O seu período de trial expirou. Por favor, contacte o suporte.");
}
?>