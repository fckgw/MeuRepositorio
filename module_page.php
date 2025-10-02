<?php
require_once 'session_check.php';
require_once 'config.php';

$module_id = $_GET['id'] ?? 0;
$module_name = 'Módulo Desconhecido';

// Busca o nome do módulo na base de dados para exibir
foreach ($user['modules'] as $module) {
    if ($module['id'] == $module_id) {
        $module_name = $module['module_name'];
        break;
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($module_name); ?> - <?php echo APP_NAME; ?></title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/style.css">
</head>
<body>
    <div class="main-wrapper">
        <!-- Inclui o menu lateral, que agora mostrará este módulo como ativo -->
        <?php include 'main_sidebar.php'; ?>

        <div class="page-content">
            <header class="header">
                <div class="header-placeholder"></div>
                <div class="user-menu">
                    <span>Olá, <?php echo htmlspecialchars($user['username']); ?>!</span>
                    <a href="logout.php">Sair</a>
                </div>
            </header>
            <main class="admin-panel">
                <h1><?php echo htmlspecialchars($module_name); ?></h1>
                <p>Esta é a página para o módulo selecionado. O conteúdo específico deste módulo será implementado aqui no futuro.</p>
                <a href="select_module.php">Voltar à seleção de módulos</a>
            </main>
        </div>
    </div>
</body>
</html>