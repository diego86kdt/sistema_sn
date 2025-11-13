<?php
session_start();
require_once 'includes/db.php';
require_once 'includes/functions.php';

$erro = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuario = trim($_POST['usuario'] ?? '');
    $senha = $_POST['senha'] ?? '';

    $stmt = $conn->prepare('SELECT * FROM usuarios WHERE usuario = ? LIMIT 1');
    $stmt->bind_param('s', $usuario);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($res && $res->num_rows) {
        $u = $res->fetch_assoc();
        if (password_verify($senha, $u['senha'])) {

            // Define variáveis de sessão
            $_SESSION['usuario_id'] = $u['id'];
            $_SESSION['usuario'] = $u['usuario'];
            $_SESSION['nome'] = $u['nome'];
            $_SESSION['perfil'] = $u['perfil'];

            // Lê config global e preferência do usuário
            $cfg = lerConfigLayout();
            $modoUser = $u['tema_preferido'] ?? null;
            if ($modoUser && in_array($modoUser, ['claro', 'escuro'])) {
                $_SESSION['modo'] = $modoUser;
            } else {
                $_SESSION['modo'] = $cfg['modo'] ?? 'claro';
            }

            $_SESSION['cor_menu'] = $cfg['cor_menu'] ?? 'primary';
            $_SESSION['logo'] = $cfg['logo'] ?? 'assets/img/logo_sistema.png';

            // Redireciona para o módulo correto
            if ($_SESSION['perfil'] === 'administrador') {
                header('Location: admin_parametrizacao.php');
            } else {
                header('Location: fiscal/index.php');
            }
            exit;
        } else {
            $erro = 'Senha incorreta.';
        }
    } else {
        $erro = 'Usuário não encontrado.';
    }
}
?>
<!doctype html>
<html lang="pt-br">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Login - Sistema SN</title>
<link href="assets/css/bootstrap.min.css" rel="stylesheet">
<link href="assets/css/style.css" rel="stylesheet">
</head>
<body class="bg-light d-flex align-items-center justify-content-center" style="min-height:100vh">
<div class="card p-4 shadow" style="width:360px">
  <div class="text-center mb-3">
    <img src="assets/img/logo_sistema.png" alt="logo" style="height:56px">
  </div>
  <h5 class="text-center mb-3">Acesso ao Sistema</h5>

  <?php if($erro): ?>
    <div class="alert alert-danger"><?= htmlspecialchars($erro) ?></div>
  <?php endif; ?>

  <form method="post" autocomplete="off">
    <div class="mb-2">
      <label class="form-label">Usuário</label>
      <input name="usuario" class="form-control" required autofocus>
    </div>
    <div class="mb-3">
      <label class="form-label">Senha</label>
      <div class="input-group">
        <input name="senha" id="senha" type="password" class="form-control" required>
        <button class="btn btn-outline-secondary" type="button" id="toggleSenha">👁️</button>
      </div>
    </div>
    <div class="d-grid">
      <button class="btn btn-primary">Entrar</button>
    </div>
  </form>
</div>

<script>
document.getElementById('toggleSenha').addEventListener('click', function(){
  const campo = document.getElementById('senha');
  campo.type = campo.type === 'password' ? 'text' : 'password';
});
</script>
<script src="assets/js/bootstrap.bundle.min.js"></script>
</body>
</html>
