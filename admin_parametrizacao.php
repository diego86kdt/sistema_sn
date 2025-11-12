<?php
session_start();
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/db.php';

if ($_SESSION['perfil'] !== 'administrador') {
    header('Location: index.php');
    exit;
}

include __DIR__ . '/includes/header.php';
?>

<div class="container mt-4">
    <h4>Parametrização do Simples Nacional</h4>
    <hr>
    <?php include __DIR__ . '/admin/parametrizacao_sn.php'; ?>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
