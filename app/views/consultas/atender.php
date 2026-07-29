<?php ob_start(); ?>
<?php $isMedico = \App\Core\Session::get('usuario_rol') === 'medico'; ?>
<h1>Atención Médica - Cita #<?php echo $cita['id_cita']; ?></h1>

<div style="display:flex; gap: 20px; flex-wrap: wrap;">
    <!-- Panel Izquierdo: Diagnóstico -->
    <div class="card" style="flex:1; min-width: 300px;">
        <h3>Datos Clínicos</h3>
        <?php if (!$consulta): ?>
            <?php if ($isMedico): ?>
                <form method="POST" action="<?php $bU = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'])); echo $bU === '/' ? '' : $bU; ?>/consultas/store">
                    <input type="hidden" name="id_cita" value="<?php echo $cita['id_cita']; ?>">
                    <div class="form__group">
                        <label class="form__label">Diagnóstico</label>
                        <textarea name="diagnostico" class="form__input" rows="4" required></textarea>
                    </div>
                    <div class="form__group">
                        <label class="form__label">Tratamiento e Indicaciones Generales</label>
                        <textarea name="tratamiento" class="form__input" rows="4"></textarea>
                    </div>
                    <button type="submit" class="btn btn--primary">Guardar Consulta y Marcar Atendida</button>
                </form>
            <?php else: ?>
                <p>La consulta aún no ha sido registrada por el médico.</p>
            <?php endif; ?>
        <?php else: ?>
            <p><strong>Diagnóstico:</strong><br><?php echo nl2br(htmlspecialchars($consulta['diagnostico'])); ?></p>
            <p><strong>Tratamiento:</strong><br><?php echo nl2br(htmlspecialchars($consulta['tratamiento'])); ?></p>
            <p><em>Registrado el: <?php echo date('d/m/Y H:i', strtotime($consulta['fecha_atencion'])); ?></em></p>
        <?php endif; ?>
    </div>

    <!-- Panel Derecho: Receta -->
    <?php if ($consulta): ?>
    <div class="card" style="flex:1; min-width: 300px;">
        <div style="display:flex; justify-content:space-between; align-items:center;">
            <h3>Receta Médica</h3>
            <a href="<?php $bU = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'])); echo $bU === '/' ? '' : $bU; ?>/receta/pdf?id_consulta=<?php echo $consulta['id_consulta']; ?>" class="btn btn--accent" target="_blank" style="padding:4px 8px; font-size:0.8rem;">Descargar PDF</a>
        </div>
        
        <?php if ($isMedico): ?>
            <form method="POST" action="<?php $bU = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'])); echo $bU === '/' ? '' : $bU; ?>/recetas/store" style="background:#f9f9f9; padding:15px; border-radius:4px; margin-bottom:15px;">
                <input type="hidden" name="id_consulta" value="<?php echo $consulta['id_consulta']; ?>">
                <input type="hidden" name="id_cita" value="<?php echo $cita['id_cita']; ?>">
                
                <div class="form__group">
                    <input type="text" name="medicamento" class="form__input" placeholder="Medicamento (ej. Paracetamol 500mg)" required>
                </div>
                <div class="form__group">
                    <input type="text" name="dosis" class="form__input" placeholder="Dosis (ej. 1 tableta c/8 horas)" required>
                </div>
                <div class="form__group">
                    <input type="text" name="indicaciones" class="form__input" placeholder="Indicaciones adicionales">
                </div>
                <button type="submit" class="btn btn--success">Agregar Medicamento</button>
            </form>
        <?php endif; ?>

        <?php if (empty($recetas)): ?>
            <p>No hay medicamentos recetados todavía.</p>
        <?php else: ?>
            <table style="font-size: 0.9rem;">
                <thead>
                    <tr>
                        <th>Medicamento</th>
                        <th>Dosis</th>
                        <th>Indicaciones</th>
                        <?php if($isMedico): ?><th></th><?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recetas as $r): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($r['medicamento']); ?></td>
                            <td><?php echo htmlspecialchars($r['dosis']); ?></td>
                            <td><?php echo htmlspecialchars($r['indicaciones']); ?></td>
                            <?php if($isMedico): ?>
                            <td>
                                <form method="POST" action="<?php $bU = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'])); echo $bU === '/' ? '' : $bU; ?>/recetas/delete" style="display:inline;">
                                    <input type="hidden" name="id_receta" value="<?php echo $r['id_receta']; ?>">
                                    <input type="hidden" name="id_cita" value="<?php echo $cita['id_cita']; ?>">
                                    <button type="submit" class="btn btn--danger" style="padding: 4px 8px; font-size: 0.8rem;" onclick="return confirm('¿Quitar de la receta?');">X</button>
                                </form>
                            </td>
                            <?php endif; ?>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
    <?php endif; ?>
</div>

<!-- Módulo 4: Canvas Clínico y Subida de Archivos -->
<?php if ($consulta): ?>
<div style="display:flex; gap: 20px; flex-wrap: wrap; margin-top:20px;">

    <!-- Canvas -->
    <div class="card" style="flex:2; min-width: 400px;">
        <h3>Dibujo Clínico</h3>
        
        <?php if ($isMedico): ?>
            <div style="margin-bottom: 10px;">
                <label>Fondo Anatómico:</label>
                <select id="canvasTemplate" class="form__input" style="width:auto; display:inline-block;">
                    <?php if ($consulta['anotaciones_canvas']): ?>
                        <option value="<?php $bU = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'])); echo $bU === '/' ? '' : $bU; ?>/consultas/ver-canvas?id=<?php echo $consulta['id_consulta']; ?>">Continuar Dibujo Anterior</option>
                    <?php endif; ?>
                    <option value="<?php $bU = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'])); echo $bU === '/' ? '' : $bU; ?>/assets/img/cuerpo.png">Cuerpo Humano</option>
                    <option value="<?php $bU = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'])); echo $bU === '/' ? '' : $bU; ?>/assets/img/sistema_oseo.png">Sistema Oseo</option>
                </select>
            </div>
            <div style="border: 1px solid #ccc; border-radius: 4px; overflow:hidden; background:#fff; cursor: crosshair;">
                <canvas id="clinicalCanvas" width="800" height="500" style="display:block; width: 100%; height: auto;"></canvas>
            </div>
            <div style="margin-top: 10px;">
                <button id="btnSaveCanvas" class="btn btn--primary" data-consulta="<?php echo $consulta['id_consulta']; ?>">Guardar Dibujo</button>
                <button id="btnClearCanvas" class="btn" style="background:#888;">Limpiar</button>
            </div>
            <script src="<?php $bU = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'])); echo $bU === '/' ? '' : $bU; ?>/assets/js/canvas.js"></script>
        <?php else: ?>
            <?php if ($consulta['anotaciones_canvas']): ?>
                <p>Dibujo guardado por el médico:</p>
                <img src="<?php $bU = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'])); echo $bU === '/' ? '' : $bU; ?>/consultas/ver-canvas?id=<?php echo $consulta['id_consulta']; ?>" style="max-width: 100%; border:1px solid #ccc; border-radius: 4px;" alt="Anotaciones">
            <?php else: ?>
                <p>El médico aún no ha realizado anotaciones gráficas.</p>
            <?php endif; ?>
        <?php endif; ?>
    </div>

    <!-- Archivos / Estudios Adjuntos -->
    <div class="card" style="flex:1; min-width: 300px;">
        <h3>Estudios Adjuntos</h3>
        
        <form method="POST" action="<?php $bU = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'])); echo $bU === '/' ? '' : $bU; ?>/estudios/upload" enctype="multipart/form-data" style="background:#f9f9f9; padding:15px; border-radius:4px; margin-bottom:15px;">
            <input type="hidden" name="id_cita" value="<?php echo $cita['id_cita']; ?>">
            <div class="form__group">
                <label class="form__label">Subir Archivo (PDF, JPG, PNG)</label>
                <input type="file" name="estudio_file" accept=".pdf, .jpg, .jpeg, .png" class="form__input" required>
            </div>
            <button type="submit" class="btn btn--primary" style="width:100%;">Subir</button>
        </form>

        <?php if (empty($estudios)): ?>
            <p style="color:#666;">No hay estudios adjuntos.</p>
        <?php else: ?>
            <ul style="list-style:none; padding:0;">
                <?php foreach ($estudios as $est): ?>
                    <li style="background:#fff; border:1px solid #eee; padding:10px; margin-bottom:8px; border-radius:4px; display:flex; justify-content:space-between; align-items:center;">
                        <span style="font-size:0.9rem; word-break:break-all;">
                            <?php if($est['tipo_archivo']=='pdf') echo '📄 '; else echo '🖼️ '; ?>
                            <?php echo htmlspecialchars($est['nombre_archivo']); ?>
                        </span>
                        <a href="<?php $bU = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'])); echo $bU === '/' ? '' : $bU; ?>/estudios/download?id=<?php echo $est['id_estudio']; ?>" target="_blank" class="btn btn--success" style="padding: 4px 8px; font-size: 0.8rem;">Ver</a>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </div>
</div>
<?php endif; ?>

<?php 
$content = ob_get_clean();
require __DIR__ . '/../layouts/main.php'; 
?>
