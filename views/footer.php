<footer class="site-footer" style="position:fixed; display:flex; flex-direction:column; align-items:center; justify-content:center; bottom:0;background:#f8f9fa;padding:auto 0;text-align:center;">
    <div class="container">
        <p class="mb-1">&copy; 2025 Mi Sitio Web. Todos los derechos reservados.</p>
        <p class="mb-0">
            Desarrollado por
            <a href="https://github.com/ppolo1" target="_blank" class="text-black text-decoration-underline">Pablo</a>
        </p>
    </div>
</footer>

<script>
// Ajusta el padding-bottom del body para que el contenido no quede oculto bajo el footer
(function(){
    var footer = document.querySelector('footer.site-footer');
    if (!footer) return;
    function updateBodyPadding(){
        var h = footer.offsetHeight || 0;
        document.body.style.paddingBottom = h + 'px';
    }
    window.addEventListener('resize', updateBodyPadding);
    // Ejecutar después de cargado para asegurar medidas correctas
    if (document.readyState === 'complete' || document.readyState === 'interactive') {
        setTimeout(updateBodyPadding,30);
    } else {
        document.addEventListener('DOMContentLoaded', function(){ setTimeout(updateBodyPadding,30); });
    }
})();
</script>

</body>

</html>