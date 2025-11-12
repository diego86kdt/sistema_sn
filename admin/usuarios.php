<?php
// admin/usuarios.php (incluído por wrapper)
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

$msg = '';

// Processar POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];

    if ($action === 'create') {
        $nome = limpar($_POST['nome']);
        $email = limpar($_POST['email']);
        $usuario = limpar($_POST['usuario']);
        $senha = $_POST['senha'] ?? '';
        $conf = $_POST['confirma'] ?? '';
        $data_nascimento = $_POST['data_nascimento'] ?? null;
        $perfil = $_POST['perfil'] ?? 'padrao';

        if (!$nome || !$email || !$usuario || !$senha || !$conf || !$data_nascimento || !$perfil) {
            $msg = "Todos os campos são obrigatórios.";
        } elseif (!validarEmail($email)) {
            $msg = "E-mail inválido.";
        } elseif ($senha !== $conf) {
            $msg = "As senhas não conferem.";
        } else {
            // duplicidade de usuário
            $chk = $conn->prepare("SELECT id FROM usuarios WHERE usuario = ? OR email = ? LIMIT 1");
            $chk->bind_param("ss", $usuario, $email);
            $chk->execute();
            if ($chk->get_result()->num_rows) {
                $msg = "Usuário ou e-mail já cadastrado.";
            } else {
                $hash = password_hash($senha, PASSWORD_DEFAULT);
                $ins = $conn->prepare("INSERT INTO usuarios (nome,email,usuario,senha,data_nascimento,perfil) VALUES (?,?,?,?,?,?)");
                $ins->bind_param("ssssss",$nome,$email,$usuario,$hash,$data_nascimento,$perfil);
                $ins->execute();
                $msg = "Usuário cadastrado com sucesso.";
            }
        }

    } elseif ($action === 'update' && isset($_POST['id'])) {
        $id = intval($_POST['id']);
        $nome = limpar($_POST['nome']);
        $email = limpar($_POST['email']);
        $data_nascimento = $_POST['data_nascimento'] ?? null;
        $perfil = $_POST['perfil'] ?? 'padrao';

        if (!$nome || !$email || !$data_nascimento) {
            $msg = "Preencha todos os campos obrigatórios.";
        } elseif (!validarEmail($email)) {
            $msg = "E-mail inválido.";
        } else {
            $upd = $conn->prepare("UPDATE usuarios SET nome=?, email=?, data_nascimento=?, perfil=? WHERE id=?");
            $upd->bind_param("ssssi", $nome, $email, $data_nascimento, $perfil, $id);
            $upd->execute();
            $msg = "Usuário atualizado.";
        }
    }
}

// Exclusão
if (isset($_GET['del'])) {
    $id = intval($_GET['del']);
    // não deixar apagar único admin
    $row = $conn->query("SELECT perfil FROM usuarios WHERE id = $id")->fetch_assoc();
    if ($row && $row['perfil'] === 'administrador') {
        $admins = $conn->query("SELECT COUNT(*) as c FROM usuarios WHERE perfil='administrador'")->fetch_assoc()['c'];
        if ($admins <= 1) {
            $msg = "Não é permitido remover o único administrador.";
        } else {
            $conn->query("DELETE FROM usuarios WHERE id = $id");
            $msg = "Usuário removido.";
        }
    } else {
        $conn->query("DELETE FROM usuarios WHERE id = $id");
        $msg = "Usuário removido.";
    }
}

// Buscar para edição
$editUser = null;
if (isset($_GET['edit'])) {
    $id = intval($_GET['edit']);
    $stmt = $conn->prepare("SELECT id,nome,email,usuario,data_nascimento,perfil FROM usuarios WHERE id = ?");
    $stmt->bind_param("i",$id); $stmt->execute();
    $editUser = $stmt->get_result()->fetch_assoc();
}

// Carregar lista
$users = $conn->query("SELECT id,nome,email,usuario,perfil,data_nascimento FROM usuarios ORDER BY nome ASC");
?>

<?php if($msg): ?><div class="alert alert-info"><?= htmlspecialchars($msg) ?></div><?php endif; ?>

<form method="post" class="row g-3 mb-4">
    <?php if ($editUser): ?>
        <input type="hidden" name="action" value="update">
        <input type="hidden" name="id" value="<?= intval($editUser['id']) ?>">
    <?php else: ?>
        <input type="hidden" name="action" value="create">
    <?php endif; ?>

    <div class="col-md-6">
        <label class="form-label">Nome</label>
        <input name="nome" class="form-control" value="<?= htmlspecialchars($editUser['nome'] ?? '') ?>" required>
    </div>

    <div class="col-md-6">
        <label class="form-label">E-mail</label>
        <input name="email" type="email" class="form-control" value="<?= htmlspecialchars($editUser['email'] ?? '') ?>" required>
    </div>

    <div class="col-md-4">
        <label class="form-label">Usuário</label>
        <input name="usuario" class="form-control" value="<?= htmlspecialchars($editUser['usuario'] ?? '') ?>" <?= $editUser ? 'readonly' : 'required' ?>>
    </div>

    <?php if (!$editUser): ?>
    <div class="col-md-4">
        <label class="form-label">Senha</label>
        <input name="senha" type="password" class="form-control" required>
    </div>

    <div class="col-md-4">
        <label class="form-label">Confirmar Senha</label>
        <input name="confirma" type="password" class="form-control" required>
    </div>
	<div class="col-md-4">
        <label class="form-label">Data de Nascimento</label>
        <input name="data_nascimento" type="date" class="form-control" value="<?= htmlspecialchars($editUser['data_nascimento'] ?? '') ?>" required>
    </div>

    <div class="col-md-4">
        <label class="form-label">Perfil</label>
        <select name="perfil" class="form-select" required>
            <option value="administrador" <?= ($editUser['perfil'] ?? '') === 'administrador' ? 'selected' : '' ?>>Administrador</option>
            <option value="padrao" <?= ($editUser['perfil'] ?? '') === 'padrao' ? 'selected' : '' ?>>Padrão</option>
        </select>
    </div>
    <?php else: ?>

	<div class="col-md-4">
        <label class="form-label">Data de Nascimento</label>
        <input name="data_nascimento" type="date" class="form-control" value="<?= htmlspecialchars($editUser['data_nascimento'] ?? '') ?>" required>
    </div>

    <div class="col-md-4">
        <label class="form-label">Perfil</label>
        <select name="perfil" class="form-select" required>
            <option value="administrador" <?= ($editUser['perfil'] ?? '') === 'administrador' ? 'selected' : '' ?>>Administrador</option>
            <option value="padrao" <?= ($editUser['perfil'] ?? '') === 'padrao' ? 'selected' : '' ?>>Padrão</option>
        </select>
    </div>
    <?php endif; ?>

    <div class="col-12 text-end">
        <?php if ($editUser): ?>
            <a href="/admin_usuarios.php" class="btn btn-secondary">Cancelar</a>
            <button class="btn btn-primary">Salvar</button>
        <?php else: ?>
            <button type="reset" class="btn btn-secondary">Limpar</button>
            <button class="btn btn-success">Cadastrar</button>
        <?php endif; ?>
    </div>
</form>

<hr>
<h5>Usuários cadastrados</h5>
<table class="table table-sm">
<thead><tr><th>Nome</th><th>E-mail</th><th>Usuário</th><th>Perfil</th><th>Nascimento</th><th>Ações</th></tr></thead>
<tbody>
<?php while($u = $users->fetch_assoc()): ?>
<tr>
    <td><?= htmlspecialchars($u['nome']) ?></td>
    <td><?= htmlspecialchars($u['email']) ?></td>
    <td><?= htmlspecialchars($u['usuario']) ?></td>
    <td><?= htmlspecialchars($u['perfil']) ?></td>
    <td><?= $u['data_nascimento'] ? date('d/m/Y', strtotime($u['data_nascimento'])) : '' ?></td>
    <td>
        <a class="btn btn-sm btn-warning" href="?edit=<?= $u['id'] ?>">Editar</a>
        <a class="btn btn-sm btn-danger" href="?del=<?= $u['id'] ?>" onclick="return confirm('Excluir este usuário?')">Excluir</a>
    </td>
</tr>
<?php endwhile; ?>
</tbody>
</table>
