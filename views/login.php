<?php

ini_set('display_errors', 1);
ini_set('controller_override', 1);
error_reporting(E_ALL);

require_once 'header.php';
?>

<form action="/ControlLogin" method="POST">
    <label for="email">Email:</label>
    <input type="email" id="email" name="email" required>
    <br>
    <label for="passwd">Password:</label>
    <input type="password" id="passwd" name="passwd" required>
    <br>
    <button type="submit">Login</button>
</form>
<p>Si aún no tienes una cuenta, puedes registrarte<a href="/registro"> aquí</a>.</p>

<?php

ini_set('display_errors', 1);
ini_set('controller_override', 1);
error_reporting(E_ALL);

require_once 'footer.php';
?>