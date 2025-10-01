<?php

/**
 * Calcula e retorna o espaço total usado no diretório de um utilizador.
 *
 * @param string $user_root_path O caminho FTP completo para o diretório raiz do utilizador.
 * @return int O espaço total usado em bytes.
 */
function get_used_space($user_root_path) {
    require_once 'config.php';
    $used_space_bytes = 0;
    $conn_id = ftp_connect(FTP_SERVER);
    if ($conn_id && ftp_login($conn_id, FTP_USER, FTP_PASS)) {
        ftp_pasv($conn_id, true);
        // Chama a função recursiva para calcular o tamanho a partir do diretório raiz do utilizador
        $used_space_bytes = calculate_directory_size($conn_id, $user_root_path);
        ftp_close($conn_id);
    }
    return $used_space_bytes;
}

/**
 * Lista os ficheiros e diretórios de um caminho FTP específico com detalhes completos.
 * Esta função navega para o diretório de destino antes de listar, garantindo a localização correta.
 *
 * @param string $path O caminho FTP completo a ser listado.
 * @return array Um array com os detalhes dos ficheiros ou um array com uma chave 'error'.
 */
function listarArquivosFTP($path) {
    require_once 'config.php';
    
    $conn_id = ftp_connect(FTP_SERVER);
    if ($conn_id === false) {
        return ['error' => 'Falha ao ligar ao servidor FTP.'];
    }

    if (!ftp_login($conn_id, FTP_USER, FTP_PASS)) {
        ftp_close($conn_id);
        return ['error' => 'Falha no login do FTP.'];
    }

    ftp_pasv($conn_id, true);

    // Tenta navegar para o diretório. O @ suprime a mensagem de erro padrão do PHP.
    if (!@ftp_chdir($conn_id, $path)) {
        ftp_close($conn_id);
        // Retorna uma mensagem de erro clara se a pasta não for encontrada.
        return ['error' => "ERRO DE NAVEGAÇÃO: O diretório '$path' não foi encontrado no servidor FTP. Verifique se o caminho está correto e se a pasta existe."];
    }
    
    // Se chegou aqui, estamos no diretório certo. Lista o conteúdo local ('.').
    $contents = ftp_mlsd($conn_id, '.');
    ftp_close($conn_id);

    if ($contents === false) {
        return ['error' => "Não foi possível listar o conteúdo de '$path'."];
    }
    
    $items = [];
    foreach ($contents as $item) {
        if ($item['name'] != '.' && $item['name'] != '..') {
            $items[] = $item;
        }
    }
    return $items;
}

/**
 * Calcula o tamanho total (em bytes) de um diretório de forma recursiva.
 *
 * @param resource $conn_id O identificador da ligação FTP ativa.
 * @param string $directory O caminho do diretório a ser calculado.
 * @return int O tamanho total em bytes.
 */
function calculate_directory_size($conn_id, $directory) {
    $size = 0;
    // O @ suprime erros se o diretório não for legível, retornando um array vazio.
    $files = @ftp_mlsd($conn_id, $directory);
    if (empty($files)) {
        return 0;
    }
    foreach ($files as $file) {
        if ($file['name'] == '.' || $file['name'] == '..') {
            continue;
        }
        $path = rtrim($directory, '/') . '/' . $file['name'];
        if ($file['type'] == 'dir') {
            $size += calculate_directory_size($conn_id, $path);
        } else {
            $size += (int)$file['size'];
        }
    }
    return $size;
}

/**
 * Apaga um ficheiro ou um diretório (e todo o seu conteúdo) de forma recursiva.
 *
 * @param resource $conn_id O identificador da ligação FTP ativa.
 * @param string $path O caminho completo do ficheiro ou diretório a ser apagado.
 * @return bool Retorna true em caso de sucesso, false em caso de falha.
 */
function ftp_delete_recursive($conn_id, $path) {
    // Primeiro, verifica se o item é um diretório ou um ficheiro
    $details = @ftp_mlsd($conn_id, dirname($path));
    if ($details === false) {
        return false;
    }
    $is_dir = false;
    foreach($details as $detail) {
        if($detail['name'] == basename($path) && $detail['type'] == 'dir') {
            $is_dir = true;
            break;
        }
    }
    // Se não for um diretório, é um ficheiro. Basta apagar.
    if (!$is_dir) {
        return ftp_delete($conn_id, $path);
    }
    // Se for um diretório, precisamos de apagar o seu conteúdo primeiro
    $files = ftp_nlist($conn_id, $path);
    if ($files !== false) {
        foreach ($files as $file) {
            $basename = basename($file);
            if ($basename == '.' || $basename == '..') {
                continue;
            }
            ftp_delete_recursive($conn_id, $path . '/' . $basename);
        }
    }
    // Após o diretório estar vazio, remove o próprio diretório
    return ftp_rmdir($conn_id, $path);
}
?>