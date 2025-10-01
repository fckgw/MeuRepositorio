<?php
// Garante que a sessão é iniciada apenas uma vez.
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 1. Se não houver 'user_id' na sessão, o utilizador não está autenticado.
// Redireciona para a página de login e termina a execução do script.
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// 2. Se o utilizador estiver autenticado, carrega os seus dados.
require_once 'db.php';

$stmt = $conn->prepare("SELECT id, username, email, role, trial_ends_at FROM users WHERE id = ?");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

// 3. Se o ID da sessão for inválido (utilizador apagado), destrói a sessão e redireciona.
if (!$user) {
    session_destroy();
    header('Location: login.php');
    exit();
}

// 4. Verifica se o período de trial expirou (não se aplica a administradores).
if ($user['role'] !== 'admin' && strtotime($user['trial_ends_at']) < time()) {
    // Para a execução e mostra uma mensagem. No futuro, pode redirecionar para uma página de subscrição.
    die("O seu período de trial de 7 dias expirou. Por favor, contacte o suporte para continuar a usar o " . APP_NAME . ".");
}
?>