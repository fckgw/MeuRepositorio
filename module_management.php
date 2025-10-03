<?php
require_once 'session_check.php';
if ($user['role'] !== 'admin') { die("Acesso negado."); }
require_once 'db.php';

$modules_result = $conn->query("SELECT * FROM modules ORDER BY module_name");
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF--8">
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
                    <button class="btn-submit" id="btn-show-create-modal">Criar Novo Módulo</button>
                </div>

                <?php
                if (isset($_SESSION['admin_message'])) {
                    $message = $_SESSION['admin_message']; $status = $_SESSION['admin_status'];
                    echo "<div class='alert $status' style='margin-bottom: 20px;'>" . nl2br($message) . "</div>";
                    unset($_SESSION['admin_message']); unset($_SESSION['admin_status']);
                }
                ?>
                
                <div class="module-management-grid">
                    <?php if ($modules_result->num_rows > 0): ?>
                        <?php while($row = $modules_result->fetch_assoc()): ?>
                        <div class="module-manage-card">
                            <div class="module-icon-preview small"><?php echo $row['icon_svg'] ?: '<span>-</span>'; ?></div>
                            <div class="module-manage-info">
                                <strong><?php echo htmlspecialchars($row['module_name']); ?></strong>
                                <small>Caminho: <code><?php echo htmlspecialchars($row['module_path']); ?></code></small>
                            </div>
                            <div class="module-manage-actions">
                                <?php
                                    $path = $row['module_path'];
                                    $link = strpos($path, 'http') === 0 ? $path : BASE_URL . '/' . ltrim($path, '/');
                                ?>
                                <a href="<?php echo $link; ?>" target="_blank" class="btn btn-icon btn-view" title="Aceder ao Módulo">👁️</a>
                                <button class="btn btn-icon btn-edit btn-edit-module" data-moduleid="<?php echo $row['id']; ?>" title="Editar Módulo">✏️</button>
                                <form action="delete_module.php" method="POST" style="display:inline-block;">
                                    <input type="hidden" name="module_id" value="<?php echo $row['id']; ?>">
                                    <button type="submit" class="btn btn-icon btn-delete" title="Apagar Módulo" onclick="return confirm('Tem a certeza? Apagar um módulo não apaga a sua pasta, mas remove o acesso.');">🗑️</button>
                                </form>
                            </div>
                        </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <p>Nenhum módulo criado ainda.</p>
                    <?php endif; ?>
                </div>
            </main>
        </div>
    </div>

    <!-- Modal para Criar/Editar Módulo -->
    <div id="module-modal" class="modal">
        <div class="modal-content-form">
            <span class="close-modal">&times;</span>
            <h3 id="modal-title">Criar Novo Módulo</h3>
            <form id="module-form" action="create_module.php" method="POST" class="admin-form-stacked">
                <input type="hidden" name="module_id" id="edit-module-id">
                <div class="form-group">
                    <label for="module_name">Nome do Módulo</label>
                    <input type="text" id="edit-module-name" name="module_name" placeholder="Ex: Meu Financeiro" required>
                </div>
                <div class="form-group">
                    <label for="module_path">Caminho do Módulo (URL)</label>
                    <input type="text" id="edit-module-path" name="module_path" placeholder="Ex: /Financeiro/" required>
                </div>
                <div class="form-group">
                    <label for="icon_svg">Código SVG do Ícone (Opcional)</label>
                    <textarea id="edit-module-svg" name="icon_svg" placeholder="Cole o código <svg>...</svg> aqui"></textarea>
                </div>
                <button type="submit" class="btn-submit">Guardar</button>
            </form>
        </div>
    </div>

    <script src="<?php echo BASE_URL; ?>/admin_script.js"></script>
</body>
</html>