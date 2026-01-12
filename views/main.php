<!-- Esta parte es fija en todas las páginas -->
<?php
ini_set('display_errors', 1);
ini_set('controller_override', 1);
error_reporting(E_ALL);

require_once 'header.php';
?>


<!-- Carrusel de Bootstrap -->
<style>
.carousel-indicators button {
    background-color: black !important;
    display: inline-block;
    padding-top: -30px;
}
.carousel-indicators .active {
    background-color: #333 !important; /* Un poco más claro para el activo */
}
</style>
<div id="carouselExampleCaptions" class="carousel slide" data-bs-ride="carousel">
    <div class="carousel-indicators">
        <?php require_once __DIR__ . '/../controls/controlLibros.php'; ?>
        
        <?php foreach ($libros as $index => $libro): ?>
            <button type="button" data-bs-target="#carouselExampleCaptions" data-bs-slide-to="<?php echo $index; ?>" 
                <?php echo ($index === 0) ? 'class="active" aria-current="true"' : ''; ?> 
                aria-label="Slide <?php echo ($index + 1); ?>">
            </button>
        <?php endforeach; ?>
    </div>
    <div class="carousel-inner">
        <?php
        // base URL para construir rutas públicas (ajusta '/TFG' si tu proyecto está en otra carpeta)
        $baseUrl = rtrim(dirname($_SERVER['SCRIPT_NAME']), "/\\");
        if ($baseUrl === '/' || $baseUrl === '\\') { $baseUrl = ''; }

        // Asegurar sesión para comprobar si hay usuario
        if (session_status() === PHP_SESSION_NONE) { session_start(); }
        ?>

        <?php foreach ($libros as $index => $libro): ?>
            <?php
            $imgSrc = $libro['img_url'] ?? (isset($libro['img']) && !empty($libro['img']) ? ($baseUrl . '/img/libros/' . ltrim($libro['img'], '/\\')) : '');
            $titulo = $libro['titulo'] ?? '';
            $autor = $libro['autor'] ?? '';
            $genero = $libro['genero'] ?? '';
            $sinopsis = $libro['sinopsis'] ?? '';
            $ejemplares = isset($libro['ejemplares']) ? (int)$libro['ejemplares'] : 0;
            $id = $libro['id'] ?? '';
            $puedeComprar = !empty($_SESSION) && $ejemplares > 0;
            ?>
            <div class="carousel-item <?php echo ($index === 0) ? 'active' : ''; ?>" >
                <div class="d-flex flex-column flex-md-row align-items-center gap-3 p-3 justify-content-center">
                    <?php if (!empty($imgSrc)): ?>
                        <img src="<?php echo htmlspecialchars($imgSrc); ?>" class="custom-book-img" alt="<?php echo htmlspecialchars($titulo); ?>">
                    <?php else: ?>
                        <div class="custom-book-img" style="display:flex;align-items:center;justify-content:center;background:#f0f0f0;border-radius:8px;width:300px;height:450px;">
                            <div style="text-align:center;color:#666;">
                                <strong><?php echo htmlspecialchars($titulo); ?></strong>
                            </div>
                        </div>
                    <?php endif; ?>

                    <div class="text-start text-dark bg-light bg-opacity-75 rounded p-3" style="max-width:600px;">
                        <h5 class="mb-1"><?php echo htmlspecialchars($titulo); ?></h5>
                        <p class="mb-1"><strong>Autor:</strong> <?php echo htmlspecialchars($autor); ?></p>
                        <p class="mb-1"><strong>Género:</strong> <?php echo htmlspecialchars($genero); ?></p>
                        <p class="mb-2"><?php echo nl2br(htmlspecialchars($sinopsis)); ?></p>
                        <p class="mb-2"><strong>Ejemplares disponibles:</strong> <?php echo $ejemplares; ?></p>

                        <?php if ($puedeComprar): ?>
                            <a href="<?php echo htmlspecialchars($baseUrl . '/controls/controlLibros.php?buy=1&id=' . urlencode($id)); ?>" class="btn btn-primary">Comprar</a>
                        <?php else: ?>
                            <button class="btn btn-secondary" disabled>
                                Comprar
                            </button>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
    <button class="carousel-control-prev" type="button" data-bs-target="#carouselExampleCaptions" data-bs-slide="prev">
        <span class="carousel-control-prev-icon" aria-hidden="true"></span>
        <span class="visually-hidden">Previous</span>
    </button>
    <button class="carousel-control-next" type="button" data-bs-target="#carouselExampleCaptions" data-bs-slide="next">
        <span class="carousel-control-next-icon" aria-hidden="true"></span>
        <span class="visually-hidden">Next</span>
    </button>
</div>

<script>
// Controles de teclado para el carrusel
document.addEventListener('keydown', function(event) {
    const carousel = document.getElementById('carouselExampleCaptions');
    if (!carousel) return;
    
    if (event.key === 'ArrowLeft') {
        const prevBtn = carousel.querySelector('[data-bs-slide="prev"]');
        if (prevBtn) prevBtn.click();
    } else if (event.key === 'ArrowRight') {
        const nextBtn = carousel.querySelector('[data-bs-slide="next"]');
        if (nextBtn) nextBtn.click();
    }
});
</script>

<!-- Esta parte es fija en todas las páginas -->
<?php
ini_set('display_errors', 1);
ini_set('controller_override', 1);
error_reporting(E_ALL);

require_once 'footer.php';
?>