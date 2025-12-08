<?php

ini_set('display_errors', 1);
ini_set('controller_override', 1);
error_reporting(E_ALL);

require_once 'header.php';
?>

<form action="/ControlRegistro" method="POST">
    <label for="nombre">Nombre:</label>
    <input type="text" id="nombre" name="nombre" required>
    <br>
    <label for="apellido">Apellido:</label>
    <input type="text" id="apellido" name="apellido" required>
    <br>
    <label for="email">Email:</label>
    <input type="email" id="email" name="email" required>
    <br>
    <label for="password">Password:</label>
    <input type="password" id="password" name="passwd" required>
    <br>
    <button type="submit">Registrarse</button>
</form>
<p>Si ya tienes una cuenta, puedes iniciar sesión <a href="/login"> aquí</a>.</p>

<?php

ini_set('display_errors', 1);
ini_set('controller_override', 1);
error_reporting(E_ALL);

require_once 'footer.php';
?>