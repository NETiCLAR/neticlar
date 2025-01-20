<?php
session_start();
$html = file_get_contents('index.html');

// Agregar el ícono de autenticación al menú
$menuItem = '<li><a href="#contact">Contacto</a></li>';
$authItem = '<a href="#" id="authButton" data-bs-toggle="modal" data-bs-target="#loginModal"><i class="fas fa-user-lock" style="font-size:24px"></i></a>';
$html = str_replace($menuItem, $menuItem . $authItem, $html);

// Agregar los scripts necesarios antes del cierre del body
$scripts = '
<!-- Font Awesome -->
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">

<!-- Modal de Login sin sombras ni recuadro interior -->
<div class="modal fade" id="loginModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="margin: 0 auto; width: auto; max-width: 400px;">
        <div class="modal-content" 
             style="background: transparent; border: none; box-shadow: none; overflow: visible;">

            <!-- Botón de cerrar (X) -->
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"
                    style="
                        position: absolute;
                        top: -15px;
                        right: -15px;
                        z-index: 1055;
                        background-color: rgba(255, 255, 255, 0.7);
                        border-radius: 50%;
                        width: 35px;
                        height: 35px;
                        display: flex;
                        align-items: center;
                        justify-content: center;
                        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
                        cursor: pointer;">
            </button>

            <!-- Contenido del modal -->
            <div class="modal-body p-0" style="display: flex; justify-content: center;">
                <iframe id="loginFrame" src="admin/login.php"
                        style="width: 100%; border: none; display: block; background-color: transparent;"></iframe>
            </div>
        </div>
    </div>
</div>


<!-- Script de ajuste automático -->
<script>
    document.addEventListener("DOMContentLoaded", function() {
        const iframe = document.getElementById("loginFrame");

        // Función para ajustar la altura del iframe según el contenido
        function adjustModalHeight() {
            try {
                const iframeContent = iframe.contentWindow.document.body;
                iframe.style.height = iframeContent.scrollHeight + "px";
            } catch (error) {
                console.error("Error ajustando el iframe: ", error);
            }
        }

        // Escuchar eventos de carga y mensajes de error dentro del iframe
        iframe.addEventListener("load", adjustModalHeight);

        // Detectar cambios en el contenido del iframe (por ejemplo, error de autenticación)
        iframe.onload = function () {
            const iframeDoc = iframe.contentWindow.document;

            // Configurar un intervalo para detectar cambios en altura dinámicamente
            setInterval(() => {
                iframe.style.height = iframeDoc.body.scrollHeight + "px";
            }, 200);
        };
    });
</script>


<!-- Modal del Dashboard -->
<div class="modal fade" id="dashboardModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 95%; margin: 40px auto;">
        <div class="modal-content" 
             style="border: none; box-shadow: none; border-radius: 15px; overflow: hidden; position: relative;">

            <!-- Botón de cerrar (X) dentro del modal -->
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"
                    style="
                        position: absolute;
                        top: 10px; /* Dentro del modal */
                        right: 10px; /* Dentro del modal */
                        z-index: 1055;
                        background-color: rgba(255, 255, 255, 0.7); /* Fondo blanco semi-transparente */
                        border-radius: 50%; /* Circular */
                        width: 35px;
                        height: 35px;
                        display: flex;
                        align-items: center;
                        justify-content: center;
                        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2); /* Sombra ligera */
                        cursor: pointer;">
            </button>

            <!-- Contenido del modal -->
            <div class="modal-body p-0" style="height: 90vh;">
                <iframe id="dashboardFrame" src="" 
                        style="width: 100%; height: 100%; border: none; display: block;"></iframe>
            </div>
        </div>
    </div>
</div>

<!-- Script para cerrar el modal y recargar la página -->
<script>
    document.addEventListener("DOMContentLoaded", function() {
        const dashboardModal = document.getElementById("dashboardModal");
        const iframe = document.getElementById("dashboardFrame");

        // Evento al cerrar el modal del dashboard
        dashboardModal.addEventListener("hidden.bs.modal", function () {
            // Limpiar el contenido del iframe
            iframe.src = "";

            // Recargar la página principal
            window.location.reload();
        });
    });
</script>

<!-- Script -->
<script>
    document.addEventListener("DOMContentLoaded", function() {
        const dashboardModal = document.getElementById("dashboardModal");
        const dashboardFrame = document.getElementById("dashboardFrame");
        const loginModal = document.getElementById("loginModal");

        // Función para cerrar sesión desde el iframe del dashboard
        window.handleLogout = function() {
            // Cerrar el modal del dashboard
            $("#dashboardModal").modal("hide");

            // Establecer una bandera para evitar que se abra el modal de login
            sessionStorage.setItem("logoutFlag", "true");

            // Recargar la página principal
            window.location.reload();
        };

        // Evento para cerrar el modal del dashboard
        dashboardModal.addEventListener("hidden.bs.modal", function () {
            dashboardFrame.src = ""; // Limpiar el contenido del iframe
        });

        // Evitar que el modal de login se muestre después de cerrar sesión
        if (sessionStorage.getItem("logoutFlag") === "true") {
            sessionStorage.removeItem("logoutFlag");
            $("#loginModal").modal("hide"); // Asegurarse de cerrar el login modal
        }
    });
</script>


<script>
function checkAuthAndShowModal() {
    fetch("admin/check_session.php")
        .then(response => response.json())
        .then(data => {
            if (data.logged_in) {
                // Si está autenticado, mostrar directamente el dashboard
                var dashboardModal = new bootstrap.Modal(document.getElementById("dashboardModal"), {
                    backdrop: false
                });
                document.getElementById("dashboardFrame").src = "admin/index.html";
                dashboardModal.show();
            } else {
                // Si no está autenticado, mostrar el modal de login
                var loginModal = new bootstrap.Modal(document.getElementById("loginModal"));
                loginModal.show();
            }
        })
        .catch(error => {
            console.error("Error:", error);
            // En caso de error, mostrar el modal de login por defecto
            var loginModal = new bootstrap.Modal(document.getElementById("loginModal"));
            loginModal.show();
        });
}

document.addEventListener("DOMContentLoaded", function() {
    var authButtons = document.querySelectorAll("[data-bs-target=\"#loginModal\"]");
    authButtons.forEach(function(button) {
        button.removeAttribute("data-bs-target");
        button.onclick = checkAuthAndShowModal;
    });
});

$(document).ready(function() {
    function checkSession() {
        $.getJSON("admin/check_session.php", function(response) {
            if(response.logged_in) {
                $("#authButton i").removeClass("fa-user-lock").addClass("fa-user-check");
                $("#authButton")
                    .attr("data-bs-target", "#dashboardModal")
                    .off("click")
                    .on("click", function(e) {
                        $("#dashboardFrame").attr("src", "admin/index.html");
                    });
            } else {
                $("#authButton i").removeClass("fa-user-check").addClass("fa-user-lock");
                $("#authButton")
                    .attr("data-bs-target", "#loginModal");
            }
        });
    }

    // Verificar sesión al cargar
    checkSession();

    // Cuando se cierra el modal del dashboard
    $("#dashboardModal").on("hidden.bs.modal", function () {
        $("#dashboardFrame").attr("src", "");
        checkSession();
        location.reload();
    });

    // Cuando se cierra el modal de login
    $("#loginModal").on("hidden.bs.modal", function () {
        checkSession();
    });
});
</script>
';

// Insertar los scripts antes del cierre del body
$html = str_replace('</body>', $scripts . '</body>', $html);

echo $html;
?>
?>
