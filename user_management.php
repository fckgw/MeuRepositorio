<?php
require_once 'session_check.php';
if ($user['role'] !== 'admin') { die("Acesso negado."); }
require_once 'db.php';

// SQL OTIMIZADO: Busca utilizadores, planos, e concatena os nomes dos módulos atribuídos.
$users_result = $conn->query(
    "SELECT 
        u.id, u.username, u.email, u.role, u.trial_ends_at, u.status, p.plan_name,
        (SELECT GROUP_CONCAT(m.module_name ORDER BY m.module_name SEPARATOR ', ') 
         FROM user_modules um 
         JOIN modules m ON um.module_id = m.id 
         WHERE um.user_id = u.id) as assigned_modules
    FROM users u 
    LEFT JOIN storage_plans p ON u.plan_id = p.id 
    ORDER BY u.created_at DESC"
);

// Busca todos os planos e módulos para os formulários
$plans_result = $conn->query("SELECT id, plan_name, space_gb FROM storage_plans ORDER BY id");
$plans = $plans_result->fetch_all(MYSQLI_ASSOC);
$modules_result = $conn->query("SELECT id, module_name FROM modules ORDER BY module_name");
$all_modules = $modules_result->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestão de Utilizadores - <?php echo APP_NAME; ?></title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="main-wrapper">
        <?php include 'admin_sidebar.php'; ?>
        <div class="page-content">
            <header class="header">
                <div class="header-placeholder"></div>
                <div class="user-menu">
                    <span>Olá, <?php echo htmlspecialchars($user['username']); ?>! (Admin)</span>
                    <a href="index.php">Ver o meu Driver</a>
                    <a href="logout.php">Sair</a>
                </div>
            </header>
            <main class="admin-panel">
                <h2>Gestão de Utilizadores</h2>
                <?php
                if (isset($_SESSION['admin_message'])) {
                    $message = $_SESSION['admin_message']; $status = $_SESSION['admin_status'];
                    echo "<div class='alert $status' style='margin-bottom: 20px;'>" . nl2br($message) . "</div>";
                    unset($_SESSION['admin_message']); unset($_SESSION['admin_status']);
                }
                ?>
                <div class="table-wrapper">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>Utilizador</th>
                                <th>Plano</th>
                                <th>Estado</th>
                                <th>Módulos Atribuídos</th>
                                <th style="text-align: center;">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($row = $users_result->fetch_assoc()): ?>
                            <tr>
                                <td>
                                    <strong><?php echo htmlspecialchars($row['username']); ?></strong><br>
                                    <small><?php echo htmlspecialchars($row['email']); ?></small>
                                </td>
                                <td>
                                    <!-- Formulário de Plano -->
                                    <form action="update_user_plan.php" method="POST" class="admin-form">
                                        <input type="hidden" name="user_id" value="<?php echo $row['id']; ?>">
                                        <select name="plan_id" <?php if($row['role'] == 'admin') echo 'disabled'; ?>>
                                            <?php foreach ($plans as $plan): ?>
                                                <option value="<?php echo $plan['id']; ?>" <?php if ($row['plan_id'] == $plan['id']) echo 'selected'; ?>>
                                                    <?php echo htmlspecialchars($plan['plan_name']); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                        <?php if($row['role'] != 'admin'): ?>
                                            <button type="submit" class="btn btn-update" title="Atualizar Plano">✓</button>
                                        <?php endif; ?>
                                    </form>
                                </td>
                                <td>
                                    <!-- Formulário de Estado -->
                                    <?php if($row['role'] != 'admin'): ?>
                                    <form action="update_user_status.php" method="POST" class="admin-form">
                                        <input type="hidden" name="user_id" value="<?php echo $row['id']; ?>">
                                        <select name="status">
                                            <option value="active" <?php if ($row['status'] == 'active') echo 'selected'; ?>>Ativo</option>
                                            <option value="blocked" <?php if ($row['status'] == 'blocked') echo 'selected'; ?>>Bloqueado</option>
                                        </select>
                                        <button type="submit" class="btn btn-status" title="Mudar Estado">✓</button>
                                    </form>
                                    <?php else: ?>
                                        <span class="status-ok">Ativo</span>
                                    <?php endif; ?>
                                </td>
                                <td class="modules-cell">
                                    <!-- LÓGICA DAS ETIQUETAS DE MÓDULOS -->
                                    <?php if (!empty($row['assigned_modules'])): ?>
                                        <?php $modules_array = explode(', ', $row['assigned_modules']); ?>
                                        <div class="module-tags">
                                            <?php foreach ($modules_array as $module_name): ?>
                                                <span class="module-tag"><?php echo htmlspecialchars($module_name); ?></span>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php else: ?>
                                        <span class="no-modules">Nenhum</span>
                                    <?php endif; ?>
                                </td>
                                <td class="actions-cell">
                                    <button class="btn btn-manage-modules" data-userid="<?php echo $row['id']; ?>" data-username="<?php echo htmlspecialchars($row['username']); ?>" title="Gerir Módulos">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16"><path d="M.5 4v8.5A.5.5 0 0 0 1 13h14a.5.5 0 0 0 .5-.5V4H.5zM1 3a.5.5 0 0 1 .5-.5h13a.5.5 0 0 1 .5.5v.5h-14V3z"/></svg>
                                    </button>
                                    <form action="resend_welcome_email.php" method="POST" class="admin-form" style="display:inline-block;">
                                        <input type="hidden" name="user_id" value="<?php echo $row['id']; ?>">
                                        <button type="submit" class="btn btn-resend" title="Reenviar Email de Boas-vindas">✉️</button>
                                    </form>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </main>
        </div>
    </div>

    <!-- Modal para Gerir Módulos -->
    <div id="manage-modules-modal" class="modal">
        <div class="modal-content-form">
            <span class="close-modal">&times;</span>
            <h3>Gerir Módulos</h3>
            <p>Atribuir módulos para: <strong id="modal-username"></strong></p>
            <form id="manage-modules-form" action="update_user_modules.php" method="POST">
                <input type="hidden" name="user_id" id="modal-user-id">
                <div class="modules-checkbox-list">
                    <?php foreach ($all_modules as $module): ?>
                        <label>
                            <input type="checkbox" name="modules[]" value="<?php echo $module['id']; ?>">
                            <?php echo htmlspecialchars($module['module_name']); ?>
                        </label>
                    <?php endforeach; ?>
                </div>
                <button type="submit" class="btn-submit">Guardar Alterações</button>
            </form>
        </div>
    </div>

    <script src="<?php echo BASE_URL; ?>/admin_script.js"></script>
</body>
</html>