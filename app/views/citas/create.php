<?php 
ob_start(); 
$baseUrl = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
if ($baseUrl === '/') $baseUrl = '';
?>
<h1>Agendar Nueva Cita</h1>

<div class="card" style="max-width: 500px;">
    <form method="POST" action="<?php echo $baseUrl; ?>/citas/store">
        <div class="form__group">
            <label class="form__label">Seleccionar Paciente</label>
            <select name="id_paciente" class="form__input" required>
                <option value="">-- Elija un paciente --</option>
                <?php foreach ($pacientes as $p): ?>
                    <option value="<?php echo $p['id_usuario']; ?>"><?php echo htmlspecialchars($p['nombre']); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        
        <div class="form__group">
            <label class="form__label">Fecha y Hora</label>
            <input type="datetime-local" name="fecha_hora" class="form__input" required>
        </div>
        
        <div class="form__group">
            <label class="form__label">Motivo de la Cita</label>
            <textarea name="motivo" class="form__input" rows="3" required></textarea>
        </div>
        
        <button type="submit" class="btn btn--primary">Agendar</button>
        <a href="<?php echo $baseUrl; ?>/citas" class="btn" style="background:#666; margin-left:10px;">Cancelar</a>
    </form>
</div>
<?php 
$content = ob_get_clean();
require __DIR__ . '/../layouts/main.php'; 
?>
