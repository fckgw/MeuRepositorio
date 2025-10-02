<?php
require_once 'session_check.php';
require_once 'config.php';

/**
 * Função recursiva para obter a árvore de pastas de um diretório.
 * Esta versão trabalha com caminhos completos para maior estabilidade.
 */
function get_folder_tree($conn_id, $base_path, $current_prefix = '') {
    $folders = [];
    $full_current_path = $base_path . ($current_prefix ? '/' . $current_prefix : '');
    
    // O @ suprime erros se uma subpasta não for legível
    $items = @ftp_mlsd($conn_id, $full_current_path);
    if (empty($items)) {
        return [];
    }

    foreach ($items as $item) {
        if ($item['name'] == '.' || $item['name'] == '..' || $item['type'] != 'dir') {
            continue;
        }
        
        $path_for_option = ltrim($current_prefix . '/' . $item['name'], '/');
        $folders[] = [
            'name' => str_repeat('&nbsp;&nbsp;&nbsp;', substr_count($path_for_option, '/')) . '📁 ' . $item['name'],
            'path' => $path_for_option
        ];
        
        // Chama a função recursivamente para as subpastas
        $sub_folders = get_folder_tree($conn_id, $base_path, $path_for_option);
        $folders = array_merge($folders, $sub_folders);
    }
    return $folders;
}

$user_root_ftp_path = FTP_PARENT_DIR . '/user_' . $user['id'];
$response = ['status' => 'error', 'message' => 'Falha na ligação FTP.', 'folders' => []];

$conn_id = ftp_connect(FTP_SERVER);
if ($conn_id && ftp_login($conn_id, FTP_USER, FTP_PASS)) {
    ftp_pasv($conn_id, true);
    
    // Inicia a busca a partir da pasta raiz do utilizador
    $all_folders = get_folder_tree($conn_id, $user_root_ftp_path);
    
    // Adiciona a opção da Raiz do utilizador no início da lista
    array_unshift($all_folders, ['name' => '📁 Raiz (Diretório Principal)', 'path' => '']);
    
    $response = ['status' => 'success', 'message' => 'Pastas carregadas.', 'folders' => $all_folders];
    ftp_close($conn_id);
}

header('Content-Type: application/json');
echo json_encode($response);
exit();
?>