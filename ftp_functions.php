<?php

/**
 * Calcula e retorna o espaço total usado no diretório principal do drive.
 *
 * @return int O espaço total usado em bytes.
 */
function get_used_space() {
    require_once 'config.php';
    $used_space_bytes = 0;
    $conn_id = ftp_connect(FTP_SERVER);
    if ($conn_id && ftp_login($conn_id, FTP_USER, FTP_PASS)) {
        ftp_pasv($conn_id, true);
        // Chama a função recursiva para calcular o tamanho a partir do diretório raiz do drive
        $used_space_bytes = calculate_directory_size($conn_id, FTP_UPLOAD_DIR);
        ftp_close($conn_id);
    }
    return $used_space_bytes;
}

/**
 * Lista os arquivos e diretórios de um caminho FTP com detalhes completos.
 */
function listarArquivosFTP($path) {
    require_once 'config.php';
    $conn_id = ftp_connect(FTP_SERVER);
    if ($conn_id === false) return ['error' => 'Falha ao conectar ao servidor FTP.'];
    if (!ftp_login($conn_id, FTP_USER, FTP_PASS)) { ftp_close($conn_id); return ['error' => 'Falha no login do FTP.']; }
    ftp_pasv($conn_id, true);
    if (!@ftp_chdir($conn_id, $path)) { ftp_close($conn_id); return ['error' => "ERRO CRÍTICO: O diretório '$path' não foi encontrado. Verifique o config.php e se a pasta existe."]; }
    $contents = ftp_mlsd($conn_id, '.');
    ftp_close($conn_id);
    if ($contents === false) return ['error' => "Não foi possível listar o conteúdo de '$path'."];
    $items = [];
    foreach ($contents as $item) { if ($item['name'] != '.' && $item['name'] != '..') $items[] = $item; }
    return $items;
}

/**
 * Calcula o tamanho total (em bytes) de um diretório de forma recursiva.
 */
function calculate_directory_size($conn_id, $directory) {
    $size = 0;
    $files = ftp_mlsd($conn_id, $directory);
    if (empty($files)) return 0;
    foreach ($files as $file) {
        if ($file['name'] == '.' || $file['name'] == '..') continue;
        $path = rtrim($directory, '/') . '/' . $file['name'];
        if ($file['type'] == 'dir') $size += calculate_directory_size($conn_id, $path);
        else $size += (int)$file['size'];
    }
    return $size;
}

/**
 * Deleta um arquivo ou um diretório (e todo o seu conteúdo) de forma recursiva.
 */
function ftp_delete_recursive($conn_id, $path) {
    $details = ftp_mlsd($conn_id, dirname($path));
    if ($details === false) return false;
    $is_dir = false;
    foreach($details as $detail) { if($detail['name'] == basename($path) && $detail['type'] == 'dir') { $is_dir = true; break; } }
    if (!$is_dir) return ftp_delete($conn_id, $path);
    $files = ftp_nlist($conn_id, $path);
    if ($files !== false) {
        foreach ($files as $file) {
            $basename = basename($file);
            if ($basename == '.' || $basename == '..') continue;
            ftp_delete_recursive($conn_id, $path . '/' . $basename);
        }
    }
    return ftp_rmdir($conn_id, $path);
}
?>