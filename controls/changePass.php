<?php

ini_set('display_errors', 1);
ini_set('controller_override', 1);
error_reporting(E_ALL);

$new_passwd;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // procesar el cambio de contraseña
    $new_passwd = trim($_POST['new_password'] ?? '');

    if (empty($_SESSION['user_id'])) {
        // redirigir al login si no está logueado
        header('Location: /login');
        exit;
    }

    if ($new_passwd === '') {
        echo 'La nueva contraseña no puede estar vacía.';
        exit;
    }

    // actualizar la contraseña en la base de datos
    require_once __DIR__ . '/../models/ModelUser.php';
    $model = new User();
    $userId = intval($_SESSION['user_id']);

    // $updated = $model->updateUserPassword($userId, $new_passwd);
    if ($updated) {
        // actualizar la contraseña en la sesión
        $_SESSION['passwd'] = $new_passwd;
        echo 'Contraseña cambiada con éxito.';
    } else {
        echo 'Error al cambiar la contraseña. Por favor, inténtelo de nuevo.';
    }
} else {
    // mostrar el formulario de cambio de contraseña
    echo '<form method="post" action="controls/changePass.php">
            <input type="password" name="new_password" required>
            <input type="submit" value="Cambiar contraseña">
        </form>';
}