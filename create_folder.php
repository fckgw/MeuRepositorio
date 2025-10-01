<?php
// 1. Inicia a sessão e verifica se o utilizador está autenticado
require_once 'session_check.php';

// 2. Inclui os ficheiros de configuração
require_once 'config.php';

// 3. Define o caminho de redirecionamento
$current_path = $_POST['path'] ?? '';
$current_path = str_replace('..', '', trim($current_path, '/'));
$redirect_url = BASE_URL . (!empty($current_path) ? '/index.php?path=' . urlencode($current_path) : '');

// 4. Define o diretório raiz do utilizador atual
$user_root_ftp_path = FTP_PARENT_DIR . '/user_' . $user['id'];

// 5. Define o caminho base onde a nova pasta será criada
$base_path = !empty($current_path) ? $user_root_ftp_path . '/' . $current_path : $user_root_ftp_path;

if (isset($_POST['folder_name']) && !empty(trim($_POST['folder_name']))) {
    // Sanitiza o nome da pasta
    $folder_name = preg_replace("/[^a-zA-Z0-9\-_ ]/", "", $_POST['folder_name']);
    $new_path = $base_path . '/' . $folder_name;

    $conn_id = ftp_connect(FTP_SERVER);
    if ($conn_id && ftp_login($conn_id, FTP_USER, FTP_PASS)) {
        ftp_pasv($conn_id, true);
        if (@ftp_mkdir($conn_id, $new_path)) {
            $_SESSION['upload_message'] = "Pasta '$folder_name' criada com sucesso!";
            $_SESSION['upload_status'] = 'success';
        } else {
            $_SESSION['upload_message'] = "ERRO: Não foi possível criar a pasta. Verifique as permissões.";
            $_SESSION['upload_status'] = 'error';
        }
        ftp_close($conn_id);
    } else {
        $_SESSION['upload_message'] = "ERRO: Falha na ligação FTP.";
        $_SESSION['upload_status'] = 'error';
    }
} else {
    $_SESSION['upload_message'] = "ERRO: O nome da pasta não pode ser vazio.";
    $_SESSION['upload_status'] = 'error';
}

// 6. Redireciona para a página correta
header('Location: ' . $redirect_url);
exit();
?>