<?php

ini_set('display_errors', 1);
ini_set('controller_override', 1);
error_reporting(E_ALL);

require_once 'models/ModeloUser.php';

$modelUser = new User();

$name = $_POST['nombre'];
$apellido = $_POST['apellido'];
$email = $_POST['email'];
$passwd = $_POST['passwd'];

// Validar formato de email: letras@letras.com
$emailRegex = '/^[a-zA-Z]+@[a-zA-Z]+\.[a-zA-Z]+$/';
if (!preg_match($emailRegex, $email)) {
    die('Error: El correo debe tener el formato letras@letras.com');
}

$modelUser->register($name, $apellido, $email, $passwd);

header('Location: /login');
