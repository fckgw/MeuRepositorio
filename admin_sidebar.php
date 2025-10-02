<?php
$current_page = basename($_SERVER['PHP_SELF']);
?>
<aside class="sidebar">
    <div class="sidebar-header">
        <a href="<?php echo BASE_URL; ?>/dashboard.php">
            <img src="<?php echo BASE_URL; ?>/images/logo-oficial.png" alt="<?php echo APP_NAME; ?> Logo" class="sidebar-logo">
        </a>
    </div>
    <nav class="sidebar-nav">
        <ul>
            <li>
                <a href="dashboard.php" class="nav-link <?php echo $current_page == 'dashboard.php' ? 'active' : ''; ?>">
                    <span>Dashboard</span>
                </a>
            </li>
            <li>
                <a href="user_management.php" class="nav-link <?php echo $current_page == 'user_management.php' ? 'active' : ''; ?>">
                    <span>Gestão de Utilizadores</span>
                </a>
            </li>
            <li>
                <a href="module_management.php" class="nav-link <?php echo $current_page == 'module_management.php' ? 'active' : ''; ?>">
                    <span>Gestão de Módulos</span>
                </a>
            </li>
             <li>
                <a href="activity_log.php" class="nav-link <?php echo $current_page == 'activity_log.php' ? 'active' : ''; ?>">
                    <span>Log de Atividades</span>
                </a>
            </li>
        </ul>
    </nav>
    <div class="sidebar-footer">
         <div class="user-info">
            <span class="info-label">Acesso de Administrador</span>
            <span class="info-value"><?php echo htmlspecialchars($user['username']); ?></span>
        </div>
    </div>
</aside>