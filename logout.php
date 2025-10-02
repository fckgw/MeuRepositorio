<?php
/**
 * Script de Logout
 * Destrói a sessão atual e redireciona o utilizador para a página de login.
 */

// 1. Inicia a sessão para poder aceder e modificar os dados da sessão.
session_start();

// 2. Limpa todas as variáveis da sessão (ex: $_SESSION['user_id']).
session_unset();

// 3. Destrói a sessão completamente no servidor.
session_destroy();

// 4. Redireciona o utilizador para a página de login.
// É importante chamar exit() depois de um redirecionamento para garantir
// que nenhum outro código seja executado.
header('Location: login.php');
exit();
?>