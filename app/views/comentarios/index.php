<?php ob_start(); ?>
<h1>Bitácora de Comentarios y Retroalimentación</h1>

<div class="card">
    <?php if (empty($comentarios)): ?>
        <p style="color:var(--text-muted); text-align:center; padding:2rem;">Aún no hay comentarios registrados en la bitácora física.</p>
    <?php else: ?>
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>Fecha</th>
                        <th>Paciente</th>
                        <th>Calificación</th>
                        <th>Comentario</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($comentarios as $com): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($com['fecha']); ?></td>
                            <td><?php echo htmlspecialchars($com['paciente_nombre']); ?></td>
                            <td>
                                <?php 
                                    $estrellas = str_repeat('⭐', (int)$com['calificacion']);
                                    echo $estrellas; 
                                ?>
                            </td>
                            <td><?php echo htmlspecialchars($com['comentario']); ?></td>
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
