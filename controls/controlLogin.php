<?php

ini_set('display_errors', 1);
ini_set('controller_override', 1);
error_reporting(E_ALL);

session_start();

require_once 'models/ModeloUser.php';

$modeloUser = new User();
$users = $modeloUser->getUsuarios();

// Variable para saber si existe o no el usuario
$found = false;

// Comprobar que los valores no estan vacios
if (!empty($_POST['email']) && !empty($_POST['passwd'])) {
    foreach ($users as $user) {
        // La contrasena y el email del usuario son iguales
        if ($_POST['email'] == $user['email'] && $_POST['passwd'] == $user['passwd']) {
            // session_start();
            $_SESSION['id'] = $user['id'];
            $_SESSION['nombre'] = $user['nombre'];
            $_SESSION['apellido'] = $user['apellido'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['passwd'] = $user['passwd'];
            $found = true;
            break;
        }
    }
}

// Dependiendo de si existe el usuario haces login o no
if ($found) {
    // session_start();
    header('Location: /');
} else {
    header('Location: /login');
}
