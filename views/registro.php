<?php

ini_set('display_errors', 1);
ini_set('controller_override', 1);
error_reporting(E_ALL);

require_once 'header.php';
?>

<div class="d-flex flex-column flex-md-row align-items-center gap-3 p-3 justify-content-center" style="max-width:800px;margin:0 auto;">

    <!-- Formulario de registro -->
    <div class="text-start text-dark bg-light bg-opacity-75 rounded p-3" style="max-width:600px;">
        <h5 class="mb-3 text-center">Nueva Cuenta</h5>
        <form action="/ControlRegistro" method="POST">
            <div class="mb-3">
                <label for="nombre" class="form-label"><strong>Nombre:</strong></label>
                <input type="text" id="nombre" name="nombre" class="form-control" required>
            </div>
            <div class="mb-3">
                <label for="apellido" class="form-label"><strong>Apellido:</strong></label>
                <input type="text" id="apellido" name="apellido" class="form-control" required>
            </div>
            <div class="mb-3">
                <label for="email" class="form-label"><strong>Email:</strong></label>
                <input type="email" id="email" name="email" class="form-control" required>
            </div>
            <div class="mb-3">
                <label for="password" class="form-label"><strong>Password:</strong></label>
                <input type="password" id="password" name="passwd" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-primary">Registrarse</button>
        </form>
        <p class="mt-3">Si ya tienes una cuenta, puedes iniciar sesión <a href="/login">aquí</a>.</p>
    </div>
</div>

<?php

ini_set('display_errors', 1);
ini_set('controller_override', 1);
error_reporting(E_ALL);

require_once 'footer.php';
?>