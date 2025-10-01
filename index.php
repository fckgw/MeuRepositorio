<?php
require_once 'session_check.php';
require_once 'config.php';
require_once 'ftp_functions.php';
date_default_timezone_set('America/Sao_Paulo');
$user_root_ftp_path = FTP_PARENT_DIR . '/user_' . $user['id'];
$user_root_public_path = PUBLIC_PARENT_PATH . '/user_' . $user['id'];
$current_path = $_GET['path'] ?? '';
$current_path = str_replace('..', '', trim($current_path, '/'));
$full_path = !empty($current_path) ? $user_root_ftp_path . '/' . $current_path : $user_root_ftp_path;
$lista_de_arquivos = listarArquivosFTP($full_path);
$used_space_bytes = get_used_space($user_root_ftp_path);
$total_space_bytes = TOTAL_SPACE_GB * 1024 * 1024 * 1024;
$free_space_bytes = $total_space_bytes - $used_space_bytes;
$used_space_gb = round($used_space_bytes / (1024 * 1024 * 1024), 2);
$percentage_used = ($total_space_bytes > 0) ? round(($used_space_bytes / $total_space_bytes) * 100) : 0;
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo !empty($current_path) ? basename($current_path) . ' - ' : ''; ?><?php echo APP_NAME; ?></title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/style.css">
</head>
<body>
    <div class="container">
        <header class="header">
            <strong><?php echo APP_NAME; ?></strong>
            <div class="user-menu">
                <span>Olá, <?php echo htmlspecialchars($user['username']); ?>!</span>
                <?php if ($user['role'] == 'admin'): ?>
                    <a href="admin.php">Painel Admin</a>
                <?php endif; ?>
                <a href="logout.php">Sair</a>
            </div>
        </header>
        <nav class="toolbar">
            <button id="upload-btn">Fazer Upload</button>
            <button id="new-folder-btn">Criar Pasta</button>
        </nav>
        <div class="breadcrumbs">
            <a href="<?php echo BASE_URL; ?>">Raiz</a>
            <?php
            if (!empty($current_path)) {
                $path_parts = explode('/', $current_path);
                $built_path = '';
                foreach ($path_parts as $part) {
                    $built_path .= $part;
                    echo "<span> / </span><a href='" . BASE_URL . "/index.php?path=" . urlencode($built_path) . "'>" . htmlspecialchars($part) . "</a>";
                    $built_path .= '/';
                }
            }
            ?>
        </div>
        <div class="storage-info">
            <div class="pie-chart" style="--p:<?php echo $percentage_used; ?>"> <?php echo $percentage_used; ?>% </div>
            <div class="storage-text">
                <strong>Espaço em Disco</strong>
                <span><?php echo $used_space_gb; ?> GB de <?php echo TOTAL_SPACE_GB; ?> GB usados</span>
            </div>
        </div>
        <?php
        if (isset($_SESSION['upload_message'])) {
            $message = $_SESSION['upload_message']; $status = $_SESSION['upload_status'];
            echo "<div class='alert $status'>" . nl2br($message) . "</div>";
            unset($_SESSION['upload_message']); unset($_SESSION['upload_status']);
        }
        ?>
        <main class="file-explorer">
            <?php
            if (isset($lista_de_arquivos['error'])) {
                echo "<p style='color:red; font-weight:bold;'>" . htmlspecialchars($lista_de_arquivos['error']) . "</p>";
            } elseif (empty($lista_de_arquivos)) {
                echo "<p>Esta pasta está vazia.</p>";
            } else {
                foreach ($lista_de_arquivos as $arquivo) {
                    $nome_arquivo = htmlspecialchars($arquivo['name']);
                    $data_hora = date('d/m/Y H:i:s', $arquivo['modify']);
                    $is_dir = $arquivo['type'] == 'dir';
                    $is_image = !$is_dir && preg_match('/\.(jpg|jpeg|png|gif|webp)$/i', $nome_arquivo);
                    $is_video = !$is_dir && preg_match('/\.(mp4|webm|mov|ogg)$/i', $nome_arquivo);
                    $is_word = !$is_dir && preg_match('/\.(doc|docx)$/i', $nome_arquivo);
                    $is_pdf = !$is_dir && preg_match('/\.pdf$/i', $nome_arquivo);
                    $item_path = !empty($current_path) ? $current_path . '/' . $arquivo['name'] : $arquivo['name'];
                    $tag = $is_dir ? 'a' : 'div';
                    $href = $is_dir ? "href='" . BASE_URL . "/index.php?path=" . urlencode($item_path) . "'" : '';
                    echo "<div class='file-item-wrapper' draggable='true' data-filename='$nome_arquivo'>";
                    echo "  <$tag $href class='file-item' data-is-image='" . ($is_image ? '1' : '0') . "' data-is-video='" . ($is_video ? '1' : '0') . "' data-is-word='" . ($is_word ? '1' : '0') . "' data-is-pdf='" . ($is_pdf ? '1' : '0') . "' data-is-dir='" . ($is_dir ? '1' : '0') . "'>";
                    $file_url = BASE_URL . '/' . $user_root_public_path . '/' . ($current_path ? $current_path . '/' : '') . $nome_arquivo;
                    if ($is_image) {
                        echo "  <div class='thumbnail-container'><img src='" . htmlspecialchars($file_url) . "' class='file-thumbnail' alt='Thumbnail' loading='lazy'></div>";
                    } elseif ($is_video) {
                        echo "  <div class='thumbnail-container'><video class='file-thumbnail' preload='metadata'><source src='" . htmlspecialchars($file_url) . "#t=0.5' type='video/mp4'></video></div>";
                    } else {
                        echo "  <div class='file-icon'></div>";
                    }
                    echo "      <div class='file-info'><span class='file-name'>$nome_arquivo</span><span class='file-date'>$data_hora</span></div>";
                    echo "      <div class='file-actions'>";
                    if (!$is_dir) echo "      <a href='" . BASE_URL . "/download.php?path=" . urlencode($current_path) . "&file=$nome_arquivo' class='action-btn download' title='Baixar'>&#x21E9;</a>";
                    echo "          <button class='action-btn move' data-name='$nome_arquivo' title='Mover Para...'>&#10144;</button>";
                    echo "          <button class='action-btn delete' data-name='$nome_arquivo' title='Remover'>&#x1F5D1;</button>";
                    echo "      </div>";
                    echo "  </$tag>";
                    echo "</div>";
                }
            }
            ?>
        </main>
    </div>
    <div id="preview-modal" class="modal"><span class="close-modal">&times;</span><div id="modal-preview-content"></div></div>
    <div id="move-item-modal" class="modal"><div class="modal-content-form"><span class="close-modal">&times;</span><h3>Mover Item</h3><p>Selecione a pasta de destino para: <strong id="move-item-name"></strong></p><select id="folder-destination-select" size="10"></select><button id="confirm-move-btn">Mover Agora</button></div></div>
    <div id="upload-progress-modal" class="modal"><div class="modal-content-form"><h3>Enviando Arquivos...</h3><div id="upload-feedback"></div><div class="progress-bar-container"><div id="progress-bar"></div></div><p id="progress-text"></p></div></div>
    <form id="upload-form" action="<?php echo BASE_URL; ?>/upload.php" method="post" enctype="multipart/form-data" style="display: none;"><input type="file" name="arquivos[]" id="file-input" required multiple><input type="hidden" name="path" value="<?php echo htmlspecialchars($current_path); ?>"></form>
    <form id="new-folder-form" action="<?php echo BASE_URL; ?>/create_folder.php" method="post" style="display:none;"><input type="hidden" name="folder_name" id="folder_name_input"><input type="hidden" name="path" value="<?php echo htmlspecialchars($current_path); ?>"></form>
    <form id="delete-item-form" action="<?php echo BASE_URL; ?>/delete_item.php" method="post" style="display:none;"><input type="hidden" name="item_name" id="item_name_input"><input type="hidden" name="path" value="<?php echo htmlspecialchars($current_path); ?>"></form>
    <script>
        const publicBaseUrl = '<?php echo BASE_URL; ?>/';
        const publicBasePath = '<?php echo $user_root_public_path; ?>/';
        const availableSpace = <?php echo max(0, $free_space_bytes); ?>;
    </script>
    <script src="<?php echo BASE_URL; ?>/script.js"></script>
</body>
</html>