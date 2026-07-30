<?php ob_start(); ?>
<h1>Directorio de Pacientes</h1>

<div class="card">
    <?php if (empty($pacientes)): ?>
        <p style="color:var(--text-muted); text-align:center; padding:2rem;">No hay pacientes registrados en el sistema.</p>
    <?php else: ?>
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>Nombre</th>
                        <th>Correo</th>
                        <th>Teléfono</th>
                        <th>Fecha de Registro</th>
                        <th style="text-align:center;">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($pacientes as $p): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($p['nombre']); ?></td>
                            <td><?php echo htmlspecialchars($p['correo']); ?></td>
                            <td><?php echo htmlspecialchars($p['telefono'] ?? 'N/D'); ?></td>
                            <td><?php echo date('d/m/Y', strtotime($p['fecha_registro'])); ?></td>
                            <td style="text-align:center;">
                                <?php $bU = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'])); if ($bU === '/') $bU = ''; ?>
                                <a href="<?php echo $bU; ?>/pacientes/historial?id=<?php echo $p['id_usuario']; ?>" class="btn btn--primary" style="padding: 0.4rem 0.8rem; font-size: 0.85rem; border-radius: 8px;">
                                    📘 Historial
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>
<?php 
$content = ob_get_clean();
require __DIR__ . '/../layouts/main.php'; 
?>
