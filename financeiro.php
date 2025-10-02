<?php
// O index.php já chama o session_check.php, então o utilizador está autenticado.
?>
<!DOCTYPE html>
<html>
<head>
    <title>Meu Financeiro - <?php echo APP_NAME; ?></title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/style.css">
</head>
<body>
    <div class="main-wrapper">
        <?php include 'main_sidebar.php'; ?>
        <div class="page-content">
            <header class="header">
                <div class="user-menu"><span>Olá, <?php echo htmlspecialchars($user['username']); ?>!</span><a href="logout.php">Sair</a></div>
            </header>
            <main class="admin-panel">
                <h1>Módulo: Meu Financeiro</h1>
                <p>O conteúdo deste módulo será implementado no futuro.</p>
            </main>
        </div>
    </div>
</body>
</html>