<?php 
ob_start(); 
$baseUrl = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
if ($baseUrl === '/') $baseUrl = '';
?>
<div style="display:flex; justify-content: space-between; align-items:center;">
    <h1><?php echo $rol === 'medico' ? 'Agenda de Citas' : 'Mis Citas'; ?></h1>
    <?php if ($rol === 'medico'): ?>
        <a href="<?php echo $baseUrl; ?>/citas/nueva" class="btn">Agendar Nueva Cita</a>
    <?php endif; ?>
</div>

<div class="card">
    <?php if (empty($citas)): ?>
        <p>No hay citas registradas.</p>
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
                                <?php echo ucfirst($cita['estado']); ?>
                            </span>
                        </td>
                        <td>
                            <?php if ($cita['estado'] === 'solicitada' || $cita['estado'] === 'confirmada'): ?>
                                <form method="POST" action="<?php echo $baseUrl; ?>/citas/cancelar" style="display:inline;">
                                    <input type="hidden" name="id_cita" value="<?php echo $cita['id_cita']; ?>">
                                    <button type="submit" class="btn btn--danger" onclick="return confirm('¿Seguro que desea cancelar esta cita?');">Cancelar</button>
                                </form>
                                <?php if ($rol === 'medico' && $cita['estado'] === 'solicitada'): ?>
                                    <form method="POST" action="<?php echo $baseUrl; ?>/citas/update" style="display:inline;">
                                        <input type="hidden" name="id_cita" value="<?php echo $cita['id_cita']; ?>">
                                        <input type="hidden" name="estado" value="confirmada">
                                        <button type="submit" class="btn btn--success">Confirmar</button>
                                    </form>
                                <?php endif; ?>
                            <?php endif; ?>
                            
                            <?php if ($rol === 'medico' && ($cita['estado'] === 'confirmada' || $cita['estado'] === 'atendida')): ?>
                                <a href="<?php echo $baseUrl; ?>/consultas/atender?id_cita=<?php echo $cita['id_cita']; ?>" class="btn btn--primary">Atender / Ver</a>
                            <?php endif; ?>
                            <?php if ($rol === 'paciente' && $cita['estado'] === 'atendida'): ?>
                                <a href="<?php $bU = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'])); echo $bU === '/' ? '' : $bU; ?>/consultas/atender?id_cita=<?php echo $cita['id_cita']; ?>" class="btn btn--primary" style="margin-bottom: 4px; padding: 4px 8px;">Diagnóstico</a>
                                <a href="<?php $bU = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'])); echo $bU === '/' ? '' : $bU; ?>/comentarios/nuevo?id_cita=<?php echo $cita['id_cita']; ?>" class="btn btn--accent" style="margin-bottom: 4px; padding: 4px 8px;">Evaluar</a>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>
<?php 
$content = ob_get_clean();
require __DIR__ . '/../layouts/main.php'; 
?>
