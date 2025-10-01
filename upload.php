<?php
// 1. Inicia a sessão e verifica se o utilizador está autenticado
require_once 'session_check.php';

// 2. Inclui os ficheiros de configuração e funções
require_once 'config.php';
require_once 'ftp_functions.php';

// 3. Define o caminho de redirecionamento (para onde voltar após o upload)
$current_path = $_POST['path'] ?? '';
$current_path = str_replace('..', '', trim($current_path, '/'));
$redirect_url = BASE_URL . (!empty($current_path) ? '/index.php?path=' . urlencode($current_path) : '');

// 4. Verifica se algum ficheiro foi enviado
if (!isset($_FILES['arquivos']['name']) || !is_array($_FILES['arquivos']['name']) || empty($_FILES['arquivos']['name'][0])) {
    $_SESSION['upload_message'] = "ERRO: Nenhum ficheiro selecionado.";
    $_SESSION['upload_status'] = 'error';
    header('Location: ' . $redirect_url);
    exit();
}

// 5. Define o diretório raiz do utilizador atual
$user_root_ftp_path = FTP_PARENT_DIR . '/user_' . $user['id'];

// --- VERIFICAÇÃO DE ESPAÇO NO SERVIDOR (ESPECÍFICO DO UTILIZADOR) ---
$total_space_bytes = TOTAL_SPACE_GB * 1024 * 1024 * 1024;
$used_space_bytes = get_used_space($user_root_ftp_path); // Passa a pasta do utilizador para a função
$available_space = $total_space_bytes - $used_space_bytes;

$incoming_files_size = 0;
foreach ($_FILES['arquivos']['size'] as $size) {
    $incoming_files_size += $size;
}

if ($incoming_files_size > $available_space) {
    $_SESSION['upload_message'] = "Upload Cancelado: Espaço em Disco Insuficiente. <br>Por favor, liberte espaço ou compre mais armazenamento com a nossa equipa.";
    $_SESSION['upload_status'] = 'error';
    header('Location: ' . $redirect_url);
    exit();
}
// --- FIM DA VERIFICAÇÃO ---

// 6. Define o caminho base de upload (dentro da pasta do utilizador)
$base_path = !empty($current_path) ? $user_root_ftp_path . '/' . $current_path : $user_root_ftp_path;
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
    $_SESSION['upload_message'] = "ERRO Crítico: Falha na ligação FTP.";
    $_SESSION['upload_status'] = 'error';
    header('Location: ' . $redirect_url);
    exit();
}

// 7. Monta a mensagem de feedback final
$message = '';
if (!empty($success_files)) {
    $message .= count($success_files) . " ficheiro(s) enviados com sucesso.<br>";
    $_SESSION['upload_status'] = 'success';
}
if (!empty($failed_files)) {
    $message .= count($failed_files) . " ficheiro(s) falharam: " . implode(', ', $failed_files) . ".";
    $_SESSION['upload_status'] = empty($success_files) ? 'error' : 'warning';
}
$_SESSION['upload_message'] = $message;

// 8. Redireciona para a página correta
header('Location: ' . $redirect_url);
exit();
?>