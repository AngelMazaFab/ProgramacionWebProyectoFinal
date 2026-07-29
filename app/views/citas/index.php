<?php 
ob_start(); 
$baseUrl = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
if ($baseUrl === '/') $baseUrl = '';
?>
<div style="display:flex; justify-content: space-between; align-items:center;">
    <h1><?php echo $rol === 'medico' ? '📅 Agenda de Citas' : '📅 Mis Citas'; ?></h1>
    <?php if ($rol === 'medico'): ?>
        <a href="<?php echo $baseUrl; ?>/citas/nueva" class="btn btn--primary">➕ Agendar Nueva Cita</a>
    <?php endif; ?>
</div>

<div class="card">
    <?php if (empty($citas)): ?>
        <p style="color:var(--text-muted); text-align:center; padding:2rem;">No hay citas registradas.</p>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>Fecha y Hora</th>
                    <th><?php echo $rol === 'medico' ? 'Paciente' : 'Médico'; ?></th>
                    <th>Motivo</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($citas as $cita): ?>
                    <tr>
                        <td><?php echo date('d/m/Y H:i', strtotime($cita['fecha_hora'])); ?></td>
                        <td><?php echo htmlspecialchars($rol === 'medico' ? $cita['paciente_nombre'] : $cita['medico_nombre']); ?></td>
                        <td><?php echo htmlspecialchars($cita['motivo']); ?></td>
                        <td>
                            <span class="badge badge--<?php echo $cita['estado']; ?>">
                                <?php 
                                $iconos = ['solicitada'=>'⏳','confirmada'=>'✅','atendida'=>'💊','cancelada'=>'❌'];
                                echo ($iconos[$cita['estado']] ?? '') . ' ' . ucfirst($cita['estado']); 
                                ?>
                            </span>
                        </td>
                        <td style="display:flex; gap:6px; flex-wrap:wrap;">
                            <?php if ($cita['estado'] === 'solicitada' || $cita['estado'] === 'confirmada'): ?>
                                <form method="POST" action="<?php echo $baseUrl; ?>/citas/cancelar" style="display:inline;">
                                    <input type="hidden" name="id_cita" value="<?php echo $cita['id_cita']; ?>">
                                    <button type="submit" class="btn btn--danger" style="padding:4px 10px; font-size:0.8rem;" onclick="return confirm('¿Seguro que desea cancelar esta cita?');">❌ Cancelar</button>
                                </form>
                                
                                <?php if ($rol === 'medico' && $cita['estado'] === 'solicitada'): ?>
                                    <form method="POST" action="<?php echo $baseUrl; ?>/citas/update" style="display:inline;">
                                        <input type="hidden" name="id_cita" value="<?php echo $cita['id_cita']; ?>">
                                        <input type="hidden" name="estado" value="confirmada">
                                        <button type="submit" class="btn btn--success" style="padding:4px 10px; font-size:0.8rem;">✅ Confirmar</button>
                                    </form>
                                <?php endif; ?>

                                <?php if ($rol === 'medico'): ?>
                                    <button class="btn btn--warning" style="padding:4px 10px; font-size:0.8rem;" onclick="openReprogramar(<?php echo $cita['id_cita']; ?>, '<?php echo $cita['fecha_hora']; ?>')">🔄 Reprogramar</button>
                                <?php endif; ?>
                            <?php endif; ?>
                            
                            <?php if ($rol === 'medico' && ($cita['estado'] === 'confirmada' || $cita['estado'] === 'atendida')): ?>
                                <a href="<?php echo $baseUrl; ?>/consultas/atender?id_cita=<?php echo $cita['id_cita']; ?>" class="btn btn--primary" style="padding:4px 10px; font-size:0.8rem;">🩺 Atender / Ver</a>
                            <?php endif; ?>
                            <?php if ($rol === 'paciente' && $cita['estado'] === 'atendida'): ?>
                                <a href="<?php echo $baseUrl; ?>/consultas/atender?id_cita=<?php echo $cita['id_cita']; ?>" class="btn btn--primary" style="padding:4px 10px; font-size:0.8rem;">📋 Diagnóstico</a>
                                <a href="<?php echo $baseUrl; ?>/comentarios/nuevo?id_cita=<?php echo $cita['id_cita']; ?>" class="btn btn--accent" style="padding:4px 10px; font-size:0.8rem;">⭐ Evaluar</a>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<!-- Modal de Reprogramación -->
<div class="modal-overlay" id="reprogramarModal">
    <div class="modal">
        <div class="modal__title">🔄 Reprogramar Cita</div>
        <form method="POST" action="<?php echo $baseUrl; ?>/citas/update">
            <input type="hidden" name="id_cita" id="reprogramarIdCita">
            <div class="form__group">
                <label class="form__label">Nueva Fecha y Hora</label>
                <input type="datetime-local" name="fecha_hora" id="reprogramarFecha" class="form__input" required>
            </div>
            <div class="modal__actions">
                <button type="button" class="btn btn--ghost" onclick="closeReprogramar()">Cancelar</button>
                <button type="submit" class="btn btn--primary">Confirmar Cambio</button>
            </div>
        </form>
    </div>
</div>

<script>
function openReprogramar(idCita, fechaActual) {
    document.getElementById('reprogramarIdCita').value = idCita;
    // Convertir fecha para el input datetime-local
    const fecha = new Date(fechaActual);
    const offset = fecha.getTimezoneOffset();
    const local = new Date(fecha.getTime() - (offset * 60 * 1000));
    document.getElementById('reprogramarFecha').value = local.toISOString().slice(0, 16);
    document.getElementById('reprogramarModal').classList.add('active');
}

function closeReprogramar() {
    document.getElementById('reprogramarModal').classList.remove('active');
}

// Cerrar modal al hacer clic fuera
document.getElementById('reprogramarModal').addEventListener('click', function(e) {
    if (e.target === this) closeReprogramar();
});
</script>

<?php 
$content = ob_get_clean();
require __DIR__ . '/../layouts/main.php'; 
?>
