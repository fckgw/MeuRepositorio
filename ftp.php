<?php
// ATENÇÃO: Substitua pelos seus dados de FTP.
$ftp_server = "bdsoft.com.br";
$ftp_user = "souzafelipe@bdsoft.com.br";
$ftp_pass = "Fckgw!151289";

// Estabelece a conexão
$conn_id = ftp_connect($ftp_server) or die("Não foi possível conectar a $ftp_server");

// Realiza o login
if (@ftp_login($conn_id, $ftp_user, $ftp_pass)) {
    echo "Conectado como $ftp_user@$ftp_server\n";
} else {
    echo "Não foi possível conectar como $ftp_user\n";
}

// Lista os arquivos
$contents = ftp_nlist($conn_id, ".");

// Fecha a conexão
ftp_close($conn_id);
?>