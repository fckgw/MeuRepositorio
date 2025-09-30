<?php
require_once 'config.php';
function get_folder_tree($conn_id, $directory, $prefix = '') {
    $folders = [];
    $items = ftp_mlsd($conn_id, $directory);
    if (empty($items)) return [];
    foreach ($items as $item) {
        if ($item['name'] != '.' && $item['name'] != '..') {
            if ($item['type'] == 'dir') {
                $current_path = ltrim($prefix . '/' . $item['name'], '/');
                $folders[] = [ 'name' => str_repeat('&nbsp;&nbsp;', substr_count($current_path, '/')) . '📁 ' . $item['name'], 'path' => $current_path ];
                $sub_folders = get_folder_tree($conn_id, $directory . '/' . $item['name'], $current_path);
                $folders = array_merge($folders, $sub_folders);
            }
        }
    }
    return $folders;
}
$response = ['status' => 'error', 'folders' => []];
$conn_id = ftp_connect(FTP_SERVER);
if ($conn_id && ftp_login($conn_id, FTP_USER, FTP_PASS)) {
    ftp_pasv($conn_id, true);
    $all_folders = get_folder_tree($conn_id, FTP_UPLOAD_DIR);
    array_unshift($all_folders, ['name' => '📁 Raiz (Diretório Principal)', 'path' => '']);
    $response = ['status' => 'success', 'folders' => $all_folders];
    ftp_close($conn_id);
}
header('Content-Type: application/json');
echo json_encode($response);
exit();
?>