<?php
header('Content-Type: application/json; charset=utf-8');
require_once 'config.php';

// Permitir sólo POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Método no permitido']);
    exit;
}

// Sanitizar entradas (ejemplo básico)
$nombre = trim($_POST['nombre'] ?? '');
$email = trim($_POST['email'] ?? '');
$telefono = trim($_POST['telefono'] ?? '');
$asunto = trim($_POST['asunto'] ?? '');
$mensaje = trim($_POST['mensaje'] ?? '');

// Validaciones mínimas
if ($nombre === '' || $email === '' || $asunto === '' || $mensaje === '') {
    http_response_code(400);
    echo json_encode(['error' => 'Faltan campos requeridos']);
    exit;
}

// Manejo de archivo opcional
$archivoNombre = null;
if (!empty($_FILES['archivo']) && $_FILES['archivo']['error'] === UPLOAD_ERR_OK) {
    $uploadsDir = __DIR__ . '/uploads';
    if (!is_dir($uploadsDir)) mkdir($uploadsDir, 0755, true);

    $tmpName = $_FILES['archivo']['tmp_name'];
    $origName = basename($_FILES['archivo']['name']);
    // crear nombre único para evitar colisiones
    $safeName = time() . '_' . preg_replace('/[^a-zA-Z0-9\.\-_]/', '_', $origName);
    $dest = $uploadsDir . '/' . $safeName;
    if (move_uploaded_file($tmpName, $dest)) {
        $archivoNombre = 'uploads/' . $safeName;
    }
}

// Insert usando prepared statement
$stmt = $mysqli->prepare("INSERT INTO contactos (nombre,email,telefono,asunto,mensaje,archivo) VALUES (?,?,?,?,?,?)");
if (!$stmt) {
    http_response_code(500);
    echo json_encode(['error' => 'Error en la consulta: ' . $mysqli->error]);
    exit;
}
$stmt->bind_param('ssssss', $nombre, $email, $telefono, $asunto, $mensaje, $archivoNombre);
$ok = $stmt->execute();
if (!$ok) {
    http_response_code(500);
    echo json_encode(['error' => 'No se pudo guardar: ' . $stmt->error]);
    exit;
}

echo json_encode(['success' => true, 'message' => 'Mensaje guardado correctamente']);