<?php
session_start();
require_once __DIR__.'/../includes/auth.php';
require_once __DIR__.'/../includes/db.php';
include __DIR__.'/../includes/header.php';
include __DIR__.'/../includes/menu_admin.php';

$isAdmin = ($_SESSION['perfil'] ?? '') === 'administrador';
$msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $modo = $_POST['modo'] ?? 'claro';
  $cor = $_POST['cor_menu'] ?? 'primary';
  $logoPath = null;

  if ($isAdmin && !empty($_FILES['logo']['name'])) {
    $dir = __DIR__.'/../assets/img/';
    if(!is_dir($dir)) mkdir($dir,0755,true);
    $ext = pathinfo($_FILES['logo']['name'], PATHINFO_EXTENSION);
    $file = 'logo_sistema.'.$ext;
    move_uploaded_file($_FILES['logo']['tmp_name'],$dir.$file);
    $logoPath = 'assets/img/'.$file;
  }

  // Atualiza no banco
  if($isAdmin){
    $stmt = $conn->prepare("UPDATE configuracoes_layout SET modo_tema=?, cor_menu=?, logotipo=? WHERE id=1");
    $stmt->bind_param('sss',$modo,$cor,$logoPath);
    $stmt->execute();
  }
  $msg = 'Configurações salvas.';
}

// Carrega configuração atual
$res = $conn->query("SELECT * FROM configuracoes_layout WHERE id=1");
$conf = $res->fetch_assoc();
?>

<div class="container mt-4">
  <h3>Personalização do Layout</h3>
  <?php if($msg): ?><div class="alert alert-success"><?=$msg?></div><?php endif; ?>

  <form method="post" enctype="multipart/form-data" class="row g-3">
    <div class="col-md-4">
      <label class="form-label">Modo</label>
      <select name="modo" class="form-select">
        <option value="claro" <?=$conf['modo_tema']=='claro'?'selected':''?>>Claro</option>
        <option value="escuro" <?=$conf['modo_tema']=='escuro'?'selected':''?>>Escuro</option>
      </select>
    </div>

    <?php if($isAdmin): ?>
      <div class="col-md-4">
        <label class="form-label">Cor do Menu</label>
        <select name="cor_menu" class="form-select">
          <option value="primary" <?=$conf['cor_menu']=='primary'?'selected':''?>>Azul</option>
          <option value="danger" <?=$conf['cor_menu']=='danger'?'selected':''?>>Vermelho</option>
          <option value="dark" <?=$conf['cor_menu']=='dark'?'selected':''?>>Preto</option>
          <option value="success" <?=$conf['cor_menu']=='success'?'selected':''?>>Verde</option>
        </select>
      </div>
      <div class="col-md-4">
        <label class="form-label">Logotipo</label>
        <input type="file" name="logo" class="form-control" accept="image/*">
        <?php if($conf['logotipo']): ?>
          <img src="/<?=$conf['logotipo']?>" alt="Logo" style="height:50px;margin-top:5px;">
        <?php endif; ?>
      </div>
    <?php endif; ?>

    <div class="col-12 text-end">
      <button class="btn btn-primary" type="submit">Salvar</button>
    </div>
  </form>
</div>

<?php include __DIR__.'/../includes/footer.php'; ?>
