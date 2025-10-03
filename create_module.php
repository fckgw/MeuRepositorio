<?php
require_once 'session_check.php';
if ($user['role'] !== 'admin') { die("Acesso negado."); }

// Garante que a ligação à base de dados é incluída
require_once 'db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && !empty(trim($_POST['module_name'])) && !empty(trim($_POST['module_path']))) {
    $module_name = trim($_POST['module_name']);
    $module_path = trim($_POST['module_path']);
    $icon_svg = $_POST['icon_svg'] ?? null;
    
    $stmt = $conn->prepare("INSERT INTO modules (module_name, module_path, icon_svg) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $module_name, $module_path, $icon_svg);
    
    if ($stmt->execute()) {
        $folder_name = trim(parse_url($module_path, PHP_URL_PATH), '/');

        if (!empty($folder_name) && strpos($folder_name, '.php') === false) {
            $module_folder_path = 'public_html/' . $folder_name;
            $conn_id = ftp_connect(FTP_SERVER);
            if ($conn_id && ftp_login($conn_id, FTP_USER, FTP_PASS)) {
                ftp_pasv($conn_id, true);
                if (@ftp_mkdir($conn_id, $module_folder_path)) {
                    $_SESSION['admin_message'] = "Módulo '{$module_name}' e pasta '{$module_folder_path}' criados com sucesso.";
                } else {
                    $_SESSION['admin_message'] = "Módulo '{$module_name}' criado na base de dados, mas falha ao criar a pasta no FTP. Verifique se já existe ou se há permissões.";
                }
                ftp_close($conn_id);
            }
        } else {
            $_SESSION['admin_message'] = "Módulo '{$module_name}' criado com sucesso (sem criação de pasta).";
        }
        $_SESSION['admin_status'] = 'success';
    } else {
        $_SESSION['admin_message'] = "Erro ao criar o módulo na base de dados: " . $stmt->error;
        $_SESSION['admin_status'] = 'error';
    }
} else {
    $_SESSION['admin_message'] = "Nome e Caminho do Módulo são obrigatórios.";
    $_SESSION['admin_status'] = 'error';
}

header('Location: module_management.php');
exit();
?>