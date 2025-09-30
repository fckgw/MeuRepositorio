<?php
$servername = "localhost";
$username = "feli0499_root";
$password = "BDSoft@1020";
$dbname = "feli0499_meurepositorio";

// Criar conexão
$conn = new mysqli($servername, $username, $password, $dbname);

// Checar conexão
if ($conn->connect_error) {
    die("Conexão falhou: " . $conn->connect_error);
}
?>