<?php ob_start(); ?>
<h1>Dejar Retroalimentación - Cita #<?php echo htmlspecialchars($id_cita); ?></h1>

<div class="card" style="max-width: 500px;">
    <p>Agradecemos tus comentarios sobre la atención médica recibida.</p>
    <form method="POST" action="<?php $bU = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'])); echo $bU === '/' ? '' : $bU; ?>/comentarios/store">
        <input type="hidden" name="id_cita" value="<?php echo htmlspecialchars($id_cita); ?>">
        
        <div class="form__group">
            <label class="form__label">Calificación (1 a 5 estrellas)</label>
            <select name="calificacion" class="form__input" required>
                <option value="5">5 - Excelente</option>
                <option value="4">4 - Muy buena</option>
                <option value="3">3 - Regular</option>
                <option value="2">2 - Deficiente</option>
                <option value="1">1 - Mala</option>
            </select>
        </div>
        
        <div class="form__group">
            <label class="form__label">Comentarios o sugerencias</label>
            <textarea name="comentario" class="form__input" rows="4" placeholder="Escribe aquí tu retroalimentación..." required></textarea>
        </div>
        
        <button type="submit" class="btn btn--primary">Enviar Comentario</button>
        <a href="<?php $bU = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'])); echo $bU === '/' ? '' : $bU; ?>/citas" class="btn" style="background:#666; margin-left:10px;">Cancelar</a>
    </form>
</div>

<?php 
$content = ob_get_clean();
require __DIR__ . '/../layouts/main.php'; 
?>
