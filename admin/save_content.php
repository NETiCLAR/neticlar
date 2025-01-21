<?php
session_start();
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    die(json_encode(['success' => false, 'message' => 'Acceso denegado']));
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die(json_encode(['success' => false, 'message' => 'Método no permitido']));
}

// Recibir los datos del formulario
$input = json_decode(file_get_contents('php://input'), true);
if ($input) {
    $section_id = $input['section_id'] ?? '';
    $content_data = $input['content'] ?? [];
} else {
    $section_id = $_POST['section_id'] ?? '';
    $content_data = $_POST['content'] ?? [];
}

if (empty($section_id) || empty($content_data)) {
    die(json_encode(['success' => false, 'message' => 'Datos incompletos']));
}

// Función para hacer backup del archivo
function backup_file($file_path) {
    if (!file_exists($file_path)) return false;
    $backup_dir = dirname($file_path) . '/backups';
    if (!is_dir($backup_dir)) {
        mkdir($backup_dir, 0777, true);
    }
    $backup_file = $backup_dir . '/' . basename($file_path) . '.' . date('Y-m-d-H-i-s') . '.bak';
    return copy($file_path, $backup_file);
}

// Determinar qué archivo necesita ser actualizado
$file_path = '';
if (strpos($section_id, 'service') !== false) {
    $file_path = '../service-details.html';
} else {
    $file_path = '../index.html';
}

// Hacer backup antes de modificar
backup_file($file_path);

// Leer el archivo HTML
$html = file_get_contents($file_path);
if ($html === false) {
    die(json_encode(['success' => false, 'message' => 'Error al leer el archivo']));
}

// Procesar cada cambio
foreach ($content_data as $element_id => $new_content) {
    // Determinar si es un encabezado o párrafo
    if (strpos($element_id, 'heading') !== false) {
        // Actualizar encabezado
        $pattern = '/<h[1-6][^>]*>.*?<\/h[1-6]>/is';
        $replacement = preg_replace($pattern, $new_content, $html, 1);
        if ($replacement !== null) {
            $html = $replacement;
        }
    } else {
        // Actualizar párrafo
        $pattern = '/<p[^>]*>.*?<\/p>/is';
        $replacement = preg_replace($pattern, "<p>" . htmlspecialchars($new_content) . "</p>", $html, 1);
        if ($replacement !== null) {
            $html = $replacement;
        }
    }
}

// Guardar los cambios
if (file_put_contents($file_path, $html) === false) {
    die(json_encode(['success' => false, 'message' => 'Error al guardar los cambios']));
}

echo json_encode(['success' => true, 'message' => 'Cambios guardados exitosamente']);
