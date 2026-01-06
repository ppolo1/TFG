<?php

ini_set('display_errors', 1);
ini_set('controller_override', 1);
error_reporting(E_ALL);

require_once 'header.php';
?>

<div class="d-flex flex-column flex-md-row align-items-center gap-3 p-3 justify-content-center" style="max-width:800px;margin:0 auto;">

    <!-- Formulario de login -->
    <div class="text-start text-dark bg-light bg-opacity-75 rounded p-3" style="max-width:600px;">
        <h5 class="mb-3 text-center">Inicio de sesión</h5>
        <form action="/ControlLogin" method="POST">
            <div class="mb-3">
                <label for="email" class="form-label"><strong>Email:</strong></label>
                <input type="email" id="email" name="email" class="form-control" required>
            </div>
            <div class="mb-3">
                <label for="passwd" class="form-label"><strong>Password:</strong></label>
                <input type="password" id="passwd" name="passwd" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-primary">Login</button>
        </form>
        <p class="mt-3">Si aún no tienes una cuenta, puedes registrarte <a href="/registro">aquí</a>.</p>
    </div>
</div>

<?php

ini_set('display_errors', 1);
ini_set('controller_override', 1);
error_reporting(E_ALL);

require_once 'footer.php';
?>