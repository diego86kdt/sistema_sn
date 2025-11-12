<?php
// fiscal/api_get_faixas.php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';
header('Content-Type: application/json; charset=utf-8');

$anexo = $_GET['anexo'] ?? '';
$stmt = $conn->prepare("SELECT id, anexo, faixa, valor_inicial, valor_final, aliquota, deducao FROM parametrizacao_sn WHERE anexo = ? ORDER BY faixa ASC");
$stmt->bind_param("s",$anexo); $stmt->execute();
$res = $stmt->get_result();
$arr = [];
while ($r = $res->fetch_assoc()) $arr[] = $r;
echo json_encode($arr);
