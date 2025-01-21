<?php
$host = 'localhost';
$user = 'root';
$password = '';

// Crear conexión
$conn = new mysqli($host, $user, $password);

// Verificar conexión
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Leer el archivo SQL
$sql = file_get_contents(__DIR__ . '/neticlar_db.sql');

// Ejecutar las consultas SQL
if ($conn->multi_query($sql)) {
    do {
        // Almacenar el primer resultado
        if ($result = $conn->store_result()) {
            $result->free();
        }
        // Preparar siguiente resultado
    } while ($conn->more_results() && $conn->next_result());
    
    echo "Base de datos inicializada correctamente\n";
} else {
    echo "Error al inicializar la base de datos: " . $conn->error . "\n";
}

$conn->close();
?>
