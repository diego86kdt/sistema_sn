<?php
// includes/functions.php
if (session_status() === PHP_SESSION_NONE) session_start();

function validarEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

function limpar($str) {
    return trim(htmlspecialchars($str, ENT_QUOTES, 'UTF-8'));
}

/**
 * Lê a configuração de layout (JSON).
 * Retorna array com keys: modo, cor_menu, logo
 */
function lerConfigLayout() {
    $f = __DIR__ . '/../config_layout.json';
    if (!file_exists($f)) {
        $default = ['modo'=>'claro','cor_menu'=>'primary','logo'=>'assets/img/logo_sistema.png'];
        file_put_contents($f, json_encode($default, JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES));
        return $default;
    }
    $json = file_get_contents($f);
    $arr = json_decode($json, true);
    if (!is_array($arr)) {
        $arr = ['modo'=>'claro','cor_menu'=>'primary','logo'=>'assets/img/logo_sistema.png'];
    }
    return $arr;
}

/**
 * Salva configuração de layout (array).
 */
function salvarConfigLayout(array $config) {
    $f = __DIR__ . '/../config_layout.json';
    file_put_contents($f, json_encode($config, JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES));
    // atualiza sessão
    $_SESSION['modo'] = $config['modo'] ?? 'claro';
    $_SESSION['cor_menu'] = $config['cor_menu'] ?? 'primary';
    $_SESSION['logo'] = $config['logo'] ?? 'assets/img/logo_sistema.png';
    return true;
}
