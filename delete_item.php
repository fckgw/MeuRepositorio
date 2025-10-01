<?php
// 1. Inicia a sessão e verifica se o utilizador está autenticado
require_once 'session_check.php';

// 2. Inclui os ficheiros de configuração e funções
require_once 'config.php';
require_once 'ftp_functions.php';

// 3. Define o caminho de redirecionamento
$current_path = $_POST['path'] ?? '';
$current_path = str_replace('..', '', trim($current_path, '/'));
$redirect_url = BASE_URL . (!empty($current_path) ? '/index.php?path=' . urlencode($current_path) : '');

// 4. Define o diretório raiz do utilizador atual
$user_root_ftp_path = FTP_PARENT_DIR . '/user_' . $user['id'];

// 5. Define o caminho base onde o item a ser apagado se encontra
$base_path = !empty($current_path) ? $user_root_ftp_path . '/' . $current_path : $user_root_ftp_path;

if (isset($_POST['item_name']) && !empty($_POST['item_name'])) {
    $item_name = basename($_POST['item_name']);
    $item_path = $base_path . '/' . $item_name;
    
    $conn_id = ftp_connect(FTP_SERVER);
    if ($conn_id && ftp_login($conn_id, FTP_USER, FTP_PASS)) {
        ftp_pasv($conn_id, true);
        if (ftp_delete_recursive($conn_id, $item_path)) {
            $_SESSION['upload_message'] = "Item '$item_name' removido com sucesso!";
            $_SESSION['upload_status'] = 'success';
        } else {
            $_SESSION['upload_message'] = "ERRO: Não foi possível remover o item '$item_name'.";
            $_SESSION['upload_status'] = 'error';
        }
        ftp_close($conn_id);
    } else {
        $_SESSION['upload_message'] = "ERRO: Falha na ligação FTP.";
        $_SESSION['upload_status'] = 'error';
    }
} else {
    $_SESSION['upload_message'] = "ERRO: Nenhum item especificado para remoção.";
    $_SESSION['upload_status'] = 'error';
}

// 6. Redireciona para a página correta
header('Location: ' . $redirect_url);
exit();
?>