<?php
// includes/salvar_tema.php
session_start();
require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/db.php';

if (!isset($_SESSION['usuario_id'])) exit;

$modo = $_POST['modo'] ?? 'claro';
$modo = ($modo === 'escuro') ? 'escuro' : 'claro';

if ($_SESSION['perfil'] === 'administrador') {
    // Atualiza o layout global (todos usuários)
    $cfg = lerConfigLayout();
    $cfg['modo'] = $modo;
    salvarConfigLayout($cfg);
} else {
    // Atualiza apenas o tema do usuário
    salvarPreferenciaUsuario($_SESSION['usuario_id'], $modo);
}

$_SESSION['modo'] = $modo;
echo 'ok';
