<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

// mover session_start arriba para que header.php y comprobaciones de sesión funcionen
if (session_status() === PHP_SESSION_NONE) session_start();

require_once 'header.php';

// cargar modelo para sincronizar admin desde BBDD si hace falta
require_once __DIR__ . '/../models/ModeloLibro.php';
$model = new Libro();

// Verificar que es admin: primero por sesión, si no existe consultar la BBDD y sincronizar
$userId = intval($_SESSION['user_id'] ?? 0);
$isAdmin = ($userId > 0) && !empty($_SESSION['is_admin']);
if (!$isAdmin && $userId > 0) {
	$user = $model->getUserById($userId);
	if ($user && intval($user['is_admin'] ?? 0) === 1) {
		$_SESSION['is_admin'] = 1; // sincronizar sesión
		$isAdmin = true;
	}
}
if (!$isAdmin) {
	header('Location: /');
	exit;
}

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

	<form method="POST" action="../controls/controlAdmin.php" style="max-width:600px;border:1px solid #ddd;padding:16px;border-radius:6px;" enctype="multipart/form-data">
		<input type="hidden" name="action" value="<?php echo $editBook ? 'update' : 'create'; ?>">
		<!-- enviar session_id para que controlAdmin.php pueda reanudar la sesión si hace falta -->
		<input type="hidden" name="session_id" value="<?php echo htmlspecialchars(session_id()); ?>">
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
			<a href="admin.php" style="padding:10px 20px;background:#6c757d;color:white;border:none;border-radius:4px;cursor:pointer;text-decoration:none;display:inline-block;">Cancelar</a>
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
							<button type="button" class="btn-ejemplares" data-id="<?php echo htmlspecialchars($b['id']); ?>" data-ejemplares="<?php echo htmlspecialchars($b['ejemplares']); ?>" data-titulo="<?php echo htmlspecialchars($b['titulo']); ?>" style="padding:5px 10px;background:#ffc107;color:black;border-radius:4px;border:none;cursor:pointer;margin-right:5px;">Actualizar Ejemplares</a>
							<a href="../controls/controlAdmin.php?action=delete&id=<?php echo htmlspecialchars($b['id']); ?>&session_id=<?php echo htmlspecialchars(session_id()); ?>" onclick="return confirm('¿Estás seguro de que deseas eliminar este libro?');" style="padding:5px 10px;background:#dc3545;color:white;border-radius:4px;text-decoration:none;display:inline-block;">Eliminar</a>
						</td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
	<?php endif; ?>
</div>

<!-- Modal para actualizar ejemplares -->
<div id="modalEjemplares" style="display:none;position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.5);z-index:1000;justify-content:center;align-items:center;">
	<div style="background:white;padding:30px;border-radius:8px;box-shadow:0 4px 6px rgba(0,0,0,0.1);max-width:400px;width:90%;">
		<h3 id="modalTitle">Actualizar Ejemplares</h3>
		<form id="formEjemplares" method="POST" action="../controls/controlAdmin.php">
			<input type="hidden" name="action" value="update_ejemplares">
			<input type="hidden" name="session_id" value="<?php echo htmlspecialchars(session_id()); ?>">
			<input type="hidden" name="id" id="modalId">
			
			<div style="margin-bottom:15px;">
				<label for="modalEjemplaresInput"><strong>Número de Ejemplares:</strong></label><br>
				<input type="number" id="modalEjemplaresInput" name="ejemplares" min="0" style="width:100%;padding:8px;border:1px solid #ddd;border-radius:4px;margin-top:5px;">
			</div>

			<div style="display:flex;gap:10px;">
				<button type="submit" style="flex:1;padding:10px;background:#28a745;color:white;border:none;border-radius:4px;cursor:pointer;">Guardar</button>
				<button type="button" id="btnCerrarModal" style="flex:1;padding:10px;background:#6c757d;color:white;border:none;border-radius:4px;cursor:pointer;">Cancelar</button>
			</div>
		</form>
	</div>
</div>

<script>
	// Abrir modal
	document.querySelectorAll('.btn-ejemplares').forEach(btn => {
		btn.addEventListener('click', function() {
			const id = this.getAttribute('data-id');
			const ejemplares = this.getAttribute('data-ejemplares');
			const titulo = this.getAttribute('data-titulo');

			document.getElementById('modalId').value = id;
			document.getElementById('modalEjemplaresInput').value = ejemplares;
			document.getElementById('modalTitle').textContent = 'Actualizar Ejemplares - ' + titulo;
			document.getElementById('modalEjemplares').style.display = 'flex';
		});
	});

	// Cerrar modal
	document.getElementById('btnCerrarModal').addEventListener('click', function() {
		document.getElementById('modalEjemplares').style.display = 'none';
	});

	// Cerrar modal si se clickea fuera del contenedor
	document.getElementById('modalEjemplares').addEventListener('click', function(e) {
		if (e.target === this) {
			this.style.display = 'none';
		}
	});
</script>

<?php
require_once 'footer.php';
?>
