<?php
// O session_start() é necessário para definir as variáveis de sessão APÓS o login bem-sucedido.
session_start();

// Se o utilizador já estiver autenticado, redireciona-o para a página de seleção de módulos.
if (isset($_SESSION['user_id'])) {
    header('Location: select_module.php');
    exit();
}

// Inclui apenas os ficheiros necessários para o login.
require_once 'config.php';
require_once 'db.php';

$error = '';
$registration_success = isset($_GET['status']) && $_GET['status'] == 'registered';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];
    
    // Prepara a consulta para evitar injeção de SQL.
    $stmt = $conn->prepare("SELECT id, password, role FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();
    
    if ($stmt->num_rows > 0) {
        $stmt->bind_result($id, $hashed_password, $role);
        $stmt->fetch();
        
        // Verifica se a palavra-passe corresponde.
        if (password_verify($password, $hashed_password)) {
            // Sucesso! Define as variáveis de sessão.
            $_SESSION['user_id'] = $id;
            $_SESSION['role'] = $role;

            // --- NOVO: ATUALIZA A DATA DO ÚLTIMO LOGIN ---
            $stmt_update = $conn->prepare("UPDATE users SET last_login_at = NOW() WHERE id = ?");
            $stmt_update->bind_param("i", $id);
            $stmt_update->execute();
            $stmt_update->close();
            // --- FIM DA ATUALIZAÇÃO ---

             require_once 'utils.php';
             log_activity('LOGIN_SUCCESS', "Utilizador: {$email}");

            // Lógica de redirecionamento (já deve estar assim)
            if ($role == 'admin') {
                header('Location: dashboard.php');
            } else {
                header('Location: select_module.php');
            }
            exit();
        } 
        
        else {
            $error = 'Palavra-passe incorreta.';
        }
    } else {
        $error = 'Nenhum utilizador encontrado com esse email.';
    }
    $stmt->close();
}
$conn->close();
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - <?php echo APP_NAME; ?></title>
    <link rel="stylesheet" href="style.css">
</head>
<body class="auth-page">
    <div class="auth-container">
        <img src="<?php echo BASE_URL; ?>/images/logo-oficial.png" alt="<?php echo APP_NAME; ?> Logo" class="auth-logo">
        <h2>Login</h2>
        
        <?php if ($error): ?><div class="alert error"><?php echo $error; ?></div><?php endif; ?>
        <?php if ($registration_success): ?><div class="alert success">Registo concluído! Verifique o seu email e faça login.</div><?php endif; ?>
        
        <form method="POST" action="login.php">
            <input type="email" name="email" placeholder="Email" required>
            <div class="password-wrapper">
                <input type="password" name="password" id="password-input" placeholder="Palavra-passe" required>
                <span id="toggle-password" class="toggle-password-icon"></span>
            </div>
            <button type="submit">Entrar</button>
        </form>
        <p>Não tem uma conta? <a href="register.php">Registe-se aqui</a>.</p>
    </div>

    <!-- SCRIPT PARA CONTROLAR O "OLHO" DA PALAVRA-PASSE -->
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const togglePassword = document.querySelector('#toggle-password');
            const passwordInput = document.querySelector('#password-input');
            const eyeIcon = '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>';
            const eyeOffIcon = '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path><line x1="1" y1="1" x2="23" y2="23"></line></svg>';
            if (togglePassword && passwordInput) {
                togglePassword.innerHTML = eyeOffIcon;
                togglePassword.addEventListener('click', function () {
                    const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                    passwordInput.setAttribute('type', type);
                    this.innerHTML = type === 'password' ? eyeOffIcon : eyeIcon;
                });
            }
        });
    </script>
</body>
</html>