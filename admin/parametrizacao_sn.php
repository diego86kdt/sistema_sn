<?php
// admin/parametrizacao_sn.php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../includes/db.php';

$msg = '';
$anexos = ['Anexo I','Anexo II','Anexo III','Anexo IV','Anexo V'];

// Inserir nova faixa
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add') {
    $anexo = $_POST['anexo'] ?? '';
    $faixa = intval($_POST['faixa']);
    $valor_inicial = floatval(str_replace(',', '.', $_POST['valor_inicial']));
    $valor_final = floatval(str_replace(',', '.', $_POST['valor_final']));
    $aliquota = floatval(str_replace(',', '.', $_POST['aliquota']));
    $deducao = floatval(str_replace(',', '.', $_POST['deducao']));

    if (!$anexo || $faixa < 1 || $valor_inicial < 0 || $valor_final <= 0) {
        $msg = "Preencha corretamente todos os campos.";
    } else {
        // contagem de faixas para o anexo
        $stmt = $conn->prepare("SELECT COUNT(*) as c FROM parametrizacao_sn WHERE anexo = ?");
        $stmt->bind_param("s", $anexo); $stmt->execute();
        $count = $stmt->get_result()->fetch_assoc()['c'];
        if ($count >= 6) {
            $msg = "Já existem 6 faixas cadastradas para este anexo.";
        } else {
            // faixa duplicada?
            $chk = $conn->prepare("SELECT id FROM parametrizacao_sn WHERE anexo = ? AND faixa = ?");
            $chk->bind_param("si",$anexo,$faixa); $chk->execute();
            if ($chk->get_result()->num_rows > 0) {
                $msg = "Faixa já cadastrada para este anexo.";
            } else {
                // checar valor_inicial = last_final + 0.01 quando houver faixa anterior
                $last = $conn->prepare("SELECT valor_final FROM parametrizacao_sn WHERE anexo = ? ORDER BY faixa DESC LIMIT 1");
                $last->bind_param("s",$anexo); $last->execute();
                $lastRow = $last->get_result()->fetch_assoc();
                if ($lastRow) {
                    $expected = round(floatval($lastRow['valor_final']) + 0.01, 2);
                    if (abs($valor_inicial - $expected) > 0.001) {
                        $msg = "Valor inicial deve ser valor final da faixa anterior + 0,01 (esperado R$ " . number_format($expected,2,',','.') . ").";
                    }
                }
                if (!$msg) {
                    $ins = $conn->prepare("INSERT INTO parametrizacao_sn (anexo, faixa, valor_inicial, valor_final, aliquota, deducao) VALUES (?,?,?,?,?,?)");
                    $ins->bind_param("sidddd",$anexo,$faixa,$valor_inicial,$valor_final,$aliquota,$deducao);
                    $ins->execute();
                    $msg = "Faixa cadastrada com sucesso.";
                }
            }
        }
    }
}

// excluir
if (isset($_GET['del'])) {
    $id = intval($_GET['del']);
    $conn->query("DELETE FROM parametrizacao_sn WHERE id = $id");
    $msg = "Faixa removida.";
}

// buscar faixas existentes
$res = $conn->query("SELECT * FROM parametrizacao_sn ORDER BY anexo, faixa ASC");
?>

<?php if ($msg): ?>
    <div class="alert alert-info"><?= htmlspecialchars($msg) ?></div>
<?php endif; ?>

<form method="post" class="row g-3 mb-4">
    <input type="hidden" name="action" value="add">
    <div class="col-md-2">
        <label class="form-label">Anexo</label>
        <select name="anexo" class="form-select" required>
            <option value="">Selecione...</option>
            <?php foreach($anexos as $a): ?><option value="<?= htmlspecialchars($a) ?>"><?= htmlspecialchars($a) ?></option><?php endforeach; ?>
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
    <div class="col-12 text-end"><button class="btn btn-secondary" type="reset">Limpar</button><button class="btn btn-success">Salvar</button></div>
</form>

<hr>
<h5>Faixas cadastradas</h5>
<div class="table-responsive">
<table class="table table-sm table-hover">
<thead><tr><th>Anexo</th><th>Faixa</th><th>Valor Inicial</th><th>Valor Final</th><th>%</th><th>Dedução</th><th>Ações</th></tr></thead>
<tbody>
<?php while($f = $res->fetch_assoc()): ?>
<tr>
    <td><?= htmlspecialchars($f['anexo']) ?></td>
    <td><?= intval($f['faixa']) ?></td>
    <td>R$ <?= number_format($f['valor_inicial'],2,',','.') ?></td>
    <td>R$ <?= number_format($f['valor_final'],2,',','.') ?></td>
    <td><?= number_format($f['aliquota'],2,',','.') ?>%</td>
    <td>R$ <?= number_format($f['deducao'],2,',','.') ?></td>
    <td><a class="btn btn-sm btn-danger" href="?del=<?= $f['id'] ?>" onclick="return confirm('Excluir esta faixa?')">Excluir</a></td>
</tr>
<?php endwhile; ?>
</tbody>
</table>
</div>
