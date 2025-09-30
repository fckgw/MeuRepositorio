<?php
require_once 'config.php';

if (!isset($_GET['file']) || empty($_GET['file'])) die("ERRO: Arquivo não especificado.");

$current_path = $_GET['path'] ?? '';
$current_path = str_replace('..', '', trim($current_path, '/'));
$base_path = !empty($current_path) ? FTP_UPLOAD_DIR . '/' . $current_path : FTP_UPLOAD_DIR;

$filename = basename($_GET['file']);
$filepath = $base_path . '/' . $filename;
$local_temp_file = fopen('php://temp', 'r+'); 

$conn_id = ftp_connect(FTP_SERVER);
if ($conn_id === false) die("ERRO: Falha na conexão FTP.");

if (ftp_login($conn_id, FTP_USER, FTP_PASS)) {
    ftp_pasv($conn_id, true);
    $filesize = ftp_size($conn_id, $filepath);
    if ($filesize != -1) {
        if (ftp_fget($conn_id, $local_temp_file, $filepath, FTP_BINARY)) {
            ftp_close($conn_id);
            rewind($local_temp_file);
            header('Content-Description: File Transfer');
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            header('Expires: 0');
            header('Cache-Control: must-revalidate');
            header('Pragma: public');
            header('Content-Length: ' . $filesize);
            fpassthru($local_temp_file);
            fclose($local_temp_file);
            exit;
        } else {
             die("ERRO: Falha ao ler o arquivo do servidor FTP.");
        }
    } else {
        ftp_close($conn_id);
        die("ERRO: Não é possível baixar diretórios ou o arquivo não foi encontrado.");
    }
} else {
    ftp_close($conn_id);
    die("ERRO: Falha no login do FTP.");
}
?>