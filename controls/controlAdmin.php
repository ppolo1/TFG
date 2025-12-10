<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// permitir reanudar sesión si el formulario/enlace envía session_id
$incomingSessionId = trim($_REQUEST['session_id'] ?? '');
if ($incomingSessionId !== '') {
	session_id($incomingSessionId);
}

if (session_status() === PHP_SESSION_NONE) session_start();

// cargar modelo ANTES de validar admin para poder comprobar en BD si hace falta
require_once __DIR__ . '/../models/ModeloLibro.php';
$model = new Libro();

// --- NUEVO: función fallback para leer is_admin directamente desde la BD ---
function fetchIsAdminFromDbFallback(int $userId): bool {
	$userId = intval($userId);
	if ($userId <= 0) return false;
	// Intentar conexión directa (misma configuración que en ModeloLibro)
	$dbHost = '127.0.0.1';
	$dbName = 'tfg';
	$dbUser = 'root';
	$dbPass = '';
	try {
		$pdo = new PDO("mysql:host={$dbHost};dbname={$dbName};charset=utf8mb4", $dbUser, $dbPass, [
			PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
			PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
		]);
		$stmt = $pdo->prepare('SELECT COALESCE(is_admin,0) AS is_admin FROM usuarios WHERE id = ? LIMIT 1');
		$stmt->execute([$userId]);
		$r = $stmt->fetch();
		if ($r && intval($r['is_admin'] ?? 0) === 1) return true;
	} catch (\Exception $e) {
		// silent fail; no DB disponible
	}
	return false;
}

// comprobar admin: primero por sesión, si no existe consultar la BBDD y sincronizar la sesión
$userId = intval($_SESSION['user_id'] ?? 0);
$isAdmin = ($userId > 0) && !empty($_SESSION['is_admin']);
if (!$isAdmin && $userId > 0) {
	// intentar con el modelo primero
	$user = $model->getUserById($userId);
	if ($user && intval($user['is_admin'] ?? 0) === 1) {
		$_SESSION['is_admin'] = 1; // sincronizar sesión
		$isAdmin = true;
	} else {
		// fallback: comprobar DB directamente si el modelo no devolvió el dato
		$fallbackAdmin = fetchIsAdminFromDbFallback($userId);
		if ($fallbackAdmin) {
			$_SESSION['is_admin'] = 1;
			$isAdmin = true;
		}
	}
}

// Si no es admin, rechazar
if (!$isAdmin) {
	http_response_code(403);
	echo "Acceso denegado.";
	exit;
}

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
	$ejemplares = max(0, $ejemplares);

	$ok = $model->setEjemplares($id, $ejemplares);
	$_SESSION['admin_msg'] = $ok ? "Ejemplares actualizados exitosamente" : "Error al actualizar los ejemplares";
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
	if ($imgName === '') {
		$imgName = trim($_POST['img'] ?? '');
	}

	if ($titulo) {
		$newId = $model->createBook($titulo, $autor, $genero, $sinopsis, $ejemplares, $imgName);
		$_SESSION['admin_msg'] = $newId ? "Libro creado exitosamente (ID: $newId)" : "Error al crear el libro";
	} else {
		$_SESSION['admin_msg'] = "El título es obligatorio";
	}
	header('Location: ../views/bienvenido.php');
	exit;
}

// Actualizar libro
if ($action === 'update' && $_SERVER['REQUEST_METHOD'] === 'POST' && $id > 0) {
	$titulo = trim($_POST['titulo'] ?? '');
	$autor = trim($_POST['autor'] ?? '');
	$genero = trim($_POST['genero'] ?? '');
	$sinopsis = trim($_POST['sinopsis'] ?? '');
	$ejemplares = intval($_POST['ejemplares'] ?? 0);

	$imgUploaded = handleImageUpload('imgfile');
	$imgExisting = trim($_POST['img_existing'] ?? '');
	$imgToUse = $imgUploaded !== '' ? $imgUploaded : $imgExisting;

	if ($titulo) {
		$ok = $model->updateBook($id, $titulo, $autor, $genero, $sinopsis, $ejemplares, $imgToUse);
		$_SESSION['admin_msg'] = $ok ? "Libro actualizado exitosamente" : "Error al actualizar el libro";
	} else {
		$_SESSION['admin_msg'] = "El título es obligatorio";
	}
	header('Location: ../views/bienvenido.php');
	exit;
}

// Eliminar libro
if ($action === 'delete' && $id > 0) {
	$ok = $model->deleteBook($id);
	$_SESSION['admin_msg'] = $ok ? "Libro eliminado exitosamente" : "Error al eliminar el libro";
	header('Location: ../views/bienvenido.php');
	exit;
}

// Si llega aquí sin acción, redirigir
header('Location: ../views/bienvenido.php');
exit;
?>
