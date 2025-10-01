<?php
// O session_start() é necessário para definir as variáveis de sessão APÓS o login bem-sucedido.
session_start();

// Se o utilizador já estiver autenticado, redireciona para o driver.
if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}

// Inclui apenas os ficheiros necessários para o login. NÃO INCLUI session_check.php.
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
            
            // Redireciona para a página principal do driver.
            header('Location: index.php');
            exit();
        } else {
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
        <h1><?php echo APP_NAME; ?></h1>
        <h2>Login</h2>
        
        <?php if ($error): ?><div class="alert error"><?php echo $error; ?></div><?php endif; ?>
        <?php if ($registration_success): ?><div class="alert success">Registo concluído com sucesso! Pode fazer login.</div><?php endif; ?>
        
        <form method="POST" action="login.php">
            <input type="email" name="email" placeholder="Email" required>
            <input type="password" name="password" placeholder="Palavra-passe" required>
            <button type="submit">Entrar</button>
        </form>
        <p>Não tem uma conta? <a href="register.php">Registe-se aqui</a>.</p>
        <!-- <a href="#" class="google-btn">Login com Google</a> -->
    </div>
</body>
</html>