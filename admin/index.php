<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/header.php';
?>
<div class="container mt-4">
    <h3>Painel Administrativo</h3>
    <div class="row g-3 mt-3">
        <div class="col-md-4">
            <div class="card p-3">
                <h5>Parametrização SN</h5>
                <p>Cadastre faixas e valores do Simples Nacional.</p>
                <a href="/admin_parametrizacao.php" class="btn btn-sm btn-primary">Abrir</a>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card p-3">
                <h5>Usuários</h5>
                <p>Gerencie o cadastro, edição e remoção de usuários.</p>
                <a href="/admin_usuarios.php" class="btn btn-sm btn-success">Abrir</a>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card p-3">
                <h5>Layout</h5>
                <p>Configurar tema, cor do menu e logotipo do sistema.</p>
                <a href="/admin/layout.php" class="btn btn-sm btn-warning">Abrir</a>
            </div>
        </div>
    </div>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
