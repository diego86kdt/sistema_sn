<?php
// admin/parametrizacao_sn.php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../includes/db.php';

$msg = '';
$msg_tipo = 'info';
$anexos = ['Anexo I','Anexo II','Anexo III','Anexo IV','Anexo V'];

// Inserir nova faixa
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'add') {
    $anexo = $_POST['anexo'] ?? '';
    $faixa = intval($_POST['faixa']);
    $valor_inicial = floatval(str_replace(',', '.', $_POST['valor_inicial']));
    $valor_final = floatval(str_replace(',', '.', $_POST['valor_final']));
    $aliquota = floatval(str_replace(',', '.', $_POST['aliquota']));
    $deducao = floatval(str_replace(',', '.', $_POST['deducao']));

    if (!$anexo || $faixa < 1 || $valor_inicial < 0 || $valor_final <= 0) {
        $msg = "Preencha corretamente todos os campos.";
        $msg_tipo = 'danger';
    } else {
        // contagem de faixas para o anexo
        $stmt = $conn->prepare("SELECT COUNT(*) as c FROM parametrizacao_sn WHERE anexo = ?");
        $stmt->bind_param("s", $anexo); 
        $stmt->execute();
        $count = $stmt->get_result()->fetch_assoc()['c'];

        if ($count >= 6) {
            $msg = "Já existem 6 faixas cadastradas para este anexo.";
            $msg_tipo = 'warning';
        } else {
            // faixa duplicada?
            $chk = $conn->prepare("SELECT id FROM parametrizacao_sn WHERE anexo = ? AND faixa = ?");
            $chk->bind_param("si", $anexo, $faixa); 
            $chk->execute();
            if ($chk->get_result()->num_rows > 0) {
                $msg = "Faixa já cadastrada para este anexo.";
                $msg_tipo = 'danger';
            } else {
                // checar valor_inicial = last_final + 0.01 quando houver faixa anterior
                $last = $conn->prepare("SELECT valor_final FROM parametrizacao_sn WHERE anexo = ? ORDER BY faixa DESC LIMIT 1");
                $last->bind_param("s", $anexo);
                $last->execute();
                $lastRow = $last->get_result()->fetch_assoc();
                if ($lastRow) {
                    $expected = round(floatval($lastRow['valor_final']) + 0.01, 2);
                    if (abs($valor_inicial - $expected) > 0.001) {
                        $msg = "Valor inicial deve ser valor final da faixa anterior + 0,01 (esperado R$ " . number_format($expected, 2, ',', '.') . ").";
                        $msg_tipo = 'danger';
                    }
                }
                if (!$msg) {
                    $ins = $conn->prepare("INSERT INTO parametrizacao_sn (anexo, faixa, valor_inicial, valor_final, aliquota, deducao) VALUES (?,?,?,?,?,?)");
                    $ins->bind_param("sidddd", $anexo, $faixa, $valor_inicial, $valor_final, $aliquota, $deducao);
                    $ins->execute();
                    $msg = "Faixa cadastrada com sucesso.";
                    $msg_tipo = 'success';
                }
            }
        }
    }
}

// Editar faixa existente
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'edit') {
    $id = intval($_POST['id']);
    $valor_inicial = floatval(str_replace(',', '.', $_POST['valor_inicial']));
    $valor_final = floatval(str_replace(',', '.', $_POST['valor_final']));
    $aliquota = floatval(str_replace(',', '.', $_POST['aliquota']));
    $deducao = floatval(str_replace(',', '.', $_POST['deducao']));

    if ($id > 0 && $valor_inicial >= 0 && $valor_final > $valor_inicial) {
        $upd = $conn->prepare("UPDATE parametrizacao_sn SET valor_inicial=?, valor_final=?, aliquota=?, deducao=? WHERE id=?");
        $upd->bind_param("ddddi", $valor_inicial, $valor_final, $aliquota, $deducao, $id);
        $upd->execute();
        $msg = "Faixa atualizada com sucesso.";
        $msg_tipo = 'success';
    } else {
        $msg = "Valores inválidos. Verifique os campos e tente novamente.";
        $msg_tipo = 'danger';
    }
}

// excluir
if (isset($_GET['del'])) {
    $id = intval($_GET['del']);
    $conn->query("DELETE FROM parametrizacao_sn WHERE id = $id");
    $msg = "Faixa removida.";
    $msg_tipo = 'warning';
}

// buscar faixas existentes
$res = $conn->query("SELECT * FROM parametrizacao_sn ORDER BY anexo, faixa ASC");
?>

<?php if ($msg): ?>
    <div class="alert alert-<?= htmlspecialchars($msg_tipo) ?>"><?= htmlspecialchars($msg) ?></div>
<?php endif; ?>

<form method="post" class="row g-3 mb-4">
    <input type="hidden" name="action" value="add">
    <div class="col-md-2">
        <label class="form-label">Anexo</label>
        <select name="anexo" class="form-select" required>
            <option value="">Selecione...</option>
            <?php foreach ($anexos as $a): ?>
                <option value="<?= htmlspecialchars($a) ?>"><?= htmlspecialchars($a) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="col-md-1">
        <label class="form-label">Faixa</label>
        <input type="number" name="faixa" class="form-control" min="1" max="6" required>
    </div>
    <div class="col-md-2"><label class="form-label">Valor Inicial</label><input type="number" step="0.01" name="valor_inicial" class="form-control" required></div>
    <div class="col-md-2"><label class="form-label">Valor Final</label><input type="number" step="0.01" name="valor_final" class="form-control" required></div>
    <div class="col-md-2"><label class="form-label">%</label><input type="number" step="0.01" name="aliquota" class="form-control" required></div>
    <div class="col-md-2"><label class="form-label">Dedução</label><input type="number" step="0.01" name="deducao" class="form-control" value="0.00" required></div>
    <div class="col-12 text-end">
        <button class="btn btn-secondary" type="reset">Limpar</button>
        <button class="btn btn-success">Salvar</button>
    </div>
</form>

<hr>
<h5>Faixas cadastradas</h5>
<div class="table-responsive">
<table class="table table-sm table-hover align-middle">
<thead>
<tr><th>Anexo</th><th>Faixa</th><th>Valor Inicial</th><th>Valor Final</th><th>%</th><th>Dedução</th><th class="text-center">Ações</th></tr>
</thead>
<tbody>
<?php while($f = $res->fetch_assoc()): ?>
<tr>
    <td><?= htmlspecialchars($f['anexo']) ?></td>
    <td><?= intval($f['faixa']) ?></td>
    <td>R$ <?= number_format($f['valor_inicial'],2,',','.') ?></td>
    <td>R$ <?= number_format($f['valor_final'],2,',','.') ?></td>
    <td><?= number_format($f['aliquota'],2,',','.') ?>%</td>
    <td>R$ <?= number_format($f['deducao'],2,',','.') ?></td>
    <td class="text-center">
        <button type="button" class="btn btn-sm btn-primary"
            data-bs-toggle="modal" data-bs-target="#editModal"
            data-id="<?= $f['id'] ?>"
            data-anexo="<?= htmlspecialchars($f['anexo']) ?>"
            data-faixa="<?= intval($f['faixa']) ?>"
            data-inicial="<?= $f['valor_inicial'] ?>"
            data-final="<?= $f['valor_final'] ?>"
            data-aliquota="<?= $f['aliquota'] ?>"
            data-deducao="<?= $f['deducao'] ?>">
            Editar
        </button>
        <a class="btn btn-sm btn-danger" href="?del=<?= $f['id'] ?>" onclick="return confirm('Excluir esta faixa?')">Excluir</a>
    </td>
</tr>
<?php endwhile; ?>
</tbody>
</table>
</div>

<!-- Modal de Edição -->
<div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <form method="post">
        <input type="hidden" name="action" value="edit">
        <input type="hidden" name="id" id="edit_id">
        <div class="modal-header bg-primary text-white">
          <h5 class="modal-title" id="editModalLabel">Editar Faixa</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label">Valor Inicial</label>
              <input type="number" step="0.01" id="edit_valor_inicial" name="valor_inicial" class="form-control" required>
            </div>
            <div class="col-md-6">
              <label class="form-label">Valor Final</label>
              <input type="number" step="0.01" id="edit_valor_final" name="valor_final" class="form-control" required>
            </div>
            <div class="col-md-6">
              <label class="form-label">%</label>
              <input type="number" step="0.01" id="edit_aliquota" name="aliquota" class="form-control" required>
            </div>
            <div class="col-md-6">
              <label class="form-label">Dedução</label>
              <input type="number" step="0.01" id="edit_deducao" name="deducao" class="form-control" required>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
          <button type="submit" class="btn btn-success">Salvar Alterações</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function(){
  var editModal = document.getElementById('editModal');
  editModal.addEventListener('show.bs.modal', function (event) {
    var button = event.relatedTarget;
    document.getElementById('edit_id').value = button.getAttribute('data-id');
    document.getElementById('edit_valor_inicial').value = button.getAttribute('data-inicial');
    document.getElementById('edit_valor_final').value = button.getAttribute('data-final');
    document.getElementById('edit_aliquota').value = button.getAttribute('data-aliquota');
    document.getElementById('edit_deducao').value = button.getAttribute('data-deducao');
    var titulo = 'Editar ' + button.getAttribute('data-anexo') + ' - Faixa ' + button.getAttribute('data-faixa');
    document.getElementById('editModalLabel').innerText = titulo;
  });
});
</script>
