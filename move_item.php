<?php
require_once 'session_check.php';
require_once 'config.php';

$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['source_item'], $data['target_folder'], $data['current_path'])) {
    header('Content-Type: application/json');
    echo json_encode(['status' => 'error', 'message' => 'Dados de origem ou destino ausentes.']);
    exit();
}

// --- Sanitização e Definição de Variáveis ---
$source_item = basename($data['source_item']);
$target_relative_path = str_replace('..', '', trim($data['target_folder'], '/'));
$current_relative_path = str_replace('..', '', trim($data['current_path'], '/'));
$user_root_ftp_path = FTP_PARENT_DIR . '/user_' . $user['id'];

// --- Construção Correta dos Caminhos Físicos ---
$source_dir_path = !empty($current_relative_path) ? $user_root_ftp_path . '/' . $current_relative_path : $user_root_ftp_path;
$old_path = $source_dir_path . '/' . $source_item;

$destination_dir_path = !empty($target_relative_path) ? $user_root_ftp_path . '/' . $target_relative_path : $user_root_ftp_path;
$new_path = $destination_dir_path . '/' . $source_item;

// --- VERIFICAÇÕES DE LÓGICA CORRIGIDAS ---

// 1. Removemos a verificação com realpath() e usamos uma comparação de strings simples.
if ($old_path === $new_path) {
    header('Content-Type: application/json');
    echo json_encode(['status' => 'error', 'message' => 'A origem e o destino são os mesmos.']);
    exit();
}

// 2. Verifica se está a tentar mover uma pasta para dentro dela mesma ou de uma subpasta sua.
// Ex: Mover 'PastaA' para 'PastaA/SubPastaB' é inválido.
$is_directory = false; // Vamos assumir que é um ficheiro por defeito
$conn_id_check = ftp_connect(FTP_SERVER);
if ($conn_id_check && ftp_login($conn_id_check, FTP_USER, FTP_PASS)) {
    ftp_pasv($conn_id_check, true);
    // ftp_size retorna -1 para diretórios
    if (ftp_size($conn_id_check, $old_path) === -1) {
        $is_directory = true;
    }
    ftp_close($conn_id_check);
}

if ($is_directory && strpos($new_path, $old_path . '/') === 0) {
     header('Content-Type: application/json');
     echo json_encode(['status' => 'error', 'message' => 'Não é possível mover uma pasta para dentro de uma das suas próprias subpastas.']);
     exit();
}

$response = [];

// --- Lógica FTP ---
$conn_id = ftp_connect(FTP_SERVER);
if ($conn_id && ftp_login($conn_id, FTP_USER, FTP_PASS)) {
    ftp_pasv($conn_id, true);
    
    if (ftp_rename($conn_id, $old_path, $new_path)) {
        $response = ['status' => 'success', 'message' => "Item '$source_item' movido com sucesso!"];
    } else {
        $response = ['status' => 'error', 'message' => "ERRO: Não foi possível mover o item. Verifique as permissões."];
    }
    ftp_close($conn_id);
} else {
    $response = ['status' => 'error', 'message' => "ERRO: Falha na ligação FTP."];
}

header('Content-Type: application/json');
echo json_encode($response);
exit();
?>