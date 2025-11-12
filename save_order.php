<?php
header('Content-Type: application/json; charset=utf-8');
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Método no permitido']);
    exit;
}

// Recibir JSON o form-data
// Suponemos que el frontend enviará FormData con 'items' como JSON string
$cliente_nombre = trim($_POST['nombre'] ?? '');
$cliente_apellido = trim($_POST['apellido'] ?? '');
$cliente_celular = trim($_POST['celular'] ?? '');
$cliente_direccion = trim($_POST['direccion'] ?? '');
$cliente_correo = trim($_POST['correo'] ?? '');
$items_json = $_POST['items'] ?? '[]';
$total = floatval($_POST['total'] ?? 0.0);
$metodo_pago = trim($_POST['metodo_pago'] ?? NULL);

// Validación mínima
if ($cliente_nombre === '' || $cliente_apellido === '' || $cliente_celular === '' || $cliente_direccion === '' || $cliente_correo === '') {
    http_response_code(400);
    echo json_encode(['error' => 'Faltan campos requeridos']);
    exit;
}

// Guardar
$stmt = $mysqli->prepare("INSERT INTO pedidos (cliente_nombre, cliente_apellido, cliente_celular, cliente_direccion, cliente_correo, items, total, metodo_pago) VALUES (?,?,?,?,?,?,?,?)");
if (!$stmt) {
    http_response_code(500);
    echo json_encode(['error' => 'Error en la consulta: ' . $mysqli->error]);
    exit;
}
$stmt->bind_param('ssssssds', $cliente_nombre, $cliente_apellido, $cliente_celular, $cliente_direccion, $cliente_correo, $items_json, $total, $metodo_pago);
$ok = $stmt->execute();
if (!$ok) {
    http_response_code(500);
    echo json_encode(['error' => 'No se pudo guardar: ' . $stmt->error]);
    exit;
}

echo json_encode(['success' => true, 'message' => 'Pedido guardado correctamente', 'order_id' => $stmt->insert_id]);