<!DOCTYPE html>
<html lang="en">

<head>
    <title>Librería Online</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- No se añade CSS de Bootstrap, solo formato con clases Bootstrap -->
    <link rel="stylesheet" href="<?php echo rtrim(dirname($_SERVER['SCRIPT_NAME']), "/\\"); ?>/css/style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script> 
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

</head>

<body>
    <?php session_start(); ?>

    <header class="bg-primary text-white mb-4 p-3">
        <h1 class="mb-3">Librería Online</h1>
        <nav class="navbar navbar-expand-lg navbar-dark bg-primary p-0">
            <div class="container-fluid p-0">
                <a class="navbar-brand" href="/">Home</a>
                <!--
                El siguiente botón y div permiten el menú responsive de Bootstrap.
                Requiere JavaScript de Bootstrap para funcionar (no incluido aquí):
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
                    aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="navbarNav">
                    ...enlaces...
                </div>
                -->
                <?php if (isset($_SESSION['email']) && ($_SESSION['passwd'])) : ?>
                    <a class="nav-link d-inline text-white" href="/logeado">Profile</a>
                <?php else : ?>
                    <a class="nav-link d-inline text-white" href="/login">Login</a>
                <?php endif; ?>
                
            </div>
        </nav>
    </header>