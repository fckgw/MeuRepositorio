<?php
require_once 'session_check.php';
require_once 'config.php';
require_once 'ftp_functions.php';
$current_path = $_POST['path'] ?? '';
$current_path = str_replace('..', '', trim($current_path, '/'));
$redirect_url = BASE_URL . (!empty($current_path) ? '/index.php?path=' . urlencode($current_path) : '');
if (!isset($_FILES['arquivos']['name']) || !is_array($_FILES['arquivos']['name']) || empty($_FILES['arquivos']['name'][0])) {
    $_SESSION['upload_message'] = "ERRO: Nenhum ficheiro selecionado.";
    $_SESSION['upload_status'] = 'error';
    header('Location: ' . $redirect_url);
    exit();
}
$user_root_ftp_path = FTP_PARENT_DIR . '/user_' . $user['id'];
$total_space_gb = $user['space_gb'];
$is_unlimited = $total_space_gb <= 0;
if (!$is_unlimited) {
    $total_space_bytes = $total_space_gb * 1024 * 1024 * 1024;
    $used_space_bytes = get_used_space($user_root_ftp_path);
    $available_space = $total_space_bytes - $used_space_bytes;
    $incoming_files_size = array_sum($_FILES['arquivos']['size']);
    if ($incoming_files_size > $available_space) {
        $_SESSION['upload_message'] = "Upload Cancelado: Espaço em Disco Insuficiente. <br>Por favor, liberte espaço ou compre mais armazenamento.";
        $_SESSION['upload_status'] = 'error';
        header('Location: ' . $redirect_url);
        exit();
    }
}
$base_path = !empty($current_path) ? $user_root_ftp_path . '/' . $current_path : $user_root_ftp_path;
$success_files = [];
$failed_files = [];
$conn_id = ftp_connect(FTP_SERVER);
if ($conn_id && ftp_login($conn_id, FTP_USER, FTP_PASS)) {
    ftp_pasv($conn_id, true);
    for ($i = 0; $i < count($_FILES['arquivos']['name']); $i++) {
        if ($_FILES['arquivos']['error'][$i] === UPLOAD_ERR_OK) {
            $local_file = $_FILES['arquivos']['tmp_name'][$i];
            $file_name = basename($_FILES['arquivos']['name'][$i]);
            $remote_path = $base_path . '/' . $file_name;
            if (ftp_put($conn_id, $remote_path, $local_file, FTP_BINARY)) { $success_files[] = $file_name; }
            else { $failed_files[] = $file_name; }
        }
    }
    ftp_close($conn_id);

    require_once 'utils.php'; // Inclui as utilidades
    log_activity('UPLOAD_FILES', "Ficheiros com sucesso: " . count($success_files) . ", Ficheiros com falha: " . count($failed_files) . " na pasta: " . $base_path);

} else {
    $_SESSION['upload_message'] = "ERRO Crítico: Falha na ligação FTP.";
    $_SESSION['upload_status'] = 'error';
    header('Location: ' . $redirect_url);
    exit();
}
$message = '';
if (!empty($success_files)) { $message .= count($success_files) . " ficheiro(s) enviados com sucesso.<br>"; $_SESSION['upload_status'] = 'success'; }
if (!empty($failed_files)) { $message .= count($failed_files) . " ficheiro(s) falharam: " . implode(', ', $failed_files) . "."; $_SESSION['upload_status'] = empty($success_files) ? 'error' : 'warning'; }
$_SESSION['upload_message'] = $message;
header('Location: ' . $redirect_url);
exit();
?>