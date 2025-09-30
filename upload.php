<?php
session_start();
require_once 'config.php';
require_once 'ftp_functions.php';

$current_path = $_POST['path'] ?? '';
$current_path = str_replace('..', '', trim($current_path, '/'));
$redirect_url = BASE_URL . (!empty($current_path) ? '/index.php?path=' . urlencode($current_path) : '');

if (!isset($_FILES['arquivos']['name']) || !is_array($_FILES['arquivos']['name']) || empty($_FILES['arquivos']['name'][0])) {
    $_SESSION['upload_message'] = "ERRO: Nenhum arquivo selecionado.";
    $_SESSION['upload_status'] = 'error';
    header('Location: ' . $redirect_url);
    exit();
}

// --- VERIFICAÇÃO DE ESPAÇO NO SERVIDOR ---
$total_space_bytes = TOTAL_SPACE_GB * 1024 * 1024 * 1024;
$used_space_bytes = get_used_space();
$available_space = $total_space_bytes - $used_space_bytes;

$incoming_files_size = 0;
foreach ($_FILES['arquivos']['size'] as $size) {
    $incoming_files_size += $size;
}

if ($incoming_files_size > $available_space) {
    // --- MENSAGEM PERSONALIZADA APLICADA AQUI ---
    $_SESSION['upload_message'] = "Upload Cancelado: Espaço Insuficiente. <br>Por favor, libere espaço ou compre mais armazenamento com a nossa equipe.";
    $_SESSION['upload_status'] = 'error';
    header('Location: ' . $redirect_url);
    exit();
}
// --- FIM DA VERIFICAÇÃO ---

$base_path = !empty($current_path) ? FTP_UPLOAD_DIR . '/' . $current_path : FTP_UPLOAD_DIR;
$success_files = [];
$failed_files = [];

$conn_id = ftp_connect(FTP_SERVER);
if ($conn_id && ftp_login($conn_id, FTP_USER, FTP_PASS)) {
    ftp_pasv($conn_id, true);
    $file_count = count($_FILES['arquivos']['name']);
    for ($i = 0; $i < $file_count; $i++) {
        if ($_FILES['arquivos']['error'][$i] === UPLOAD_ERR_OK) {
            $local_file = $_FILES['arquivos']['tmp_name'][$i];
            $file_name = basename($_FILES['arquivos']['name'][$i]);
            $remote_path = $base_path . '/' . $file_name;
            if (ftp_put($conn_id, $remote_path, $local_file, FTP_BINARY)) {
                $success_files[] = $file_name;
            } else {
                $failed_files[] = $file_name;
            }
        }
    }
    ftp_close($conn_id);
} else {
    $_SESSION['upload_message'] = "ERRO Crítico: Falha na conexão FTP.";
    $_SESSION['upload_status'] = 'error';
    header('Location: ' . $redirect_url);
    exit();
}

$message = '';
if (!empty($success_files)) {
    $message .= count($success_files) . " arquivo(s) enviados com sucesso.<br>";
    $_SESSION['upload_status'] = 'success';
}
if (!empty($failed_files)) {
    $message .= count($failed_files) . " arquivo(s) falharam: " . implode(', ', $failed_files) . ".";
    $_SESSION['upload_status'] = empty($success_files) ? 'error' : 'warning';
}
$_SESSION['upload_message'] = $message;

header('Location: ' . $redirect_url);
exit();
?>
