<?php
ini_set('display_errors', 1);
ini_set('controller_override', 1);
error_reporting(E_ALL);

require_once 'header.php';

if (session_status() === PHP_SESSION_NONE) session_start();

// Verificar que es admin
$isAdmin = intval($_SESSION['user_id'] ?? 0) > 0 && !empty($_SESSION['is_admin']);
if (!$isAdmin) {
	header('Location: /');
	exit;
}

require_once __DIR__ . '/../models/ModeloLibro.php';
$model = new Libro();

$books = $model->getAll();
$msg = $_SESSION['admin_msg'] ?? '';
if ($msg) unset($_SESSION['admin_msg']);

$editId = intval($_GET['edit'] ?? 0);
$editBook = null;
if ($editId > 0) {
	$editBook = $model->getById($editId);
}

// baseUrl para rutas relativas (si hace falta)
$baseUrl = rtrim(dirname($_SERVER['SCRIPT_NAME']), "/\\");
if ($baseUrl === '/' || $baseUrl === '\\') { $baseUrl = ''; }
?>

<div class="content-wrapper">
	<h2>Panel de Administración - Gestión de Libros</h2>
	<?php if ($msg): ?>
		<div style="padding:10px;background:#d4edda;color:#155724;border:1px solid #c3e6cb;border-radius:4px;margin:10px 0;">
			<?php echo htmlspecialchars($msg); ?>
		</div>
	<?php endif; ?>

	<hr class="full-width-separator">

	<!-- FORMULARIO CREAR/EDITAR -->
	<h3><?php echo $editBook ? 'Editar Libro' : 'Crear Nuevo Libro'; ?></h3>

	<form method="POST" action="/controls/controlAdmin.php" style="max-width:600px;border:1px solid #ddd;padding:16px;border-radius:6px;" enctype="multipart/form-data">
		<input type="hidden" name="action" value="<?php echo $editBook ? 'update' : 'create'; ?>">
		<?php if ($editBook): ?>
			<input type="hidden" name="id" value="<?php echo htmlspecialchars($editBook['id']); ?>">
			<input type="hidden" name="img_existing" value="<?php echo htmlspecialchars($editBook['img'] ?? ''); ?>">
		<?php endif; ?>

		<div style="margin-bottom:12px;">
			<label for="titulo"><strong>Título:</strong></label><br>
			<input type="text" id="titulo" name="titulo" required style="width:100%;padding:8px;border:1px solid #ddd;border-radius:4px;" value="<?php echo htmlspecialchars($editBook['titulo'] ?? ''); ?>">
		</div>

		<div style="margin-bottom:12px;">
			<label for="autor"><strong>Autor:</strong></label><br>
			<input type="text" id="autor" name="autor" style="width:100%;padding:8px;border:1px solid #ddd;border-radius:4px;" value="<?php echo htmlspecialchars($editBook['autor'] ?? ''); ?>">
		</div>

		<div style="margin-bottom:12px;">
			<label for="genero"><strong>Género:</strong></label><br>
			<input type="text" id="genero" name="genero" style="width:100%;padding:8px;border:1px solid #ddd;border-radius:4px;" value="<?php echo htmlspecialchars($editBook['genero'] ?? ''); ?>">
		</div>

		<div style="margin-bottom:12px;">
			<label for="sinopsis"><strong>Sinopsis:</strong></label><br>
			<textarea id="sinopsis" name="sinopsis" rows="3" style="width:100%;padding:8px;border:1px solid #ddd;border-radius:4px;"><?php echo htmlspecialchars($editBook['sinopsis'] ?? ''); ?></textarea>
		</div>

		<div style="margin-bottom:12px;">
			<label for="ejemplares"><strong>Ejemplares:</strong></label><br>
			<input type="number" id="ejemplares" name="ejemplares" min="0" style="width:100%;padding:8px;border:1px solid #ddd;border-radius:4px;" value="<?php echo htmlspecialchars($editBook['ejemplares'] ?? 0); ?>">
		</div>

		<div style="margin-bottom:12px;">
			<label for="imgfile"><strong>Imagen (subir archivo):</strong></label><br>
			<input type="file" id="imgfile" name="imgfile" accept=".jpg,.jpeg,.png,.gif" style="width:100%;">
			<?php if (!empty($editBook['img'])): ?>
				<div style="margin-top:8px;">Imagen actual: <?php echo htmlspecialchars($editBook['img']); ?></div>
			<?php endif; ?>
			<div style="font-size:0.9em;color:#666;margin-top:6px;">Si no subes imagen se conservará la existente (si aplica).</div>
		</div>

		<button type="submit" style="padding:10px 20px;background:#28a745;color:white;border:none;border-radius:4px;cursor:pointer;">
			<?php echo $editBook ? 'Actualizar Libro' : 'Crear Libro'; ?>
		</button>
		<?php if ($editBook): ?>
			<a href="/views/admin.php" style="padding:10px 20px;background:#6c757d;color:white;border:none;border-radius:4px;cursor:pointer;text-decoration:none;display:inline-block;">Cancelar</a>
		<?php endif; ?>
	</form>

	<hr class="full-width-separator">

	<!-- LISTADO DE LIBROS -->
	<h3>Libros en la Base de Datos</h3>
	<?php if (empty($books)): ?>
		<p>No hay libros registrados.</p>
	<?php else: ?>
		<table style="width:100%;border-collapse:collapse;border:1px solid #ddd;">
			<thead>
				<tr style="background:#f8f9fa;">
					<th style="border:1px solid #ddd;padding:10px;">ID</th>
					<th style="border:1px solid #ddd;padding:10px;">Título</th>
					<th style="border:1px solid #ddd;padding:10px;">Autor</th>
					<th style="border:1px solid #ddd;padding:10px;">Género</th>
					<th style="border:1px solid #ddd;padding:10px;">Ejemplares</th>
					<th style="border:1px solid #ddd;padding:10px;">Acciones</th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ($books as $b): ?>
					<tr>
						<td style="border:1px solid #ddd;padding:10px;"><?php echo htmlspecialchars($b['id']); ?></td>
						<td style="border:1px solid #ddd;padding:10px;"><?php echo htmlspecialchars($b['titulo']); ?></td>
						<td style="border:1px solid #ddd;padding:10px;"><?php echo htmlspecialchars($b['autor']); ?></td>
						<td style="border:1px solid #ddd;padding:10px;"><?php echo htmlspecialchars($b['genero']); ?></td>
						<td style="border:1px solid #ddd;padding:10px;"><?php echo htmlspecialchars($b['ejemplares']); ?></td>
						<td style="border:1px solid #ddd;padding:10px;">
							<a href="?edit=<?php echo htmlspecialchars($b['id']); ?>" style="padding:5px 10px;background:#007bff;color:white;border-radius:4px;text-decoration:none;display:inline-block;margin-right:5px;">Editar</a>
							<a href="<?php echo ($baseUrl ?? '') . '/controls/controlAdmin.php?action=delete&id=' . htmlspecialchars($b['id']); ?>" onclick="return confirm('¿Estás seguro de que deseas eliminar este libro?');" style="padding:5px 10px;background:#dc3545;color:white;border-radius:4px;text-decoration:none;display:inline-block;">Eliminar</a>
						</td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
	<?php endif; ?>
</div>

<?php
require_once 'footer.php';
?>
