<?php
ini_set('display_errors', 1);
ini_set('controller_override', 1);
error_reporting(E_ALL);

if (session_status() === PHP_SESSION_NONE) session_start();

// Verificar que es admin
$isAdmin = intval($_SESSION['user_id'] ?? 0) > 0 && !empty($_SESSION['is_admin']);
if (!$isAdmin) {
	http_response_code(403);
	echo "Acceso denegado.";
	exit;
}

require_once __DIR__ . '/../models/ModeloLibro.php';
$model = new Libro();

$action = trim($_REQUEST['action'] ?? '');
$id = intval($_REQUEST['id'] ?? 0);

// helper: procesar upload y devolver nombre de fichero o '' si no hubo
function handleImageUpload($fieldName = 'imgfile') {
	$uploadDir = realpath(__DIR__ . '/../img/libros') ?: (__DIR__ . '/../img/libros');
	if (!is_dir($uploadDir)) @mkdir($uploadDir, 0755, true);

	if (empty($_FILES[$fieldName]) || $_FILES[$fieldName]['error'] === UPLOAD_ERR_NO_FILE) {
		return '';
	}
	$f = $_FILES[$fieldName];
	if ($f['error'] !== UPLOAD_ERR_OK) return '';

	$allowed = ['jpg','jpeg','png','gif'];
	$ext = strtolower(pathinfo($f['name'], PATHINFO_EXTENSION));
	if (!in_array($ext, $allowed)) return '';

	// generar nombre único seguro
	$base = preg_replace('/[^a-z0-9_\-]/i','_', pathinfo($f['name'], PATHINFO_FILENAME));
	$fname = $base . '_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
	$dest = $uploadDir . '/' . $fname;

	if (move_uploaded_file($f['tmp_name'], $dest)) {
		@chmod($dest, 0644);
		return $fname;
	}
	return '';
}

// Actualizar SOLO ejemplares
if ($action === 'update_ejemplares' && $_SERVER['REQUEST_METHOD'] === 'POST' && $id > 0) {
	$ejemplares = intval($_POST['ejemplares'] ?? 0);
	// obtener datos existentes para preservarlos
	$titulo = trim($_POST['titulo'] ?? '');
	$autor = trim($_POST['autor'] ?? '');
	$genero = trim($_POST['genero'] ?? '');
	$sinopsis = trim($_POST['sinopsis'] ?? '');
	$imgExisting = trim($_POST['img_existing'] ?? '');

	if ($titulo) {
		$ok = $model->updateBook($id, $titulo, $autor, $genero, $sinopsis, $ejemplares, $imgExisting);
		$_SESSION['admin_msg'] = $ok ? "Ejemplares actualizados exitosamente" : "Error al actualizar los ejemplares";
	} else {
		$_SESSION['admin_msg'] = "Error: datos del libro no encontrados";
	}
	header('Location: ../views/bienvenido.php');
	exit;
}

// Crear libro
if ($action === 'create' && $_SERVER['REQUEST_METHOD'] === 'POST') {
	$titulo = trim($_POST['titulo'] ?? '');
	$autor = trim($_POST['autor'] ?? '');
	$genero = trim($_POST['genero'] ?? '');
	$sinopsis = trim($_POST['sinopsis'] ?? '');
	$ejemplares = intval($_POST['ejemplares'] ?? 0);

	$imgName = handleImageUpload('imgfile');
	// si no subieron fichero, permitir nombre manual (opcional)
	if ($imgName === '') {
		$imgName = trim($_POST['img'] ?? '');
	}

	if ($titulo) {
		$newId = $model->createBook($titulo, $autor, $genero, $sinopsis, $ejemplares, $imgName);
		$_SESSION['admin_msg'] = $newId ? "Libro creado exitosamente (ID: $newId)" : "Error al crear el libro";
	} else {
		$_SESSION['admin_msg'] = "El título es obligatorio";
	}
	header('Location: ../views/admin.php');
	exit;
}

// Actualizar libro
if ($action === 'update' && $_SERVER['REQUEST_METHOD'] === 'POST' && $id > 0) {
	$titulo = trim($_POST['titulo'] ?? '');
	$autor = trim($_POST['autor'] ?? '');
	$genero = trim($_POST['genero'] ?? '');
	$sinopsis = trim($_POST['sinopsis'] ?? '');
	$ejemplares = intval($_POST['ejemplares'] ?? 0);

	// procesar upload; si no hay, conservar img_existing
	$imgUploaded = handleImageUpload('imgfile');
	$imgExisting = trim($_POST['img_existing'] ?? '');
	$imgToUse = $imgUploaded !== '' ? $imgUploaded : $imgExisting;

	if ($titulo) {
		$ok = $model->updateBook($id, $titulo, $autor, $genero, $sinopsis, $ejemplares, $imgToUse);
		$_SESSION['admin_msg'] = $ok ? "Libro actualizado exitosamente" : "Error al actualizar el libro";
	} else {
		$_SESSION['admin_msg'] = "El título es obligatorio";
	}
	header('Location: ../views/admin.php');
	exit;
}

// Eliminar libro
if ($action === 'delete' && $id > 0) {
	$ok = $model->deleteBook($id);
	$_SESSION['admin_msg'] = $ok ? "Libro eliminado exitosamente" : "Error al eliminar el libro";
	header('Location: ../views/admin.php');
	exit;
}

// Si llega aquí sin acción, redirigir al admin
header('Location: ../views/admin.php');
exit;
?>
