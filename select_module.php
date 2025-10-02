<?php
// Chama o session_check para garantir que os dados do utilizador e módulos são carregados
require_once 'session_check.php';
require_once 'config.php';

// Se o utilizador é admin, vai para o dashboard, não para a seleção de módulos.
if ($user['role'] === 'admin') {
    header('Location: dashboard.php');
    exit();
}

// Se o utilizador só tiver UM módulo atribuído, redireciona-o diretamente para lá.
if (count($user['modules']) === 1) {
    $module = $user['modules'][0];
    $path = $module['module_path'] ?? 'index.php?module_id=' . $module['id'];
    $link = strpos($path, 'http') === 0 ? $path : BASE_URL . '/' . ltrim($path, '/');
    header('Location: ' . $link);
    exit();
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Selecionar Módulo - <?php echo APP_NAME; ?></title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/style.css">
</head>
<body class="module-selector-page">
    <header class="header">
        <img src="<?php echo BASE_URL; ?>/images/logo-oficial.png" alt="<?php echo APP_NAME; ?> Logo" class="header-logo">
        <div class="user-menu">
            <span>Olá, <?php echo htmlspecialchars($user['username']); ?>!</span>
            <a href="logout.php">Sair</a>
        </div>
    </header>
    <div class="module-selector-container">
        <h1>Selecione um Módulo para Começar</h1>
        
        <?php if (empty($user['modules'])): ?>
            <p class="no-modules-message">Você ainda não tem acesso a nenhum módulo. Por favor, contacte o administrador.</p>
        <?php else: ?>
            <div class="module-grid">
                <?php foreach ($user['modules'] as $module): ?>
                    <?php
                        // Usa o caminho da base de dados, com um fallback para o sistema antigo.
                        $path = $module['module_path'] ?? 'index.php?module_id=' . $module['id'];
                        // Garante que o link seja absoluto.
                        $link = strpos($path, 'http') === 0 ? $path : BASE_URL . '/' . ltrim($path, '/');
                    ?>
                    <a href="<?php echo $link; ?>" class="module-card">
                        <div class="module-icon">
                            <?php echo $module['icon_svg'] ?: '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect></svg>'; ?>
                        </div>
                        <span class="module-name"><?php echo htmlspecialchars($module['module_name']); ?></span>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>