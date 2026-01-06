<?php

ini_set('display_errors', 1);
ini_set('controller_override', 1);
error_reporting(E_ALL);

$method = $_SERVER['REQUEST_METHOD'];

$req = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

switch ($method) {
    case 'GET':
        switch ($req) {
            case '/':
                require_once 'views/main.php';
                break;
            case '/login':
                require_once 'views/login.php';
                break;
            case '/registro':
                require_once 'views/registro.php';
                break;
            case '/logeado':
                require_once 'views/bienvenido.php';
                break;
            case '/logout':
                session_start();
                session_destroy();
                session_abort();
                header('Location: /');
                break;
            case '/delete':
                require_once 'controls/controlDelete.php';
                break;
            case '/changePass':
                require_once 'views/changePass.php';
                break;
        }
    case 'POST':
        switch ($req) {
            case '/ControlRegistro':
                require_once 'controls/controlRegistro.php';
                break;
            case '/ControlLogin':
                require_once 'controls/controlLogin.php';
                break;
        }
        break;
    default:
        http_response_code(405);
        echo "404 Not Found";
        break;
}
