<?php
// includes/functions.php
if (session_status() === PHP_SESSION_NONE) session_start();

/**
 * Validação básica de e-mail
 */
function validarEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

/**
 * Sanitização simples de strings
 */
function limpar($str) {
    return trim(htmlspecialchars($str, ENT_QUOTES, 'UTF-8'));
}

/**
 * Lê a configuração global de layout (JSON).
 * Retorna array com keys: modo, cor_menu, logo
 */
function lerConfigLayout() {
    $f = __DIR__ . '/../config_layout.json';
    if (!file_exists($f)) {
        $default = [
            'modo' => 'claro',
            'cor_menu' => 'primary',
            'logo' => 'assets/img/logo_sistema.png'
        ];
        file_put_contents($f, json_encode($default, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        return $default;
    }
    $json = file_get_contents($f);
    $arr = json_decode($json, true);
    if (!is_array($arr)) {
        $arr = [
            'modo' => 'claro',
            'cor_menu' => 'primary',
            'logo' => 'assets/img/logo_sistema.png'
        ];
    }
    return $arr;
}

/**
 * Salva configuração global de layout (JSON)
 */
function salvarConfigLayout(array $config) {
    $f = __DIR__ . '/../config_layout.json';
    file_put_contents($f, json_encode($config, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

    // Atualiza sessão com os novos dados
    $_SESSION['modo'] = $config['modo'] ?? 'claro';
    $_SESSION['cor_menu'] = $config['cor_menu'] ?? 'primary';
    $_SESSION['logo'] = $config['logo'] ?? 'assets/img/logo_sistema.png';
    return true;
}

/**
 * Retorna o modo de tema preferido do usuário (claro/escuro)
 */
function obterPreferenciaUsuario($usuarioId) {
    require __DIR__ . '/db.php';
    $stmt = $conn->prepare("SELECT tema_preferido FROM usuarios WHERE id = ?");
    $stmt->bind_param("i", $usuarioId);
    $stmt->execute();
    $res = $stmt->get_result()->fetch_assoc();
    return $res && $res['tema_preferido'] ? $res['tema_preferido'] : null;
}

/**
 * Salva a preferência de tema (claro/escuro) do usuário
 */
function salvarPreferenciaUsuario($usuarioId, $modo) {
    require __DIR__ . '/db.php';
    $modo = ($modo === 'escuro') ? 'escuro' : 'claro';
    $stmt = $conn->prepare("UPDATE usuarios SET tema_preferido = ? WHERE id = ?");
    $stmt->bind_param("si", $modo, $usuarioId);
    $stmt->execute();

    // Atualiza sessão também
    $_SESSION['modo'] = $modo;
    return true;
}

/**
 * Inicializa layout do usuário logado (prioriza preferências pessoais)
 */
function aplicarPreferenciaUsuario() {
    $cfg = lerConfigLayout();
    if (isset($_SESSION['usuario_id'])) {
        $modoUser = obterPreferenciaUsuario($_SESSION['usuario_id']);
        if ($modoUser) $cfg['modo'] = $modoUser;
    }

    $_SESSION['modo'] = $cfg['modo'];
    $_SESSION['cor_menu'] = $cfg['cor_menu'];
    $_SESSION['logo'] = $cfg['logo'];

    return $cfg;
}
