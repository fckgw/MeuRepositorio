<?php
session_start();
require_once 'config.php';

$current_path = $_POST['path'] ?? '';
$current_path = str_replace('..', '', trim($current_path, '/'));
$base_path = !empty($current_path) ? FTP_UPLOAD_DIR . '/' . $current_path : FTP_UPLOAD_DIR;

if (isset($_POST['folder_name']) && !empty(trim($_POST['folder_name']))) {
    $folder_name = preg_replace("/[^a-zA-Z0-9\-_ ]/", "", $_POST['folder_name']);
    $new_path = $base_path . '/' . $folder_name;

    $conn_id = ftp_connect(FTP_SERVER);
    if ($conn_id && ftp_login($conn_id, FTP_USER, FTP_PASS)) {
        ftp_pasv($conn_id, true);
        if (ftp_mkdir($conn_id, $new_path)) {
            $_SESSION['upload_message'] = "Pasta '$folder_name' criada com sucesso!";
            $_SESSION['upload_status'] = 'success';
        } else {
            $_SESSION['upload_message'] = "ERRO: Não foi possível criar a pasta.";
            $_SESSION['upload_status'] = 'error';
        }
        ftp_close($conn_id);
    } else {
        $_SESSION['upload_message'] = "ERRO: Falha na conexão FTP.";
        $_SESSION['upload_status'] = 'error';
    }
} else {
    $_SESSION['upload_message'] = "ERRO: O nome da pasta não pode ser vazio.";
    $_SESSION['upload_status'] = 'error';
}

$redirect_url = BASE_URL . (!empty($current_path) ? '/index.php?path=' . urlencode($current_path) : '');
header('Location: ' . $redirect_url);
exit();
?>