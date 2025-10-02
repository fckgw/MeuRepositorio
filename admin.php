<?php
require_once 'session_check.php';
if ($user['role'] !== 'admin') { die("Acesso negado."); }
require_once 'db.php';

// Busca todos os utilizadores e todos os planos
$users_result = $conn->query("SELECT u.id, u.username, u.email, u.role, u.trial_ends_at, u.created_at, u.plan_id, u.status, p.plan_name FROM users u LEFT JOIN storage_plans p ON u.plan_id = p.id ORDER BY u.created_at DESC");
$plans_result = $conn->query("SELECT id, plan_name, space_gb FROM storage_plans ORDER BY id");
$plans = $plans_result->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <title>Painel de Admin - <?php echo APP_NAME; ?></title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <header class="header">
            <strong>Painel de Admin</strong>
            <div class="user-menu">
                <a href="index.php">Voltar ao Driver</a>
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
            <table>
                <thead>
                    <tr>
                        <th>Utilizador</th>
                        <th>Plano Atual</th>
                        <th>Estado da Conta</th>
                        <th>Trial</th>
                        <th>Ações</th>
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
                            <form action="update_user_plan.php" method="POST" class="admin-form">
                                <input type="hidden" name="user_id" value="<?php echo $row['id']; ?>">
                                <select name="plan_id">
                                    <?php foreach ($plans as $plan): ?>
                                        <option value="<?php echo $plan['id']; ?>" <?php if ($row['plan_id'] == $plan['id']) echo 'selected'; ?>>
                                            <?php echo htmlspecialchars($plan['plan_name']) . ' (' . ($plan['space_gb'] > 0 ? $plan['space_gb'].'GB' : 'Ilimitado') . ')'; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <button type="submit">Atualizar Plano</button>
                            </form>
                        </td>
                        <td>
                            <form action="update_user_status.php" method="POST" class="admin-form">
                                <input type="hidden" name="user_id" value="<?php echo $row['id']; ?>">
                                <select name="status">
                                    <option value="active" <?php if ($row['status'] == 'active') echo 'selected'; ?>>Ativo</option>
                                    <option value="blocked" <?php if ($row['status'] == 'blocked') echo 'selected'; ?>>Bloqueado</option>
                                </select>
                                <button type="submit">Mudar Estado</button>
                            </form>
                        </td>
                        <td>
                            <?php if ($row['role'] !== 'admin'): ?>
                                <?php if (strtotime($row['trial_ends_at']) > time()): ?>
                                    <span class="status-active">Ativo até <?php echo date('d/m/Y', strtotime($row['trial_ends_at'])); ?></span>
                                <?php else: ?>
                                    <span class="status-expired">Expirado</span>
                                <?php endif; ?>
                            <?php else: ?>
                                <span>- N/A -</span>
                            <?php endif; ?>
                        </td>
                        <td class="actions-cell">
                             <form action="resend_welcome_email.php" method="POST" class="admin-form">
                                <input type="hidden" name="user_id" value="<?php echo $row['id']; ?>">
                                <button type="submit" class="resend-btn" title="Reenviar email de boas-vindas">Reenviar Email</button>
                             </form>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </main>
    </div>
</body>
</html>