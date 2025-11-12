<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/header.php';
?>
<div class="container mt-4">
    <h3>Painel Fiscal</h3>
    <div class="row g-3 mt-3">
        <div class="col-md-6"><div class="card p-3"><h5>Cálculo SN</h5><p>Realizar cálculo do DAS</p><a href="/fiscal/calculo_sn.php" class="btn btn-sm btn-primary">Abrir</a></div></div>
        <div class="col-md-6"><div class="card p-3"><h5>Layout</h5><p>Alterar modo claro/escuro</p><a href="/fiscal/layout.php" class="btn btn-sm btn-warning">Alterar</a></div></div>
    </div>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
