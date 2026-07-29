<?php ob_start(); ?>
<h1>Directorio de Pacientes</h1>

<div class="card">
    <?php if (empty($pacientes)): ?>
        <p>No hay pacientes registrados en el sistema.</p>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>Nombre</th>
                    <th>Correo</th>
                    <th>Teléfono</th>
                    <th>Fecha de Registro</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($pacientes as $p): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($p['nombre']); ?></td>
                        <td><?php echo htmlspecialchars($p['correo']); ?></td>
                        <td><?php echo htmlspecialchars($p['telefono'] ?? 'N/D'); ?></td>
                        <td><?php echo date('d/m/Y', strtotime($p['fecha_registro'])); ?></td>
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
