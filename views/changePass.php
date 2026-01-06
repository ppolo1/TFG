<?php

ini_set('display_errors', 1);
ini_set('controller_override', 1);
error_reporting(E_ALL);

require_once 'views/header.php';

?>

<?php

ini_set('display_errors', 1);
ini_set('controller_override', 1);
error_reporting(E_ALL);


// verificar si el usuario está logueado
if (empty($_SESSION['user_id'])) {
    // redirigir al login si no está logueado
    header('Location: /login');
    exit;
} else {
    echo 'Contraseña actual: ' . htmlspecialchars($_SESSION['passwd'] ?? '');
    echo '<br>';
    echo 'Nueva contarseña:'.' <form method="post" action="controls/changePass.php">
            <input type="password" name="new_password" required>
            <input type="submit" value="Cambiar contraseña">
        </form>';
    echo '<br><a href="/logeado">Volver a Bienvenido</a>';
}

?>

<?php

ini_set('display_errors', 1);
ini_set('controller_override', 1);
error_reporting(E_ALL);

require_once 'views/footer.php';

?>