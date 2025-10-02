<?php
// Garante que a sessão é iniciada apenas uma vez.
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 1. Se não houver 'user_id' na sessão, o utilizador não está autenticado.
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// 2. Se o utilizador estiver autenticado, carrega os seus dados completos.
require_once 'db.php';

// Faz um JOIN para obter os dados do utilizador e do seu plano de armazenamento.
$stmt_user = $conn->prepare(
    "SELECT u.id, u.username, u.email, u.role, u.trial_ends_at, u.last_login_at, u.status, p.plan_name, p.space_gb 
     FROM users u 
     LEFT JOIN storage_plans p ON u.plan_id = p.id 
     WHERE u.id = ?"
);
$stmt_user->bind_param("i", $_SESSION['user_id']);
$stmt_user->execute();
$user = $stmt_user->get_result()->fetch_assoc();
$stmt_user->close();

// Segurança: se o ID na sessão não corresponder a um utilizador válido, faz logout.
if (!$user) {
    session_destroy();
    header('Location: login.php');
    exit();
}

// 3. Carrega os módulos aos quais o utilizador tem acesso.
$user['modules'] = [];
$stmt_modules = $conn->prepare(
    "SELECT m.id, m.module_name, m.module_path, m.icon_svg 
     FROM user_modules um 
     JOIN modules m ON um.module_id = m.id 
     WHERE um.user_id = ? 
     ORDER BY m.module_name ASC"
);
$stmt_modules->bind_param("i", $_SESSION['user_id']);
$stmt_modules->execute();
$modules_result = $stmt_modules->get_result();
while ($row = $modules_result->fetch_assoc()) {
    $user['modules'][] = $row;
}
$stmt_modules->close();

// 4. Verifica o estado da conta e o período de trial.
if ($user['status'] === 'blocked') {
    session_destroy();
    die("A sua conta foi bloqueada. Por favor, contacte o suporte.");
}

if ($user['role'] !== 'admin' && strtotime($user['trial_ends_at']) < time()) {
    die("O seu período de trial expirou. Por favor, contacte o suporte para continuar a usar o " . APP_NAME . ".");
}
?>