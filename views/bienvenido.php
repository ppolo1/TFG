<?php

ini_set('display_errors', 1);
ini_set('controller_override', 1);
error_reporting(E_ALL);

require_once 'header.php';

if (session_status() === PHP_SESSION_NONE) session_start();

// calcular baseUrl usado para construir rutas absolutas dentro del proyecto
$baseUrl = rtrim(dirname($_SERVER['SCRIPT_NAME']), "/\\");
if ($baseUrl === '/' || $baseUrl === '\\') { $baseUrl = ''; }

// comprobar/crear columna is_admin; si falla mostrar instrucciones para crearla manualmente
require_once __DIR__ . '/../models/ModeloLibro.php';
$__m_check = new Libro();
$__isAdminCol = $__m_check->ensureIsAdminColumnExists();
if (!$__isAdminCol['ok']) {
    echo '<div style="margin:20px;padding:12px;background:#fff3cd;border:1px solid #ffeeba;color:#856404;">';
    echo '<strong>La columna <code>is_admin</code> no existe y no se pudo crear automáticamente.</strong><br>';
    echo 'Ejecuta manualmente estas sentencias en phpMyAdmin (o en tu cliente MySQL) para crearla y asignar admin al usuario 1:<br>';
    echo '<pre style="white-space:pre-wrap;">ALTER TABLE usuarios ADD COLUMN is_admin TINYINT(1) DEFAULT 0;
UPDATE usuarios SET is_admin = 1 WHERE id = 1;</pre>';
    if (!empty($__isAdminCol['msg'])) {
        echo 'Error retornado por el intento automático: ' . htmlspecialchars($__isAdminCol['msg']) . '<br>';
    }
    echo '</div>';
}

// Si hay user_id en sesión, asegurar que $_SESSION['is_admin'] esté actualizada desde la BD
if (!empty($_SESSION['user_id'])) {
	// ...existing code may already require ModeloLibro.php elsewhere; requerir de forma segura
	if (!class_exists('Libro')) {
		require_once __DIR__ . '/../models/ModeloLibro.php';
	}
	$mTmp = new Libro();
	$u = $mTmp->getUserById(intval($_SESSION['user_id']));
	if ($u) {
		$_SESSION['is_admin'] = intval($u['is_admin'] ?? 0);
	} else {
		$_SESSION['is_admin'] = 0;
	}
}

if (isset($_SESSION['nombre'])) {

    // determinar si es admin (soporta tanto la marca en sesión como el nombre "Admin")
    $isAdmin = !empty($_SESSION['is_admin']) && intval($_SESSION['is_admin']) === 1;
    if (!$isAdmin && (($_SESSION['nombre'] ?? '') === 'Admin')) {
        $isAdmin = true;
        $_SESSION['is_admin'] = 1;
    }

    if ($isAdmin) {
        // VISTA ADMIN
        echo '<div style="margin-left:20px;">';
        echo '<h2>Bienvenido ' . htmlspecialchars($_SESSION['nombre']) . '</h2>';
        echo '<div style="margin-top:8px;margin-bottom:12px;">';
        echo '<a href="' . htmlspecialchars($baseUrl . '/logout') . '" style="padding:8px 12px;background:#dc3545;color:white;border-radius:4px;text-decoration:none;margin-right:8px;">Cerrar sesión</a>';
        // Botón que abre el modal de crear libro
        echo '<button onclick="openCreateModal()" style="padding:8px 12px;background:#28a745;color:white;border-radius:4px;cursor:pointer;border:none;">Añadir libro</button>';
        echo '</div>';

        // obtener todos los libros
        if (!class_exists('Libro')) require_once __DIR__ . '/../models/ModeloLibro.php';
        $mAdmin = new Libro();
        $all = $mAdmin->getAll();

        if (empty($all)) {
            echo '<p>No hay libros registrados.</p>';
        } else {
            echo '<table style="width:100%;border-collapse:collapse;border:1px solid #ddd;">';
            echo '<thead><tr style="background:#f8f9fa;"><th style="padding:8px;border:1px solid #ddd;text-align:left;">Título</th><th style="padding:8px;border:1px solid #ddd;text-align:left;">Autor</th><th style="padding:8px;border:1px solid #ddd;">Acciones</th></tr></thead>';
            echo '<tbody>';
            foreach ($all as $row) {
                $tid = htmlspecialchars($row['id'] ?? '');
                $tt = htmlspecialchars($row['titulo'] ?? '');
                $ta = htmlspecialchars($row['autor'] ?? '');
                $tg = htmlspecialchars($row['genero'] ?? '');
                $ts = htmlspecialchars($row['sinopsis'] ?? '');
                $ti = htmlspecialchars($row['img'] ?? '');
                $tej = intval($row['ejemplares'] ?? 0);
                
                echo '<tr data-book-id="' . $tid . '" data-titulo="' . $tt . '" data-autor="' . $ta . '" data-genero="' . $tg . '" data-sinopsis="' . $ts . '" data-img="' . $ti . '" data-ejemplares="' . $tej . '">';
                echo "<td style=\"padding:8px;border:1px solid #ddd;\">{$tt}</td>";
                echo "<td style=\"padding:8px;border:1px solid #ddd;\">{$ta}</td>";
                echo '<td style="padding:8px;border:1px solid #ddd;text-align:center;">';
                // Botón Editar abre modal de ejemplares
                echo '<button onclick="openEditEjemplaresModal(' . $tid . ', ' . $tej . ')" style="padding:6px 10px;background:#007bff;color:white;border-radius:4px;cursor:pointer;border:none;margin-right:6px;">Editar</button>';
                // Eliminar mediante enlace
                echo '<a href="' . htmlspecialchars($baseUrl . '/controls/controlAdmin.php?action=delete&id=' . $tid . '&session_id=' . urlencode(session_id())) . '" onclick="return confirm(\'¿Eliminar este libro?\');" style="padding:6px 10px;background:#dc3545;color:white;border-radius:4px;text-decoration:none;display:inline-block;">Eliminar</a>';
                echo '</td>';
                echo '</tr>';
            }
            echo '</tbody></table>';
        }

        echo '</div>'; // cierre margin-left

        // MODAL CREAR LIBRO (existente)
        ?>
        <div id="createModal" style="display:none;position:fixed;top:0;left:0;right:0;bottom:0;background:rgba(0,0,0,0.5);z-index:1000;align-items:center;justify-content:center;">
            <div style="background:white;padding:24px;border-radius:8px;max-width:500px;width:90%;max-height:80vh;overflow:auto;">
                <h3>Crear nuevo libro</h3>
                <form method="POST" action="<?php echo htmlspecialchars($baseUrl . '/controls/controlAdmin.php'); ?>" enctype="multipart/form-data">
                    <input type="hidden" name="action" value="create">
                    <input type="hidden" name="session_id" value="<?php echo htmlspecialchars(session_id()); ?>">

                    <div style="margin-bottom:12px;">
                        <label><strong>Título:</strong></label>
                        <input type="text" name="titulo" required style="width:100%;padding:8px;border:1px solid #ddd;border-radius:4px;box-sizing:border-box;">
                    </div>

                    <div style="margin-bottom:12px;">
                        <label><strong>Autor:</strong></label>
                        <input type="text" name="autor" style="width:100%;padding:8px;border:1px solid #ddd;border-radius:4px;box-sizing:border-box;">
                    </div>

                    <div style="margin-bottom:12px;">
                        <label><strong>Género:</strong></label>
                        <input type="text" name="genero" style="width:100%;padding:8px;border:1px solid #ddd;border-radius:4px;box-sizing:border-box;">
                    </div>

                    <div style="margin-bottom:12px;">
                        <label><strong>Sinopsis:</strong></label>
                        <textarea name="sinopsis" rows="3" style="width:100%;padding:8px;border:1px solid #ddd;border-radius:4px;box-sizing:border-box;"></textarea>
                    </div>

                    <div style="margin-bottom:12px;">
                        <label><strong>Ejemplares:</strong></label>
                        <input type="number" name="ejemplares" min="0" value="0" style="width:100%;padding:8px;border:1px solid #ddd;border-radius:4px;box-sizing:border-box;">
                    </div>

                    <div style="margin-bottom:12px;">
                        <label><strong>Imagen (opcional):</strong></label>
                        <input type="file" name="imgfile" accept=".jpg,.jpeg,.png,.gif" style="width:100%;">
                        <div style="font-size:0.9em;color:#666;margin-top:6px;">Si no subes imagen, se usará el nombre del libro como texto alternativo.</div>
                    </div>

                    <div style="display:flex;gap:8px;justify-content:flex-end;">
                        <button type="button" onclick="closeCreateModal()" style="padding:8px 16px;background:#6c757d;color:white;border:none;border-radius:4px;cursor:pointer;">Cancelar</button>
                        <button type="submit" style="padding:8px 16px;background:#28a745;color:white;border:none;border-radius:4px;cursor:pointer;">Crear</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- MODAL EDITAR SOLO EJEMPLARES -->
        <div id="editEjemplaresModal" style="display:none;position:fixed;top:0;left:0;right:0;bottom:0;background:rgba(0,0,0,0.5);z-index:1000;align-items:center;justify-content:center;">
            <div style="background:white;padding:24px;border-radius:8px;max-width:400px;width:90%;">
                <h3>Editar ejemplares</h3>
                <form method="POST" action="<?php echo htmlspecialchars($baseUrl . '/controls/controlAdmin.php'); ?>">
                    <input type="hidden" name="action" value="update_ejemplares">
                    <input type="hidden" name="session_id" value="<?php echo htmlspecialchars(session_id()); ?>">
                    <input type="hidden" name="id" id="editEjId">
                    <!-- campos ocultos para preservar datos existentes -->
                    <input type="hidden" name="titulo" id="editEjTitulo">
                    <input type="hidden" name="autor" id="editEjAutor">
                    <input type="hidden" name="genero" id="editEjGenero">
                    <input type="hidden" name="sinopsis" id="editEjSinopsis">
                    <input type="hidden" name="img_existing" id="editEjImg">

                    <div style="margin-bottom:12px;">
                        <label><strong>Ejemplares:</strong></label>
                        <input type="number" name="ejemplares" id="editEjEjemplares" min="0" style="width:100%;padding:8px;border:1px solid #ddd;border-radius:4px;box-sizing:border-box;">
                    </div>

                    <div style="display:flex;gap:8px;justify-content:flex-end;">
                        <button type="button" onclick="closeEditEjemplaresModal()" style="padding:8px 16px;background:#6c757d;color:white;border:none;border-radius:4px;cursor:pointer;">Cancelar</button>
                        <button type="submit" style="padding:8px 16px;background:#007bff;color:white;border:none;border-radius:4px;cursor:pointer;">Actualizar</button>
                    </div>
                </form>
            </div>
        </div>

        <script>
        // Modal crear
        function openCreateModal() {
            document.getElementById('createModal').style.display = 'flex';
        }
        function closeCreateModal() {
            document.getElementById('createModal').style.display = 'none';
        }

        // Modal editar ejemplares
        function openEditEjemplaresModal(id, ejemplares) {
            const row = document.querySelector('tr[data-book-id="' + id + '"]');
            if (row) {
                document.getElementById('editEjId').value = id;
                document.getElementById('editEjTitulo').value = row.getAttribute('data-titulo') || '';
                document.getElementById('editEjAutor').value = row.getAttribute('data-autor') || '';
                document.getElementById('editEjGenero').value = row.getAttribute('data-genero') || '';
                document.getElementById('editEjSinopsis').value = row.getAttribute('data-sinopsis') || '';
                document.getElementById('editEjImg').value = row.getAttribute('data-img') || '';
                document.getElementById('editEjEjemplares').value = ejemplares;
                document.getElementById('editEjemplaresModal').style.display = 'flex';
            }
        }
        function closeEditEjemplaresModal() {
            document.getElementById('editEjemplaresModal').style.display = 'none';
        }

        // Cerrar modals al hacer clic fuera
        window.onclick = function(event) {
            const createModal = document.getElementById('createModal');
            const editEjemplaresModal = document.getElementById('editEjemplaresModal');
            if (event.target === createModal) closeCreateModal();
            if (event.target === editEjemplaresModal) closeEditEjemplaresModal();
        };
        </script>

        <?php
        // No mostrar el resto del contenido para admin
    } else {
        // Mostrar datos del usuario en formato similar al carrusel de libros
        echo '<div style="margin-left:20px;">';
        echo '<h3>Tus datos</h3>';
        echo '<div class="d-flex flex-column flex-md-row align-items-center gap-3 p-3 justify-content-center" style="max-width:800px;margin:0 auto;">';
        
        // Imagen placeholder para el usuario
        echo '<div class="custom-book-img" style="display:flex;align-items:center;justify-content:center;background:#f0f0f0;border-radius:8px;width:300px;height:450px;">';
        echo '<div style="text-align:center;color:#666;">';
        echo '<strong>' . htmlspecialchars($_SESSION['nombre']) . '</strong>';
        echo '</div>';
        echo '</div>';
        
        // Información del usuario
        echo '<div class="text-start text-dark bg-light bg-opacity-75 rounded p-3" style="max-width:600px;">';
            echo '<p class="mb-1"><strong>Usuario:</strong>' . htmlspecialchars($_SESSION['nombre']) . ' ' . htmlspecialchars($_SESSION['apellido'] ?? '') . '</p>';
            echo '<p class="mb-1"><strong>Email:</strong> ' . htmlspecialchars($_SESSION['email'] ?? '') . '</p>';
            
            // Acciones
            echo '<div class="mt-3">';
                echo '<a href="' . htmlspecialchars($baseUrl . '/delete') . '" class="btn btn-danger me-2">Eliminar Cuenta</a>';
                echo '<a href="' . htmlspecialchars($baseUrl . '/logout') . '" class="btn btn-secondary me-2">Cerrar sesión</a>';
                echo '<a href="' . htmlspecialchars($baseUrl . '/changePass') . '" class="btn btn-primary me-2">Cambiar contraseña</a>';
            echo '</div>';
        echo '</div>';
        
        echo '</div>';
        echo '</div>';

        echo '<hr style="margin:20px 0;border:none;border-top:1px solid #ddd;width:100%;position:relative;left:50%;right:50%;margin-left:-50vw;margin-right:-50vw;width:100vw;">';
        
        echo '<div style="margin-left:20px;">';
        echo '<h3>Libros adquiridos</h3>';

        $books = [];

        // 1) Si hay user_id, coger compras desde BD
        if (!empty($_SESSION['user_id'])) {
            require_once __DIR__ . '/../models/ModeloLibro.php';
            $m = new Libro();
            $books = $m->getComprados(intval($_SESSION['user_id']));
        }

        // 2) Añadir compras guardadas en sesión (para usuarios no logueados)
        if (!empty($_SESSION['purchased_books']) && is_array($_SESSION['purchased_books'])) {
            foreach ($_SESSION['purchased_books'] as $pb) {
                // normalizar a la misma estructura que el modelo devuelve
                $books[] = [
                    'titulo' => $pb['titulo'] ?? ($pb['title'] ?? ''),
                    'autor'  => $pb['autor'] ?? ($pb['author'] ?? ''),
                    'img'    => $pb['img'] ?? ($pb['imagen'] ?? '')
                ];
            }
        }

        // 3) Si se pasó id por GET, intentar añadir ese libro (asegura aparición tras compra)
        $gid = intval($_GET['id'] ?? 0);
        if ($gid > 0) {
            if (!isset($m)) { require_once __DIR__ . '/../models/ModeloLibro.php'; $m = new Libro(); }
            $b = $m->getById($gid);
            if ($b) {
                // si no existe ya en $books por id/titulo, añadirlo
                $found = false;
                foreach ($books as $existing) {
                    if ((isset($existing['id']) && $existing['id'] == $b['id']) ||
                        (isset($existing['titulo']) && $existing['titulo'] === $b['titulo'])) {
                        $found = true; break;
                    }
                }
                if (!$found) {
                    $books[] = ['id'=>$b['id'],'titulo'=>$b['titulo'],'autor'=>$b['autor'],'img'=>$b['img']];
                }
            }
        }

        // eliminar duplicados (titulo+autor+img)
        $seen = [];
        $uniq = [];
        foreach ($books as $bk) {
            $k = md5(($bk['titulo']??'').'|'.($bk['autor']??'').'|'.($bk['img']??''));
            if (!isset($seen[$k])) { $seen[$k]=true; $uniq[] = $bk; }
        }
        $books = $uniq;

        $baseUrl = rtrim(dirname($_SERVER['SCRIPT_NAME']), "/\\");
        if ($baseUrl === '/' || $baseUrl === '\\') { $baseUrl = ''; }

        if (empty($books)) {
            echo '<p>No se han encontrado libros comprados.</p>';
        } else {
            echo '<div style="display:flex;flex-wrap:wrap;gap:16px;margin-top:12px;">';
            foreach ($books as $b) {
                // soporta array u objeto; solo mostrar titulo/autor
                if (is_object($b)) $b = (array)$b;
                $titulo = htmlspecialchars($b['titulo'] ?? $b['title'] ?? 'Título desconocido');
                $autor = htmlspecialchars($b['autor'] ?? $b['author'] ?? 'Autor desconocido');

                echo '<div style="padding:12px;border:1px solid #eaeaea;border-radius:6px;background:#fff;min-width:200px;">';
                echo '<div style="font-weight:bold;font-size:0.95em;">' . $titulo . '</div>';
                echo '<div style="color:#666;font-size:0.85em;">' . $autor . '</div>';
                echo '</div>';
            }
            echo '</div>';
        }

        echo '</div>';

        echo '<hr class="full-width-separator">';

        echo '<div class="content-wrapper">';
        // botón para abrir chat (widget en la misma página)
        echo '<button id="chat-toggle-btn" style="padding:8px 16px;background:#007bff;color:white;border:none;border-radius:4px;cursor:pointer;">Abrir Chat</button>';

        // widget oculto (el código JS puede reutilizar el que ya tienes)
        ?>
        <div id="chat-widget" style="display:none;max-width:700px;border:1px solid #ddd;padding:12px;border-radius:6px;margin-top:12px;">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:8px;">
                <div>
                    <label for="chat-lang">Idioma:</label>
                    <select id="chat-lang">
                        <option value="es" selected>Español</option>
                        <option value="en">English</option>
                        <option value="fr">Français</option>
                        <option value="de">Deutsch</option>
                    </select>
                </div>
                <button id="chat-close-btn" style="padding:4px 8px;background:#dc3545;color:white;border:none;border-radius:4px;cursor:pointer;">Cerrar</button>
            </div>

            <div id="chat-box" style="height:250px;overflow:auto;background:#fafafa;border:1px solid #eee;padding:8px;"></div>

            <div style="display:flex;gap:8px;margin-top:8px;">
                <input id="chat-input" type="text" placeholder="Escribe un mensaje..." style="flex:1;padding:8px;">
                <button id="chat-send">Enviar</button>
            </div>
        </div>

        <script>
        (function(){
            const user = "<?php echo addslashes($_SESSION['nombre']); ?>";
            const toggleBtn = document.getElementById('chat-toggle-btn');
            const chatWidget = document.getElementById('chat-widget');
            const closeBtn = document.getElementById('chat-close-btn');
            const chatBox = document.getElementById('chat-box');
            const input = document.getElementById('chat-input');
            const sendBtn = document.getElementById('chat-send');
            const langSel = document.getElementById('chat-lang');

            const UI = {
                es: { placeholder: 'Escribe un mensaje...', send: 'Enviar', systemReady: 'Chat listo.' },
                en: { placeholder: 'Type a message...', send: 'Send', systemReady: 'Chat ready.' },
                fr: { placeholder: 'Écrivez un message...', send: 'Envoyer', systemReady: 'Chat prêt.' },
                de: { placeholder: 'Nachricht schreiben...', send: 'Senden', systemReady: 'Chat bereit.' }
            };

            toggleBtn.addEventListener('click', () => {
                chatWidget.style.display = chatWidget.style.display === 'none' ? 'block' : 'none';
                if (chatWidget.style.display === 'block') load();
            });
            closeBtn.addEventListener('click', () => { chatWidget.style.display = 'none'; });

            function setLang(l){
                const t = UI[l] || UI.es;
                input.placeholder = t.placeholder;
                sendBtn.textContent = t.send;
            }
            langSel.addEventListener('change', () => { setLang(langSel.value); load(); });
            setLang(langSel.value);

            function escapeHtml(s){ return String(s).replace(/[&<>"']/g, c=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c])); }

            function append(msg){
                const el = document.createElement('div');
                el.style.padding = '4px 0';
                const displayed = (msg.text_translated && msg.text_translated !== '') ? msg.text_translated : msg.text;
                el.innerHTML = '<small style="color:#888">' + (msg.time || '') + '</small> ' +
                    '<strong>' + (msg.user ? escapeHtml(msg.user) : 'Sistema') + ':</strong> ' +
                    escapeHtml(displayed) + (msg.lang ? ' <em style="color:#888">(' + escapeHtml(msg.lang) + ')</em>' : '');
                chatBox.appendChild(el);
                chatBox.scrollTop = chatBox.scrollHeight;
            }

            async function load(){
                try{
                    const target = encodeURIComponent(langSel.value || 'es');
                    const room = encodeURIComponent(roomSel ? roomSel.value : 'global');
                    const res = await fetch('/api/chat.php?lang=' + target + '&room=' + room);
                    if(!res.ok) return;
                    const data = await res.json();
                    chatBox.innerHTML = '';
                    data.forEach(append);
                }catch(e){ console.error(e); }
            }

            async function send(){
                const text = input.value.trim();
                if(!text) return;
                const room = roomSel ? roomSel.value : 'global';
                const payload = { user: user, text: text, lang: langSel.value, room: room };
                try{
                    await fetch('/api/chat.php', {
                        method: 'POST',
                        headers: {'Content-Type':'application/json'},
                        body: JSON.stringify(payload)
                    });
                    input.value = '';
                    await load();
                }catch(e){ console.error(e); }
            }

            // reload when room changes
            if (roomSel) roomSel.addEventListener('change', load);

            sendBtn.addEventListener('click', send);
            input.addEventListener('keydown', (e)=>{ if(e.key==='Enter') send(); });

            // iniciar carga
            load();
            setInterval(load, 2000);
        })();
        </script>

        <?php
        echo '</div>';
    }
}

require_once 'footer.php';
?>