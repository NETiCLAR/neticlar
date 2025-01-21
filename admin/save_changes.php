<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', 'debug.log');

function debug_to_file($data, $label = '') {
    $debug_file = 'debug.log';
    $output = date('[Y-m-d H:i:s] ') . $label . ': ' . print_r($data, true) . "\n";
    file_put_contents($debug_file, $output, FILE_APPEND);
}

session_start();
header('Content-Type: application/json');

try {
    // Verificar la sesión
    if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
        throw new Exception('No autorizado - Estado de sesión: ' . json_encode($_SESSION));
    }

    if (!isset($_POST['section']) || !isset($_POST['changes'])) {
        throw new Exception('Datos incompletos: ' . json_encode($_POST));
    }

    $section = $_POST['section'];
    $changes = json_decode($_POST['changes'], true);
    
    if ($changes === null && json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception('Error al decodificar los cambios: ' . json_last_error_msg());
    }
    
    $file = '../index.html';
    if (!file_exists($file) || !is_readable($file) || !is_writable($file)) {
        throw new Exception('Problemas con el archivo index.html: ' . error_get_last()['message']);
    }
    
    // Crear backup
    $backup_dir = 'backups';
    if (!is_dir($backup_dir) && !mkdir($backup_dir, 0777, true)) {
        throw new Exception('No se pudo crear el directorio de backups');
    }
    
    $backup_file = $backup_dir . '/index_' . date('Y-m-d_H-i-s') . '.html';
    if (!copy($file, $backup_file)) {
        throw new Exception('No se pudo crear el backup');
    }
    
    // Leer el contenido actual
    $html = file_get_contents($file);
    if ($html === false) {
        throw new Exception('No se pudo leer el archivo');
    }
    
    // Configurar libxml
    $previous = libxml_use_internal_errors(true);
    
    // Crear un nuevo DOMDocument
    $dom = new DOMDocument('1.0', 'UTF-8');
    $dom->validateOnParse = true;
    $dom->preserveWhiteSpace = false;
    $dom->formatOutput = true;
    
    // Cargar el HTML con las etiquetas HTML5
    $dom->loadHTML($html, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD | LIBXML_NOERROR);
    
    // Procesar cada cambio
    foreach ($changes as $change) {
        if (!isset($change['element']) || !isset($change['content'])) {
            continue;
        }
        
        $elementParts = explode('-', $change['element']);
        if (count($elementParts) !== 2) {
            continue;
        }
        
        $elementType = $elementParts[0];
        $elementIndex = (int)$elementParts[1];
        
        // Encontrar la sección
        $xpath = new DOMXPath($dom);
        $sectionId = ltrim($section, '#');
        $sectionElement = $xpath->query("//*[@id='$sectionId']")->item(0);
        
        if (!$sectionElement) {
            continue;
        }
        
        // Actualizar el elemento
        if ($elementType === 'heading') {
            $headings = [];
            for ($i = 1; $i <= 6; $i++) {
                foreach ($sectionElement->getElementsByTagName('h' . $i) as $heading) {
                    $headings[] = $heading;
                }
            }
            
            if (isset($headings[$elementIndex])) {
                $heading = $headings[$elementIndex];
                $link = $heading->getElementsByTagName('a')->item(0);
                if ($link) {
                    $link->textContent = $change['content'];
                } else {
                    $heading->textContent = $change['content'];
                }
            }
        } elseif ($elementType === 'paragraph') {
            $paragraphs = $sectionElement->getElementsByTagName('p');
            if ($paragraphs->length > $elementIndex) {
                $paragraphs->item($elementIndex)->textContent = $change['content'];
            }
        } elseif ($elementType === 'list') {
            $listItems = $sectionElement->getElementsByTagName('li');
            if ($listItems->length > $elementIndex) {
                $listItems->item($elementIndex)->textContent = $change['content'];
            }
        }
    }
    
    // Obtener el HTML actualizado
    $html = $dom->saveHTML();
    
    // Restaurar la configuración de libxml
    libxml_use_internal_errors($previous);
    
    // Guardar los cambios
    if (file_put_contents($file, $html) === false) {
        throw new Exception('No se pudo escribir en el archivo');
    }
    
    echo json_encode([
        'success' => true,
        'message' => 'Cambios guardados correctamente',
        'backup' => basename($backup_file)
    ]);
    
} catch (Exception $e) {
    error_log("Error en save_changes.php: " . $e->getMessage());
    debug_to_file($e->getMessage(), 'ERROR');
    debug_to_file($e->getTraceAsString(), 'STACK TRACE');
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
        'trace' => $e->getTraceAsString()
    ]);
}
