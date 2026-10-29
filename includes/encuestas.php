<?php
// Funciones compartidas del módulo de encuestas
require_once __DIR__ . '/funciones.php';

const ENC_TIPOS = [
    'texto'  => 'Texto libre',
    'escala' => 'Escala del 1 al 5',
    'opcion' => 'Opción múltiple',
];
const ENC_ESTADOS = [
    'borrador' => 'Borrador',
    'activa'   => 'Activa',
    'cerrada'  => 'Cerrada',
];
const ENC_MAX_PREGUNTAS       = 30;
const ENC_MIN_OPCIONES        = 2;
const ENC_MAX_OPCIONES        = 15;
const ENC_MAX_TEXTO_RESPUESTA = 1000;

function enc_estado_badge(string $estado): string {
    $clases = ['borrador' => 'bg-secondary', 'activa' => 'bg-success', 'cerrada' => 'bg-dark'];
    return '<span class="badge ' . ($clases[$estado] ?? 'bg-secondary') . '">'
         . e(ENC_ESTADOS[$estado] ?? $estado) . '</span>';
}

function enc_token_nuevo(PDO $pdo): string {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM encuesta WHERE token = :t");
    do {
        $token = bin2hex(random_bytes(6));   // 12 caracteres hexadecimales, igual que los documentos
        $stmt->execute([':t' => $token]);
    } while ((int)$stmt->fetchColumn() > 0);
    return $token;
}

function enc_total_respuestas(PDO $pdo, int $id): int {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM encuesta_respuesta WHERE encuesta_id = :id");
    $stmt->execute([':id' => $id]);
    return (int)$stmt->fetchColumn();
}

// Devuelve la encuesta con sus preguntas (y las opciones de cada una), o null si no existe
function enc_cargar(PDO $pdo, int $id): ?array {
    $stmt = $pdo->prepare("SELECT * FROM encuesta WHERE id = :id");
    $stmt->execute([':id' => $id]);
    $enc = $stmt->fetch();
    return $enc ? enc_cargar_preguntas($pdo, $enc) : null;
}

function enc_cargar_por_token(PDO $pdo, string $token): ?array {
    if (!preg_match('/^[a-f0-9]{12}$/', $token)) {
        return null;
    }
    $stmt = $pdo->prepare("SELECT * FROM encuesta WHERE token = :t");
    $stmt->execute([':t' => $token]);
    $enc = $stmt->fetch();
    return $enc ? enc_cargar_preguntas($pdo, $enc) : null;
}

function enc_cargar_preguntas(PDO $pdo, array $enc): array {
    $stmt = $pdo->prepare("SELECT * FROM encuesta_pregunta WHERE encuesta_id = :id ORDER BY orden, id");
    $stmt->execute([':id' => $enc['id']]);
    $preguntas = [];
    foreach ($stmt->fetchAll() as $p) {
        $p['opciones'] = [];
        $preguntas[(int)$p['id']] = $p;
    }
    if ($preguntas) {
        $stmt = $pdo->prepare(
            "SELECT o.* FROM encuesta_opcion o
             JOIN encuesta_pregunta p ON p.id = o.pregunta_id
             WHERE p.encuesta_id = :id
             ORDER BY o.orden, o.id"
        );
        $stmt->execute([':id' => $enc['id']]);
        foreach ($stmt->fetchAll() as $o) {
            $preguntas[(int)$o['pregunta_id']]['opciones'][(int)$o['id']] = $o;
        }
    }
    $enc['preguntas'] = $preguntas;
    return $enc;
}

// ---------- Formulario de creación / edición ----------

function enc_pregunta_vacia(): array {
    return ['texto' => '', 'tipo' => 'texto', 'obligatoria' => 0, 'multiple' => 0, 'opciones' => []];
}

// Lee y normaliza las preguntas enviadas por el formulario (en el orden en que llegan)
function enc_leer_preguntas_post(): array {
    $crudo = $_POST['preguntas'] ?? [];
    if (!is_array($crudo)) {
        return [];
    }
    $cadena = fn($v): string => is_string($v) ? $v : '';
    $res = [];
    foreach ($crudo as $p) {
        if (!is_array($p)) {
            continue;
        }
        $tipo = $cadena($p['tipo'] ?? '');
        if (!isset(ENC_TIPOS[$tipo])) {
            $tipo = 'texto';
        }
        $opciones = [];
        if ($tipo === 'opcion') {
            foreach ((preg_split('/\R/u', $cadena($p['opciones'] ?? '')) ?: []) as $linea) {
                $linea = trim($linea);
                if ($linea !== '') {
                    $opciones[] = $linea;
                }
            }
            $opciones = array_values(array_unique($opciones));
        }
        $res[] = [
            'texto'       => trim($cadena($p['texto'] ?? '')),
            'tipo'        => $tipo,
            'obligatoria' => !empty($p['obligatoria']) ? 1 : 0,
            'multiple'    => ($tipo === 'opcion' && !empty($p['multiple'])) ? 1 : 0,
            'opciones'    => $opciones,
        ];
    }
    return $res;
}

function enc_validar_preguntas(array $preguntas): array {
    $err = [];
    if (count($preguntas) === 0) {
        $err[] = 'Agrega al menos una pregunta.';
    }
    if (count($preguntas) > ENC_MAX_PREGUNTAS) {
        $err[] = 'Una encuesta no puede tener más de ' . ENC_MAX_PREGUNTAS . ' preguntas.';
    }
    foreach ($preguntas as $i => $p) {
        $n = $i + 1;
        if ($p['texto'] === '' || mb_strlen($p['texto']) > 300) {
            $err[] = "Pregunta $n: el texto es obligatorio y no puede superar los 300 caracteres.";
        }
        if ($p['tipo'] === 'opcion') {
            $c = count($p['opciones']);
            if ($c < ENC_MIN_OPCIONES || $c > ENC_MAX_OPCIONES) {
                $err[] = "Pregunta $n: indica entre " . ENC_MIN_OPCIONES . ' y ' . ENC_MAX_OPCIONES . ' opciones distintas (una por línea).';
            }
            foreach ($p['opciones'] as $op) {
                if (mb_strlen($op) > 150) {
                    $err[] = "Pregunta $n: cada opción puede tener hasta 150 caracteres.";
                    break;
                }
            }
        }
    }
    return $err;
}

// Inserta las preguntas y sus opciones. Se llama dentro de una transacción.
function enc_guardar_preguntas(PDO $pdo, int $encuestaId, array $preguntas): void {
    $insP = $pdo->prepare(
        "INSERT INTO encuesta_pregunta (encuesta_id, orden, texto, tipo, obligatoria, multiple)
         VALUES (:e, :o, :t, :tipo, :ob, :m)"
    );
    $insO = $pdo->prepare("INSERT INTO encuesta_opcion (pregunta_id, orden, texto) VALUES (:p, :o, :t)");
    foreach ($preguntas as $i => $p) {
        $insP->execute([
            ':e' => $encuestaId, ':o' => $i + 1, ':t' => $p['texto'], ':tipo' => $p['tipo'],
            ':ob' => $p['obligatoria'], ':m' => $p['multiple'],
        ]);
        $preguntaId = (int)$pdo->lastInsertId();
        foreach ($p['opciones'] as $j => $texto) {
            $insO->execute([':p' => $preguntaId, ':o' => $j + 1, ':t' => $texto]);
        }
    }
}

// ---------- Filtro de fechas de los resultados ----------

// Devuelve la fecha (YYYY-MM-DD) si es válida; si no, cadena vacía
function enc_fecha_valida($valor): string {
    if (!is_string($valor) || !preg_match('/^(\d{4})-(\d{2})-(\d{2})$/', $valor, $m)) {
        return '';
    }
    return checkdate((int)$m[2], (int)$m[3], (int)$m[1]) ? $valor : '';
}
