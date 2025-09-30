<?php
require_once 'config.php';

ini_set('display_errors', 1);
error_reporting(E_ALL);

// Supondo que você receberá o nome da pasta de um formulário
$dir_name = 'NovaPastaDeTeste'; // Use um nome fixo para testar

if (empty($dir_name)) {
    die("O nome da pasta não pode ser vazio.");
}

$conn_id = ftp_connect(FTP_SERVER) or die("Não foi possível conectar ao servidor FTP: " . FTP_SERVER);

$login_result = ftp_login($conn_id, FTP_USER, FTP_PASS);
if (!$login_result) {
    die("Falha no login do FTP! Verifique usuário e senha.");
}

// NOVO: Habilita o modo passivo
ftp_pasv($conn_id, true);

if (ftp_mkdir($conn_id, $dir_name)) {
    echo "Diretório $dir_name criado com sucesso!";
} else {
    echo "Houve um problema ao criar o diretório $dir_name. Verifique se o diretório pai tem permissão de escrita.";
}

ftp_close($conn_id);
header("Refresh: 3; url=index.php");
?>