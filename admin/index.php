<?php
session_start();
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NETiCLAR - Panel Administrativo</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f8f9fa;
        }
        .sidebar {
            min-height: 100vh;
            background: #67b0d1;
            color: white;
        }
        .nav-link {
            color: rgba(255,255,255,.8);
            transition: all 0.3s ease;
            border-radius: 4px;
            margin-bottom: 5px;
        }
        .nav-link:hover {
            color: white;
            background-color: rgba(57, 92, 106, 0.5);
        }
        .nav-link.active {
            background-color: #395c6a !important;
            color: white;
        }
        .content-area {
            padding: 20px;
        }
        .content-editor {
            margin-bottom: 20px;
        }
        .content-section {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
            margin-bottom: 25px;
        }
        .content-section .form-label {
            font-weight: 500;
            color: #67b0d1;
        }
        .accordion-item {
            border: none;
            background: transparent;
        }
        .accordion-button {
            background: white;
            border-radius: 8px !important;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
            font-weight: 600;
            color: #67b0d1;
        }
        .accordion-button:not(.collapsed) {
            background: #395c6a;
            color: white;
        }
        .accordion-button:hover {
            background-color: #f8f9fa;
        }
        .accordion-button:not(.collapsed):hover {
            background: #395c6a;
        }
        .accordion-button:focus {
            box-shadow: none;
            border-color: transparent;
        }
        .accordion-body {
            padding: 20px 0;
        }
        /* Sobrescribir colores de Bootstrap */
        .btn-primary {
            background-color: #67b0d1;
            border-color: #67b0d1;
        }
        .btn-primary:hover {
            background-color: #395c6a;
            border-color: #395c6a;
        }
        .btn-primary:active, .btn-primary:focus {
            background-color: #395c6a !important;
            border-color: #395c6a !important;
            box-shadow: 0 0 0 0.25rem rgba(57, 92, 106, 0.25) !important;
        }
        .text-primary {
            color: #67b0d1 !important;
        }
        .form-control:focus {
            border-color: #67b0d1;
            box-shadow: 0 0 0 0.25rem rgba(103, 176, 209, 0.25);
        }
        .save-section {
            min-width: 200px;
            transition: all 0.3s ease;
        }
        .save-section:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3 col-lg-2 px-0 sidebar">
                <div class="d-flex flex-column p-3">
                    <a href="index.php" class="d-flex align-items-center mb-3 mb-md-0 me-md-auto text-white text-decoration-none">
                        <span class="fs-4">NETiCLAR Admin</span>
                    </a>
                    <hr>
                    <ul class="nav nav-pills flex-column mb-auto">
                        <li class="nav-item">
                            <a href="#" class="nav-link active" data-section="hero">
                                <i class="bi bi-house-door me-2"></i>
                                Hero Principal
                            </a>
                        </li>
                        <li>
                            <a href="#" class="nav-link" data-section="about">
                                <i class="bi bi-info-circle me-2"></i>
                                Acerca de
                            </a>
                        </li>
                        <li>
                            <a href="#" class="nav-link" data-section="services">
                                <i class="bi bi-gear me-2"></i>
                                Servicios
                            </a>
                        </li>
                        <li>
                            <a href="#" class="nav-link" data-section="portfolio">
                                <i class="bi bi-collection me-2"></i>
                                Portafolio
                            </a>
                        </li>
                        <li>
                            <a href="#" class="nav-link" data-section="team">
                                <i class="bi bi-people me-2"></i>
                                Equipo
                            </a>
                        </li>
                        <li>
                            <a href="#" class="nav-link" data-section="pricing">
                                <i class="bi bi-tag me-2"></i>
                                Precios
                            </a>
                        </li>
                        <li>
                            <a href="#" class="nav-link" data-section="faq">
                                <i class="bi bi-question-circle me-2"></i>
                                FAQ
                            </a>
                        </li>
                        <li>
                            <a href="#" class="nav-link" data-section="contact">
                                <i class="bi bi-envelope me-2"></i>
                                Contacto
                            </a>
                        </li>
                        <li>
                            <a href="#" class="nav-link" data-section="footer">
                                <i class="bi bi-layout-text-window-reverse me-2"></i>
                                Footer
                            </a>
                        </li>
                    </ul>
                    <hr>
                    <div class="dropdown">
                        <a href="logout.php" class="d-flex align-items-center text-white text-decoration-none">
                            <i class="bi bi-box-arrow-right me-2"></i>
                            Cerrar Sesión
                        </a>
                    </div>
                </div>
            </div>

            <!-- Main Content -->
            <div class="col-md-9 col-lg-10 content-area">
                <div class="container-fluid">
                    <div class="row">
                        <!-- Panel de edición -->
                        <div class="col-md-6">
                            <div class="editor-panel">
                                <?php include 'load_content.php'; ?>
                            </div>
                        </div>
                        <!-- Vista previa en tiempo real -->
                        <div class="col-md-6">
                            <div class="preview-panel">
                                <iframe id="preview-frame" src="" style="width: 100%; height: 100vh; border: none;"></iframe>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="js/live-update.js"></script>
    <script>
        // Cuando el documento esté listo
        $(document).ready(function() {
            // Manejar clics en los enlaces del menú
            $('.nav-link').click(function(e) {
                e.preventDefault();
                const section = $(this).data('section');
                loadSection(section);
            });

            // Cargar la sección hero por defecto
            loadSection('hero');
        });

        function loadSection(section) {
            // Actualizar clase activa en la barra lateral
            $('.nav-link').removeClass('active');
            $('[data-section="' + section + '"]').addClass('active');

            // Cargar el contenido de la sección
            $.ajax({
                url: 'load_content.php',
                method: 'GET',
                data: { section: section },
                success: function(response) {
                    $('.editor-panel').html(response);
                    // Reinicializar los editores después de cargar el contenido
                    document.querySelectorAll('.content-editor').forEach(editor => {
                        const sectionId = editor.dataset.sectionId;
                        const elementId = editor.dataset.elementId;
                        setupEditorSync(editor, sectionId, elementId);
                    });
                },
                error: function(xhr, status, error) {
                    console.error('Error al cargar la sección:', error);
                    $('.editor-panel').html('<div class="alert alert-danger">Error al cargar el contenido</div>');
                }
            });
        }
    </script>

    <!-- Toast para notificaciones -->
    <div class="toast-container position-fixed bottom-0 end-0 p-3">
        <div id="saveToast" class="toast align-items-center text-white bg-success border-0" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="d-flex">
                <div id="saveToastBody" class="toast-body">
                    Cambios guardados exitosamente
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
        </div>
    </div>
</body>
</html>
