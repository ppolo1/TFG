<?php

ini_set('display_errors', 1);
ini_set('controller_override', 1);
error_reporting(E_ALL);

// detectar si el script se está ejecutando directamente o se está incluyendo en otra página
$directRequest = (realpath(__FILE__) === realpath($_SERVER['SCRIPT_FILENAME'] ?? ''));

// iniciar sesión si la necesitamos
if (session_status() === PHP_SESSION_NONE) {
	session_start();
}

// cargar modelo
require_once __DIR__ . '/../models/ModeloLibro.php';
$model = new Libro();

// Procesar compra SOLO cuando es petición directa y se solicita buy (o POST)
$id = intval($_REQUEST['id'] ?? 0);
$doBuy = isset($_REQUEST['buy']) || ($_SERVER['REQUEST_METHOD'] ?? '') === 'POST';

if ($directRequest && $id > 0 && $doBuy) {
	$userId = intval($_SESSION['user_id'] ?? 0) ?: null;
	$ok = $model->addPurchase($userId, $id);

	// si no hay usuario logueado, guardar una copia mínima en sesión para que aparezca en bienvenido
	if ($ok && empty($userId)) {
		$book = $model->getById($id);
		if ($book) {
			if (!isset($_SESSION['purchased_books']) || !is_array($_SESSION['purchased_books'])) {
				$_SESSION['purchased_books'] = [];
			}
			// incluir también el 'id' para poder migrar al iniciar sesión
			$entry = [
				'id'     => $book['id'] ?? $id,
				'titulo' => $book['titulo'] ?? '',
				'autor'  => $book['autor'] ?? '',
				'img'    => $book['img'] ?? ''
			];
			// evitar duplicados por id
			$exists = false;
			foreach ($_SESSION['purchased_books'] as $p) {
				if (!empty($p['id']) && $p['id'] == $entry['id']) { $exists = true; break; }
			}
			if (!$exists) $_SESSION['purchased_books'][] = $entry;
		}
	}

	// redirigir al bienvenido del usuario (ruta relativa)
	header('Location: ../views/bienvenido.php?id=' . $id);
	exit;
}

// Cuando se incluye (main.php), devolver lista de libros para el carrusel
$libros = $model->getAll();


