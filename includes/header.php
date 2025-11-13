<?php
// includes/header.php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/functions.php';

// Aplica preferências do usuário e carrega layout
$config = aplicarPreferenciaUsuario();

$modo = $_SESSION['modo'] ?? ($config['modo'] ?? 'claro');
$cor_menu = $_SESSION['cor_menu'] ?? ($config['cor_menu'] ?? 'primary');
$logo = $_SESSION['logo'] ?? ($config['logo'] ?? 'assets/img/logo_sistema.png');
$perfil = $_SESSION['perfil'] ?? 'padrao';
$usuarioId = $_SESSION['usuario_id'] ?? 0;
?>
<!doctype html>
<html lang="pt-br" data-bs-theme="<?= $modo === 'escuro' ? 'dark' : 'light' ?>">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Sistema SN</title>
  <link href="/assets/css/bootstrap.min.css" rel="stylesheet">
  <link href="/assets/css/style.css" rel="stylesheet">
  <script src="/assets/js/bootstrap.bundle.min.js"></script>
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-<?= htmlspecialchars($cor_menu) ?> shadow-sm">
  <div class="container-fluid">
    <a class="navbar-brand d-flex align-items-center" href="/index.php">
      <img src="/<?= htmlspecialchars($logo) ?>" alt="Logo" height="38" style="object-fit:contain;">
    </a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navMain">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navMain">
      <ul class="navbar-nav me-auto mb-2 mb-lg-0">
        <?php if ($perfil === 'administrador'): ?>
          <li class="nav-item"><a class="nav-link" href="/admin/index.php">Admin</a></li>
          <li class="nav-item"><a class="nav-link" href="/admin_parametrizacao.php">Parametrização</a></li>
          <li class="nav-item"><a class="nav-link" href="/admin_usuarios.php">Usuários</a></li>
          <li class="nav-item"><a class="nav-link" href="/admin/layout.php">Layout</a></li>
        <?php endif; ?>
        <li class="nav-item"><a class="nav-link" href="/fiscal/index.php">Fiscal</a></li>
      </ul>
      <div class="d-flex align-items-center">
        <button id="btnToggleTheme" class="btn btn-outline-light btn-sm me-2">
          <?= $modo === 'escuro' ? 'Modo Claro' : 'Modo Escuro' ?>
        </button>
        <span class="me-2"><?= htmlspecialchars($_SESSION['nome'] ?? '') ?></span>
        <a class="btn btn-outline-light btn-sm" href="/logout.php">Sair</a>
      </div>
    </div>
  </div>
</nav>

<main class="container my-4">

<script>
document.addEventListener('DOMContentLoaded', () => {
  const btn = document.getElementById('btnToggleTheme');
  if (!btn) return;

  btn.addEventListener('click', async () => {
    const current = document.documentElement.getAttribute('data-bs-theme');
    const newMode = (current === 'dark') ? 'claro' : 'escuro';
    document.documentElement.setAttribute('data-bs-theme', newMode === 'escuro' ? 'dark' : 'light');
    btn.textContent = (newMode === 'escuro') ? 'Modo Claro' : 'Modo Escuro';

    // Salva preferência
    try {
      await fetch('/includes/salvar_tema.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'modo=' + encodeURIComponent(newMode)
      });
    } catch (e) { console.error(e); }
  });
});
</script>
