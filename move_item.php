<?php
session_start();
require_once 'config.php';
$data = json_decode(file_get_contents('php://input'), true);
if (!isset($data['source_item'], $data['target_folder'], $data['current_path']) || empty(trim($data['source_item']))) {
    header('Content-Type: application/json');
    echo json_encode(['status' => 'error', 'message' => 'Dados de origem ou destino ausentes.']);
    exit();
}
$source_item = basename($data['source_item']);
$target_folder_path = str_replace('..', '', trim($data['target_folder'], '/'));
$current_path = str_replace('..', '', trim($data['current_path'], '/'));
$source_base_path = !empty($current_path) ? FTP_UPLOAD_DIR . '/' . $current_path : FTP_UPLOAD_DIR;
$old_path = $source_base_path . '/' . $source_item;
$destination_base_path = !empty($target_folder_path) ? FTP_UPLOAD_DIR . '/' . $target_folder_path : FTP_UPLOAD_DIR;
$new_path = $destination_base_path . '/' . $source_item;
if ($old_path == $new_path) {
    header('Content-Type: application/json');
    echo json_encode(['status' => 'error', 'message' => 'A origem e o destino são os mesmos.']);
    exit();
}
$response = [];
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
    $response = ['status' => 'error', 'message' => "ERRO: Falha na conexão FTP."];
}
header('Content-Type: application/json');
echo json_encode($response);
exit();
?>