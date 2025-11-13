<?php
// fiscal/calculo_sn.php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/header.php';

// buscar anexos disponíveis
$anexos_res = $conn->query("SELECT DISTINCT anexo FROM parametrizacao_sn ORDER BY anexo ASC");
$anexos = [];
while ($r = $anexos_res->fetch_assoc()) $anexos[] = $r['anexo'];
?>
<div class="container mt-4">
    <h3>Cálculo Simples Nacional</h3>
    <form id="calcForm" class="row g-3">
        <div class="col-md-4">
            <label class="form-label">Valor dos últimos 12 meses (R$)</label>
            <input id="valor12" type="number" step="0.01" class="form-control" min="0" value="0">
        </div>
        <div class="col-md-4">
            <label class="form-label">Anexo</label>
            <select id="anexo" class="form-select">
                <option value="">Selecione...</option>
                <?php foreach($anexos as $a): ?>
                    <option value="<?= htmlspecialchars($a) ?>"><?= htmlspecialchars($a) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-4">
            <label class="form-label">Receita Mensal (R$)</label>
            <input id="receita" type="number" step="0.01" class="form-control" min="0" value="0">
        </div>

        <div class="col-md-4">
            <label class="form-label">Faixa</label>
            <input id="faixa" class="form-control" readonly>
        </div>
        <div class="col-md-4">
            <label class="form-label">Alíquota (resultado)</label>
            <input id="aliquota" class="form-control" readonly>
        </div>
        <div class="col-md-4">
            <label class="form-label">Valor do DAS (R$)</label>
            <input id="valor_das" class="form-control" readonly>
        </div>

        <div class="col-12 text-end">
            <button type="button" id="btnCalc" class="btn btn-primary">Calcular</button>
            <button type="button" id="btnLimpar" class="btn btn-secondary">Limpar</button>
        </div>
    </form>

    <hr>
    <small class="text-muted">
        Observação: os dados das faixas são obtidos da parametrização cadastrada no módulo administrativo.
    </small>
</div>

<script>
async function buscaFaixas(anexo) {
    if (!anexo) return [];
    const res = await fetch('/fiscal/api_get_faixas.php?anexo=' + encodeURIComponent(anexo));
    if (!res.ok) return [];
    return res.json();
}

function formatBR(v, dec = 2) {
    return Number(v).toLocaleString('pt-BR', {
        minimumFractionDigits: dec,
        maximumFractionDigits: dec
    });
}

document.getElementById('btnCalc').addEventListener('click', async function() {
    const valor12 = parseFloat(document.getElementById('valor12').value) || 0;
    const receita = parseFloat(document.getElementById('receita').value) || 0;
    const anexo = document.getElementById('anexo').value;

    if (!anexo) {
        alert('Selecione o anexo');
        return;
    }

    const faixas = await buscaFaixas(anexo);
    let faixaSel = null;

    for (let f of faixas) {
        const vi = parseFloat(f.valor_inicial);
        const vf = parseFloat(f.valor_final);
        if (valor12 >= vi && valor12 <= vf) {
            faixaSel = f;
            break;
        }
    }

    if (!faixaSel) {
        alert('Nenhuma faixa encontrada para o valor informado.');
        return;
    }

    // Exibir nome + valores da faixa
    const vi = parseFloat(faixaSel.valor_inicial);
    const vf = parseFloat(faixaSel.valor_final);
    document.getElementById('faixa').value =
        'Faixa ' + faixaSel.faixa +
        ' (R$ ' + formatBR(vi, 2) + ' – R$ ' + formatBR(vf, 2) + ')';

    // Cálculo com precisão de 13 casas
    const aliquota_percent = parseFloat(faixaSel.aliquota) || 0;
    const deducao = parseFloat(faixaSel.deducao) || 0;
    let aliquota_decimal = 0;

    if (valor12 > 0) {
        aliquota_decimal = (valor12 * (aliquota_percent / 100) - deducao) / valor12;
        if (!isFinite(aliquota_decimal)) aliquota_decimal = 0;
    }

    // Alíquota exata com 13 casas
    const aliquota_display_percent = aliquota_decimal * 100;
    document.getElementById('aliquota').value = aliquota_display_percent.toFixed(13) + '%';

    // Valor do DAS usando a alíquota precisa
    const valorDAS = receita * (aliquota_display_percent / 100);
    document.getElementById('valor_das').value = 'R$ ' + formatBR(valorDAS, 2);
});

// limpar
document.getElementById('btnLimpar').addEventListener('click', function() {
    document.getElementById('calcForm').reset();
    document.getElementById('faixa').value = '';
    document.getElementById('aliquota').value = '';
    document.getElementById('valor_das').value = '';
});
</script>
