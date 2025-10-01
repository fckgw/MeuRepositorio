<?php
require_once 'session_check.php';
if ($user['role'] !== 'admin') {
    die("Acesso negado.");
}
require_once 'db.php';
$result = $conn->query("SELECT id, username, email, role, trial_ends_at, created_at FROM users ORDER BY created_at DESC");
?>
<!DOCTYPE html>
<html>
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
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Utilizador</th>
                        <th>Email</th>
                        <th>Função</th>
                        <th>Trial Termina em</th>
                        <th>Estado do Trial</th>
                        <th>Registado em</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $row['id']; ?></td>
                        <td><?php echo htmlspecialchars($row['username']); ?></td>
                        <td><?php echo htmlspecialchars($row['email']); ?></td>
                        <td><?php echo $row['role']; ?></td>
                        <td><?php echo date('d/m/Y H:i', strtotime($row['trial_ends_at'])); ?></td>
                        <td>
                            <?php if (strtotime($row['trial_ends_at']) > time()): ?>
                                <span class="status-active">Ativo</span>
                            <?php else: ?>
                                <span class="status-expired">Expirado</span>
                            <?php endif; ?>
                        </td>
                        <td><?php echo date('d/m/Y', strtotime($row['created_at'])); ?></td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </main>
    </div>
</body>
</html>```

#### **8. `ftp_functions.php` (Atualizado)**
As funções agora recebem o caminho do utilizador como parâmetro.

```php
<?php
function get_used_space($user_root_path) {
    // ...
    $used_space_bytes = calculate_directory_size($conn_id, $user_root_path);
    // ...
}
function listarArquivosFTP($path) {
    // Esta função já recebe o caminho completo, então não precisa de grandes mudanças
    // ...
}
// ... As outras funções também recebem os caminhos completos, então continuam a funcionar.
?>