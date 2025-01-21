<?php
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    if ($username === "admin" && $password === "admin123") {
        $_SESSION['logged_in'] = true;
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Usuario o contraseña incorrectos']);
    }
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inicio de Sesión Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            min-height: 100vh;
            display: flex;
            align-items: center;
            background-color: transparent;
            margin: 0;
            padding: 20px;
        }
        .login-form {
            width: 100%;
            max-width: 400px;
            margin: 0 auto;
            padding: 2rem;
            background: white;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-12">
                <form class="login-form" id="loginForm" onsubmit="return handleSubmit(event)">
                    <div style="display: flex; justify-content: center; align-items: center;">
                        <img src="../assets/img/logos/edilsa-logo.png" style="width: 200px;">
                    </div>
                    <br/>
                    <h2 class="text-center mb-4">Inicio de Sesión Admin</h2>
                    
                    <div id="errorMessage" class="alert alert-danger d-none" role="alert"></div>

                    <div class="mb-3">
                        <label for="username" class="form-label">Usuario</label>
                        <input type="text" class="form-control" id="username" name="username" required>
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">Contraseña</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Iniciar Sesión</button>
                </form>
            </div>
        </div>
    </div>

    <script>
    function notifyParent() {
        console.log('Intentando notificar al padre');
        try {
            // Intentar con postMessage
            window.parent.postMessage('loginSuccess', '*');
            console.log('Mensaje enviado via postMessage');
            
            // También intentar con una función directa si está disponible
            if (window.parent.handleLoginSuccess) {
                window.parent.handleLoginSuccess();
                console.log('Función handleLoginSuccess llamada directamente');
            }
        } catch (e) {
            console.error('Error al notificar al padre:', e);
        }
    }

    function handleSubmit(event) {
        event.preventDefault();
        console.log('Formulario enviado');
        
        var formData = new FormData(event.target);
        
        fetch('login.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            console.log('Respuesta:', data);
            if (data.success) {
                console.log('Login exitoso, intentando notificar al padre');
                notifyParent();
                
                // Como respaldo, intentar cerrar el modal después de un breve retraso
                setTimeout(function() {
                    console.log('Intentando cerrar modal después de 1 segundo');
                    notifyParent();
                }, 1000);
            } else {
                document.getElementById('errorMessage').classList.remove('d-none');
                document.getElementById('errorMessage').textContent = data.error;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            document.getElementById('errorMessage').classList.remove('d-none');
            document.getElementById('errorMessage').textContent = 'Error en el servidor. Por favor intente más tarde.';
        });
        
        return false;
    }
    </script>
</body>
</html>
