<?php
require_once 'session_check.php';
if ($user['role'] !== 'admin') { die('Acesso negado.'); }
require_once 'db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['user_id'])) {
    $userId = $_POST['user_id'];
    $modules = $_POST['modules'] ?? []; // Pega os IDs dos módulos marcados

    // Inicia uma transação para garantir a consistência dos dados
    $conn->begin_transaction();
    try {
        // 1. Apaga todos os módulos atuais do utilizador
        $stmt_delete = $conn->prepare("DELETE FROM user_modules WHERE user_id = ?");
        $stmt_delete->bind_param("i", $userId);
        $stmt_delete->execute();
        $stmt_delete->close();

        // 2. Se houver módulos selecionados, insere as novas atribuições
        if (!empty($modules)) {
            $stmt_insert = $conn->prepare("INSERT INTO user_modules (user_id, module_id) VALUES (?, ?)");
            foreach ($modules as $moduleId) {
                // Validação para garantir que é um número
                if (is_numeric($moduleId)) {
                    $stmt_insert->bind_param("ii", $userId, $moduleId);
                    $stmt_insert->execute();
                }
            }
            $stmt_insert->close();
        }
        
        // Confirma a transação
        $conn->commit();
        $_SESSION['admin_message'] = "Módulos do utilizador atualizados com sucesso.";
        $_SESSION['admin_status'] = 'success';

    } catch (mysqli_sql_exception $exception) {
        $conn->rollback(); // Desfaz tudo em caso de erro
        $_SESSION['admin_message'] = "Erro ao atualizar os módulos: " . $exception->getMessage();
        $_SESSION['admin_status'] = 'error';
    }
} else {
    $_SESSION['admin_message'] = "Requisição inválida.";
    $_SESSION['admin_status'] = 'error';
}

// --- CORREÇÃO APLICADA AQUI ---
// Redireciona para o nome de ficheiro correto
header('Location: user_management.php');
exit();
?>