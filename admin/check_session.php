<?php
session_start();

header('Content-Type: application/json');

// Verificar si el usuario está logueado
$response = [
    'logged_in' => isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true
];

echo json_encode($response);
?>
