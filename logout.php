<?php
// 1. Inicia a sessão para poder aceder a ela.
session_start();

// 2. Limpa todas as variáveis de sessão.
session_unset();

// 3. Destrói a sessão completamente.
session_destroy();

// 4. Redireciona o utilizador para a página de login.
header('Location: login.php');
exit();
?>