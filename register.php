<?php
// 1. Inclui os ficheiros de configuração e base de dados
require_once 'config.php';
require_once 'db.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $password_confirm = $_POST['password_confirm'];

    // 2. Validações
    if (empty($username) || empty($email) || empty($password)) {
        $error = "Todos os campos são obrigatórios.";
    } elseif ($password !== $password_confirm) {
        $error = "As palavras-passe não coincidem.";
    } elseif (strlen($password) < 6) {
        $error = "A palavra-passe deve ter pelo menos 6 caracteres.";
    } else {
        // 3. Prepara os dados para inserir na base de dados
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $trial_days = TRIAL_DAYS;
        $trial_ends_at = date('Y-m-d H:i:s', strtotime("+$trial_days days"));
        $default_plan_id = DEFAULT_PLAN_ID;
        
        $stmt = $conn->prepare("INSERT INTO users (username, email, password, trial_ends_at, plan_id) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssi", $username, $email, $hashed_password, $trial_ends_at, $default_plan_id);
        
        if ($stmt->execute()) {
            $user_id = $stmt->insert_id;
            
            // 4. Cria a pasta do utilizador no FTP
            $user_folder = FTP_PARENT_DIR . '/user_' . $user_id;
            $conn_id = ftp_connect(FTP_SERVER);
            if ($conn_id && ftp_login($conn_id, FTP_USER, FTP_PASS)) {
                ftp_pasv($conn_id, true);
                @ftp_mkdir($conn_id, $user_folder);
                ftp_close($conn_id);
            }
            
            // 5. Envia o email de boas-vindas
            $to = $email;
            $subject = "Bem-vindo ao " . APP_NAME . "!";
            $body = "Olá {$username},\n\nA sua conta no " . APP_NAME . " foi criada com sucesso.\n\n"
                  . "Credenciais de Acesso:\n"
                  . "Email: {$email}\n\n"
                  . "O seu período de trial de {$trial_days} dias termina em: " . date('d/m/Y', strtotime($trial_ends_at)) . ".\n\n"
                  . "Obrigado,\nA Equipa BDSoft";
            
            $headers = "From: no-reply@" . parse_url(BASE_URL, PHP_URL_HOST) . "\r\n"
                     . "Reply-To: no-reply@" . parse_url(BASE_URL, PHP_URL_HOST) . "\r\n"
                     . "Content-Type: text/plain; charset=UTF-8\r\n"
                     . "MIME-Version: 1.0\r\n"
                     . "X-Mailer: PHP/" . phpversion();
            
            @mail($to, $subject, $body, $headers);

            // 6. Redireciona para o login com uma mensagem de sucesso
            header('Location: login.php?status=registered');
            exit();
        } else {
            if ($conn->errno == 1062) {
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
            <div class="password-wrapper">
                <input type="password" name="password" id="password-input" placeholder="Palavra-passe (mín. 6 caracteres)" required>
                <span id="toggle-password" class="toggle-password-icon"></span>
            </div>
            <div class="password-wrapper">
                <input type="password" name="password_confirm" id="password-confirm-input" placeholder="Confirmar palavra-passe" required>
                <span id="toggle-password-confirm" class="toggle-password-icon"></span>
            </div>
            <button type="submit">Registar</button>
        </form>
        <p>Já tem uma conta? <a href="login.php">Faça login</a>.</p>
    </div>
    <script>
        function setupToggle(toggleId, inputId) {
            const toggle = document.getElementById(toggleId);
            const input = document.getElementById(inputId);
            const eyeIcon = '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>';
            const eyeOffIcon = '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path><line x1="1" y1="1" x2="23" y2="23"></line></svg>';
            if(toggle && input) {
                toggle.innerHTML = eyeOffIcon;
                toggle.addEventListener('click', function() {
                    const type = input.getAttribute('type') === 'password' ? 'text' : 'password';
                    input.setAttribute('type', type);
                    this.innerHTML = type === 'password' ? eyeOffIcon : eyeIcon;
                });
            }
        }
        setupToggle('toggle-password', 'password-input');
        setupToggle('toggle-password-confirm', 'password-confirm-input');
    </script>
</body>
</html>