<?php
$current_module_id = $_GET['module_id'] ?? 0;
?>
<aside class="sidebar">
    <div class="sidebar-header">
        <a href="<?php echo BASE_URL; ?>/select_module.php">
            <img src="<?php echo BASE_URL; ?>/images/logo-oficial.png" alt="<?php echo APP_NAME; ?> Logo" class="sidebar-logo">
        </a>
        <button id="sidebar-toggle" title="Minimizar Menu">➔</button>
    </div>
    <nav class="sidebar-nav">
        <ul>
            <?php foreach ($user['modules'] as $module): ?>
                <?php
                    $is_active = ($module['id'] == $current_module_id);
                    // --- LÓGICA DE LINK CORRIGIDA ---
                    // Todos os módulos agora apontam para o roteador index.php com o seu ID
                    $link = BASE_URL . '/index.php?module_id=' . $module['id'];
                ?>
                <li>
                    <a href="<?php echo $link; ?>" class="nav-link <?php echo $is_active ? 'active' : ''; ?>" title="<?php echo htmlspecialchars($module['module_name']); ?>">
                        <?php echo $module['icon_svg'] ?: '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect></svg>'; ?>
                        <span><?php echo htmlspecialchars($module['module_name']); ?></span>
                    </a>
                </li>
            <?php endforeach; ?>
        </ul>
    </nav>
    <div class="sidebar-footer">
         <div class="user-info">
            <span class="info-label">Último Login:</span>
            <span class="info-value">
                <?php echo $user['last_login_at'] ? date('d/m/Y H:i', strtotime($user['last_login_at'])) : 'Primeiro login'; ?>
            </span>
        </div>
        <div class="storage-info">
            <div class="pie-chart" style="--p:<?php echo $percentage_used; ?>"> <?php echo $is_unlimited ? '∞' : $percentage_used . '%'; ?> </div>
            <div class="storage-text">
                <strong>Espaço em Disco</strong>
                <span><?php echo $is_unlimited ? 'Espaço Ilimitado' : "$used_space_gb GB de $total_space_gb GB usados"; ?></span>
            </div>
        </div>
    </div>
</aside>