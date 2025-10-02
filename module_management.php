<?php
require_once 'session_check.php';
if ($user['role'] !== 'admin') { die("Acesso negado."); }
require_once 'db.php';

$modules_result = $conn->query("SELECT * FROM modules ORDER BY module_name");
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestão de Módulos - <?php echo APP_NAME; ?></title>
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
                    <a href="logout.php">Sair</a>
                </div>
            </header>
            <main class="admin-panel">
                <div class="admin-header">
                    <h2>Gestão de Módulos</h2>
                    <button class="btn-submit" id="btn-show-create-form">Criar Novo Módulo</button>
                </div>

                <?php
                if (isset($_SESSION['admin_message'])) {
                    $message = $_SESSION['admin_message']; $status = $_SESSION['admin_status'];
                    echo "<div class='alert $status' style='margin-bottom: 20px;'>" . nl2br($message) . "</div>";
                    unset($_SESSION['admin_message']); unset($_SESSION['admin_status']);
                }
                ?>

                <!-- Formulário de Criação (Inicialmente Oculto) -->
                <div class="admin-card" id="create-module-card" style="display: none;">
                    <h3>Criar Novo Módulo</h3>
                    <form action="create_module.php" method="POST" class="admin-form-stacked">
                        <div class="form-group">
                            <label for="module_name">Nome do Módulo</label>
                            <input type="text" id="module_name" name="module_name" placeholder="Ex: Meu Financeiro" required>
                        </div>
                        <div class="form-group">
                            <label for="module_path">Caminho do Módulo</label>
                            <input type="text" id="module_path" name="module_path" placeholder="Ex: /MeuFinanceiro/" required>
                        </div>
                        <div class="form-group">
                            <label for="icon_svg">Código SVG do Ícone (Opcional)</label>
                            <textarea id="icon_svg" name="icon_svg" placeholder="Cole o código <svg>...</svg> aqui"></textarea>
                        </div>
                        <button type="submit" class="btn-submit">Criar Módulo</button>
                    </form>
                </div>

                <h3>Módulos Existentes</h3>
                <div class="table-wrapper">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Ícone</th>
                                <th>Nome do Módulo</th>
                                <th>Caminho de Acesso</th>
                                <th style="text-align: center;">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($modules_result->num_rows > 0): ?>
                                <?php while($row = $modules_result->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo $row['id']; ?></td>
                                    <td class="module-icon-preview"><?php echo $row['icon_svg'] ?: '-'; ?></td>
                                    <td><strong><?php echo htmlspecialchars($row['module_name']); ?></strong></td>
                                    <td><code><?php echo htmlspecialchars($row['module_path']); ?></code></td>
                                    <td class="actions-cell">
                                        <?php
                                            $path = $row['module_path'];
                                            $link = strpos($path, 'http') === 0 ? $path : BASE_URL . '/' . ltrim($path, '/');
                                        ?>
                                        <a href="<?php echo $link; ?>" target="_blank" class="btn btn-view" title="Aceder ao Módulo">👁️</a>
                                        <button class="btn btn-edit btn-edit-module" data-moduleid="<?php echo $row['id']; ?>" title="Editar Módulo">✏️</button>
                                        <form action="delete_module.php" method="POST" class="admin-form" style="display:inline-block;">
                                            <input type="hidden" name="module_id" value="<?php echo $row['id']; ?>">
                                            <button type="submit" class="btn btn-delete" title="Apagar Módulo" onclick="return confirm('Tem a certeza que quer apagar este módulo? Esta ação não pode ser desfeita.');">🗑️</button>
                                        </form>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr><td colspan="5" style="text-align:center;">Nenhum módulo criado.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </main>
        </div>
    </div>

    <!-- Modal para Editar Módulo (sem alterações) -->
    <div id="edit-module-modal" class="modal">
        <!-- ... (o seu modal de edição continua aqui) ... -->
    </div>

    <!-- O admin_script.js agora é incluído aqui -->
    <script src="<?php echo BASE_URL; ?>/admin_script.js"></script>
</body>
</html>