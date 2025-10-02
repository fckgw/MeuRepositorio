<?php
require_once 'session_check.php';
if ($user['role'] !== 'admin') { die("Acesso negado."); }
require_once 'db.php';

$logs_result = $conn->query(
    "SELECT l.*, u.username 
     FROM activity_logs l 
     LEFT JOIN users u ON l.user_id = u.id 
     ORDER BY l.created_at DESC LIMIT 100" // Limita a 100 para performance
);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Log de Atividades - <?php echo APP_NAME; ?></title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="main-wrapper">
        <?php include 'admin_sidebar.php'; ?>
        <div class="page-content">
            <header class="header"> <!-- ... --> </header>
            <main class="admin-panel">
                <h2>Log de Atividades do Sistema</h2>
                <div class="table-wrapper">
                    <table class="admin-table">
                        <thead><tr><th>Data/Hora</th><th>Utilizador</th><th>Ação</th><th>Detalhes</th><th>IP</th></tr></thead>
                        <tbody>
                            <?php while($row = $logs_result->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo date('d/m/Y H:i:s', strtotime($row['created_at'])); ?></td>
                                <td><?php echo htmlspecialchars($row['username'] ?: 'Sistema/Anónimo'); ?></td>
                                <td><?php echo htmlspecialchars($row['action']); ?></td>
                                <td><?php echo htmlspecialchars($row['details']); ?></td>
                                <td><?php echo htmlspecialchars($row['ip_address']); ?></td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </main>
        </div>
    </div>
</body>
</html>