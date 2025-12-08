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

$modelUser->register($name, $apellido, $email, $passwd);

header('Location: /login');
