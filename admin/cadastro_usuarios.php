<?php
session_start();
require_once __DIR__.'/../includes/auth.php';
require_once __DIR__.'/../includes/db.php';

if ($_SESSION['perfil'] !== 'administrador') {
  header('Location: ../fiscal/index.php');
  exit;
}

$msg='';
if ($_SERVER['REQUEST_METHOD']==='POST') {
  $acao = $_POST['acao'] ?? '';
  if ($acao==='cadastrar') {
    $nome=trim($_POST['nome']); $email=trim($_POST['email']);
    $usuario=trim($_POST['usuario']); $senha=$_POST['senha']; $confirma=$_POST['confirma'];
    $data=$_POST['data_nascimento']; $perfil=$_POST['perfil'];
    if(!$nome||!$email||!$usuario||!$senha||!$confirma||!$data||!$perfil){
      $msg='Preencha todos os campos.'; 
    }elseif(!filter_var($email,FILTER_VALIDATE_EMAIL)){
      $msg='E-mail inválido.';
    }elseif($senha!==$confirma){
      $msg='Senhas diferentes.';
    }else{
      $sql=$conn->prepare('SELECT id FROM usuarios WHERE usuario=?');
      $sql->bind_param('s',$usuario); $sql->execute();
      if($sql->get_result()->num_rows>0){ $msg='Usuário já existe.'; }
      else{
        $hash=password_hash($senha,PASSWORD_DEFAULT);
        $ins=$conn->prepare('INSERT INTO usuarios(nome,email,usuario,senha,data_nascimento,perfil) VALUES (?,?,?,?,?,?)');
        $ins->bind_param('ssssss',$nome,$email,$usuario,$hash,$data,$perfil);
        $ins->execute(); $msg='Usuário cadastrado.';
      }
    }
  }
  if(isset($_GET['del'])){
    $id=intval($_GET['del']); $conn->query("DELETE FROM usuarios WHERE id=$id");
    $msg='Usuário removido.';
  }
}

$res=$conn->query('SELECT * FROM usuarios ORDER BY nome');
include __DIR__.'/../includes/header.php';
include __DIR__.'/../includes/menu_admin.php';
?>

<div class="container mt-4">
  <h3>Cadastro de Usuários</h3>
  <?php if($msg): ?><div class="alert alert-info"><?=htmlspecialchars($msg)?></div><?php endif; ?>

  <form method="post" class="row g-3 mb-4">
    <input type="hidden" name="acao" value="cadastrar">
    <div class="col-md-6"><label class="form-label">Nome</label><input name="nome" class="form-control" required></div>
    <div class="col-md-6"><label class="form-label">E-mail</label><input name="email" type="email" class="form-control" required></div>
    <div class="col-md-4"><label class="form-label">Usuário</label><input name="usuario" class="form-control" required></div>
    <div class="col-md-4"><label class="form-label">Senha</label><input name="senha" type="password" class="form-control" required></div>
    <div class="col-md-4"><label class="form-label">Confirmar Senha</label><input name="confirma" type="password" class="form-control" required></div>
    <div class="col-md-4"><label class="form-label">Data de Nascimento</label><input name="data_nascimento" type="date" class="form-control" required></div>
    <div class="col-md-4">
      <label class="form-label">Perfil</label>
      <select name="perfil" class="form-select" required>
        <option value="">Selecione</option>
        <option value="administrador">Administrador</option>
        <option value="padrao">Padrão</option>
      </select>
    </div>
    <div class="col-12 text-end">
      <button class="btn btn-secondary" type="reset">Limpar</button>
      <button class="btn btn-primary" type="submit">Cadastrar</button>
    </div>
  </form>

  <div class="table-responsive">
    <table class="table table-sm table-striped">
      <thead><tr><th>Nome</th><th>E-mail</th><th>Usuário</th><th>Perfil</th><th></th></tr></thead>
      <tbody>
      <?php while($u=$res->fetch_assoc()): ?>
        <tr>
          <td><?=htmlspecialchars($u['nome'])?></td>
          <td><?=htmlspecialchars($u['email'])?></td>
          <td><?=htmlspecialchars($u['usuario'])?></td>
          <td><?=htmlspecialchars($u['perfil'])?></td>
          <td><a href="?del=<?=$u['id']?>" class="btn btn-danger btn-sm" onclick="return confirm('Excluir?')">Excluir</a></td>
        </tr>
      <?php endwhile; ?>
      </tbody>
    </table>
  </div>
</div>

<?php include __DIR__.'/../includes/footer.php'; ?>
