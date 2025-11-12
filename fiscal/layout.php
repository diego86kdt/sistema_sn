<?php
require_once '../includes/auth.php';
require_once '../includes/header.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST') { $_SESSION['modo'] = $_POST['modo'] ?? 'claro'; $msg='Modo atualizado.'; }
?>
<h3>Alterar modo (usuário padrão)</h3>
<?php if(isset($msg)) echo '<div class="alert alert-success">'.htmlspecialchars($msg).'</div>'; ?>
<form method="post" class="row g-3"><div class="col-md-4"><label>Modo</label>
<select name="modo" class="form-select"><option value="claro">Claro</option><option value="escuro">Escuro</option></select></div>
<div class="col-12 text-end"><button class="btn btn-primary">Salvar</button></div></form>
<?php require_once '../includes/footer.php'; ?>