<?php
// 1. Protege a página e carrega os dados do utilizador
require_once 'session_check.php';

// 2. Identifica qual módulo o utilizador quer aceder
$module_id = $_GET['module_id'] ?? 0;

// 3. Mapeamento de IDs para ficheiros
$module_map = [
    3 => 'driver.php',          // Meu Armazenamento
    4 => 'financeiro.php',      // Meu Financeiro
    // ... etc
];

// 4. Lógica do Roteador
if (array_key_exists($module_id, $module_map)) {
    $module_to_load = $module_map[$module_id];
    if (file_exists($module_to_load)) {
        require_once $module_to_load;
    } else {
        die("Erro: O ficheiro para o módulo ID {$module_id} não foi encontrado.");
    }
} else {
    // Se nenhum módulo for válido, redireciona para a seleção.
    header('Location: select_module.php');
    exit();
}
?>