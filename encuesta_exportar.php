<?php
require 'conexion.php';
require 'includes/auth.php';
require 'includes/encuestas.php';
requerir_rol('administrativo');

$id  = (int)($_GET['id'] ?? 0);
$enc = enc_cargar($pdo, $id);
if (!$enc) {
    flash('La encuesta no existe.', 'danger');
    redirigir('encuestas.php');
}

$desde = enc_fecha_valida($_GET['fecha_desde'] ?? '');
$hasta = enc_fecha_valida($_GET['fecha_hasta'] ?? '');
$filtroSql = '';
$params    = [':enc' => $id];
if ($desde !== '') { $filtroSql .= ' AND r.fecha >= :desde'; $params[':desde'] = $desde . ' 00:00:00'; }
if ($hasta !== '') { $filtroSql .= ' AND r.fecha <= :hasta'; $params[':hasta'] = $hasta . ' 23:59:59'; }

$stmt = $pdo->prepare("SELECT r.id, r.fecha FROM encuesta_respuesta r WHERE r.encuesta_id = :enc $filtroSql ORDER BY r.fecha, r.id");
$stmt->execute($params);
$envios = $stmt->fetchAll();

$stmt = $pdo->prepare(
    "SELECT d.respuesta_id, d.pregunta_id, d.valor_texto, d.valor_escala, d.opcion_id
     FROM encuesta_respuesta_detalle d
     JOIN encuesta_respuesta r ON r.id = d.respuesta_id
     WHERE r.encuesta_id = :enc $filtroSql
     ORDER BY d.id"
);
$stmt->execute($params);
$celdas = [];   // [respuesta_id][pregunta_id][] = valor
foreach ($stmt->fetchAll() as $d) {
    $pid = (int)$d['pregunta_id'];
    if ($d['valor_texto'] !== null) {
        $valor = $d['valor_texto'];
    } elseif ($d['valor_escala'] !== null) {
        $valor = (string)$d['valor_escala'];
    } else {
        $valor = $enc['preguntas'][$pid]['opciones'][(int)$d['opcion_id']]['texto'] ?? '';
    }
    $celdas[(int)$d['respuesta_id']][$pid][] = $valor;
}

// Evita que Excel interprete como fórmula un texto que empieza con = + - @
function csv_seguro(string $s): string {
    return preg_match('/^[=+\-@\t\r]/', $s) ? "'" . $s : $s;
}

header('Content-Type: text/csv; charset=UTF-8');
header('Content-Disposition: attachment; filename="encuesta_' . $id . '_' . date('Ymd') . '.csv"');
echo "\xEF\xBB\xBF";   // BOM para que Excel respete las tildes
$out = fopen('php://output', 'w');

$cabecera = ['Fecha'];
foreach ($enc['preguntas'] as $p) { $cabecera[] = csv_seguro($p['texto']); }
fputcsv($out, $cabecera, ';', '"', '\\');

foreach ($envios as $env) {
    $fila = [$env['fecha']];
    foreach ($enc['preguntas'] as $pid => $p) {
        $fila[] = csv_seguro(implode(' | ', $celdas[(int)$env['id']][$pid] ?? []));
    }
    fputcsv($out, $fila, ';', '"', '\\');
}
fclose($out);
