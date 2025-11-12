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
    <h4>Cadastro de Clientes (Em breve)</h4>
    <hr>
    <div class="alert alert-secondary">
        A funcionalidade de cadastro de clientes ainda não foi liberada nesta versão.
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
