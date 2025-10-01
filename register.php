<?php
require_once 'config.php';
require_once 'db.php';

$error = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    // Validações básicas
    if (empty($username) || empty($email) || empty($password)) {
        $error = "Todos os campos são obrigatórios.";
    } elseif (strlen($password) < 6) {
        $error = "A palavra-passe deve ter pelo menos 6 caracteres.";
    } else {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $trial_days = TRIAL_DAYS;
        $trial_ends_at = date('Y-m-d H:i:s', strtotime("+$trial_days days"));
        
        $stmt = $conn->prepare("INSERT INTO users (username, email, password, trial_ends_at) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $username, $email, $hashed_password, $trial_ends_at);
        
        if ($stmt->execute()) {
            $user_id = $stmt->insert_id;
            $user_folder = FTP_PARENT_DIR . '/user_' . $user_id;
            
            // Cria a pasta do utilizador no FTP
            $conn_id = ftp_connect(FTP_SERVER);
            if ($conn_id && ftp_login($conn_id, FTP_USER, FTP_PASS)) {
                ftp_pasv($conn_id, true);
                @ftp_mkdir($conn_id, $user_folder); // O @ suprime erros se a pasta já existir
                ftp_close($conn_id);
            }
            
            // Redireciona para o login com uma mensagem de sucesso
            header('Location: login.php?status=registered');
            exit();
        } else {
            if ($conn->errno == 1062) { // Código de erro para entrada duplicada
                $error = 'Email ou nome de utilizador já existe.';
            } else {
                $error = 'Ocorreu um erro. Por favor, tente novamente.';
            }
        }
        $stmt->close();
    }
}
$conn->close();
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registo - <?php echo APP_NAME; ?></title>
    <link rel="stylesheet" href="style.css">
</head>
<body class="auth-page">
    <div class="auth-container">
        <h1><?php echo APP_NAME; ?></h1>
        <h2>Criar Conta (<?php echo TRIAL_DAYS; ?> Dias Grátis)</h2>
        
        <?php if ($error): ?><div class="alert error"><?php echo $error; ?></div><?php endif; ?>
        
        <form method="POST" action="register.php">
            <input type="text" name="username" placeholder="Nome de utilizador" required>
            <input type="email" name="email" placeholder="Email" required>
            <input type="password" name="password" placeholder="Palavra-passe (mín. 6 caracteres)" required>
            <button type="submit">Registar</button>
        </form>
        <p>Já tem uma conta? <a href="login.php">Faça login</a>.</p>
    </div>
</body>
</html>