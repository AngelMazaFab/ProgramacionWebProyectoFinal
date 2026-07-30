<?php ob_start(); ?>
<?php $isMedico = \App\Core\Session::get('usuario_rol') === 'medico'; ?>

<style>
    .canvas-toolbar {
        display: flex;
        align-items: center;
        gap: 12px;
        flex-wrap: wrap;
        padding: 10px 14px;
        background: #f8fafc;
        border: 1px solid var(--border-light);
        border-radius: var(--radius-sm) var(--radius-sm) 0 0;
        border-bottom: none;
    }
    .canvas-toolbar__group {
        display: flex;
        align-items: center;
        gap: 6px;
    }
    .canvas-toolbar__label {
        font-size: 0.75rem;
        font-weight: 600;
        color: var(--text-muted);
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    .swatch {
        width: 26px; height: 26px;
        border-radius: 50%;
        border: 2px solid transparent;
        cursor: pointer;
        transition: all 0.15s ease;
    }
    .swatch:hover { transform: scale(1.2); }
    .swatch.active { border-color: var(--text-primary); box-shadow: 0 0 0 2px #fff, 0 0 0 4px var(--text-primary); }
    .canvas-toolbar__divider {
        width: 1px;
        height: 28px;
        background: var(--border-light);
    }
    .tool-btn {
        padding: 5px 10px;
        border: 1px solid var(--border-light);
        border-radius: 6px;
        background: white;
        cursor: pointer;
        font-size: 0.8rem;
        font-weight: 500;
        color: var(--text-secondary);
        transition: all 0.15s ease;
        display: flex;
        align-items: center;
        gap: 4px;
    }
    .tool-btn:hover { background: #f1f5f9; }
    .tool-btn.active { background: var(--color-primary); color: white; border-color: var(--color-primary); }
    .width-slider {
        width: 80px;
        accent-color: var(--color-primary);
    }
    .canvas-wrapper {
        border: 1px solid var(--border-light);
        border-radius: 0 0 var(--radius-sm) var(--radius-sm);
        overflow: hidden;
        background: #fff;
        cursor: crosshair;
        position: relative;
    }
    #canvasCursor {
        display: none;
        position: fixed;
        pointer-events: none;
        border: 2px solid #C62828;
        border-radius: 50%;
        z-index: 9999;
    }
    .cobro-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        background: #d1fae5;
        color: #065f46;
        padding: 6px 14px;
        border-radius: 20px;
        font-weight: 600;
        font-size: 0.9rem;
    }
    .atender-grid { display: flex; gap: 20px; flex-wrap: wrap; }
    .atender-grid > .card { flex: 1; min-width: 300px; }
</style>

<h1>🩺 Atención Médica - Cita #<?php echo $cita['id_cita']; ?></h1>

<div class="atender-grid">
    <!-- Panel Izquierdo: Diagnóstico -->
    <div class="card">
        <h3>📋 Datos Clínicos</h3>
        <?php if (!$consulta): ?>
            <?php if ($isMedico): ?>
                <form method="POST" action="<?php $bU = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'])); echo $bU === '/' ? '' : $bU; ?>/consultas/store">
                    <input type="hidden" name="id_cita" value="<?php echo $cita['id_cita']; ?>">
                    <div class="form__group">
                        <label class="form__label">Diagnóstico</label>
                        <textarea name="diagnostico" class="form__input" rows="4" required placeholder="Describe el diagnóstico del paciente..."></textarea>
                    </div>
                    <div class="form__group">
                        <label class="form__label">Tratamiento e Indicaciones Generales</label>
                        <textarea name="tratamiento" class="form__input" rows="4" placeholder="Indicaciones, recomendaciones, tratamiento..."></textarea>
                    </div>
                    <button type="submit" class="btn btn--primary">✅ Guardar Consulta y Marcar Atendida</button>
                </form>
            <?php else: ?>
                <p style="color:var(--text-muted); padding:1rem 0;">La consulta aún no ha sido registrada por el médico.</p>
            <?php endif; ?>
        <?php else: ?>
            <p><strong>Diagnóstico:</strong><br><?php echo nl2br(htmlspecialchars($consulta['diagnostico'])); ?></p>
            <p style="margin-top:0.75rem;"><strong>Tratamiento:</strong><br><?php echo nl2br(htmlspecialchars($consulta['tratamiento'])); ?></p>
            <p style="margin-top:0.75rem; color:var(--text-muted); font-size:0.85rem;">📅 Registrado el: <?php echo date('d/m/Y H:i', strtotime($consulta['fecha_atencion'])); ?></p>
        <?php endif; ?>
    </div>

    <!-- Panel Derecho: Receta -->
    <?php if ($consulta): ?>
    <div class="card">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1rem;">
            <h3 style="margin-bottom:0;">💊 Receta Médica</h3>
            <a href="<?php $bU = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'])); echo $bU === '/' ? '' : $bU; ?>/receta/pdf?id_consulta=<?php echo $consulta['id_consulta']; ?>" class="btn btn--accent" target="_blank" style="padding:4px 10px; font-size:0.8rem;">📄 Descargar PDF</a>
        </div>
        
        <?php if ($isMedico): ?>
            <form method="POST" action="<?php $bU = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'])); echo $bU === '/' ? '' : $bU; ?>/recetas/store" style="background:#f8fafc; padding:14px; border-radius:var(--radius-sm); margin-bottom:1rem; border:1px solid var(--border-light);">
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
                <button type="submit" class="btn btn--success" style="width:100%;">➕ Agregar Medicamento</button>
            </form>
        <?php endif; ?>

        <?php if (empty($recetas)): ?>
            <p style="color:var(--text-muted);">No hay medicamentos recetados todavía.</p>
        <?php else: ?>
            <table style="font-size: 0.85rem;">
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
                                    <button type="submit" class="btn btn--danger" style="padding: 3px 8px; font-size: 0.75rem;" onclick="return confirm('¿Quitar de la receta?');">✕</button>
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
<div style="display:flex; gap: 20px; flex-wrap: wrap; margin-top:1.25rem;">

    <!-- Canvas -->
    <div class="card" style="flex:2; min-width: 400px;">
        <h3>🎨 Dibujo Clínico</h3>
        
        <?php if ($isMedico): ?>
            <div style="margin-bottom: 10px;">
                <label style="font-size:0.85rem; font-weight:600; color:var(--text-secondary);">Fondo Anatómico:</label>
                <select id="canvasTemplate" class="form__input" style="width:auto; display:inline-block; margin-left:8px;">
                    <?php if ($consulta['anotaciones_canvas']): ?>
                        <option value="<?php $bU = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'])); echo $bU === '/' ? '' : $bU; ?>/consultas/ver-canvas?id=<?php echo $consulta['id_consulta']; ?>">Continuar Dibujo Anterior</option>
                    <?php endif; ?>
                    <option value="<?php $bU = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'])); echo $bU === '/' ? '' : $bU; ?>/assets/img/cuerpo.png">Cuerpo Humano</option>
                    <option value="<?php $bU = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'])); echo $bU === '/' ? '' : $bU; ?>/assets/img/sistema_oseo.png">Sistema Óseo</option>
                    <option value="youtube:UScqQmxmAAw">🎬 Video de Anatomía</option>
                </select>
            </div>

            <!-- Contenedor de video YouTube (oculto por defecto) -->
            <div id="youtubeContainer" style="display:none; margin-bottom:10px;">
                <div style="position:relative; padding-bottom:56.25%; height:0; overflow:hidden; border-radius:var(--radius-sm); border:1px solid var(--border-light);">
                    <iframe id="youtubePlayer" 
                        src="" 
                        style="position:absolute; top:0; left:0; width:100%; height:100%; border:none;" 
                        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" 
                        allowfullscreen>
                    </iframe>
                </div>
            </div>

            <!-- Toolbar -->
            <div class="canvas-toolbar" id="canvasToolbar">
                <div class="canvas-toolbar__group">
                    <span class="canvas-toolbar__label">Color</span>
                    <div class="swatch active" data-color="#C62828" style="background:#C62828;" title="Rojo"></div>
                    <div class="swatch" data-color="#1565C0" style="background:#1565C0;" title="Azul"></div>
                    <div class="swatch" data-color="#2E7D32" style="background:#2E7D32;" title="Verde"></div>
                    <div class="swatch" data-color="#1e293b" style="background:#1e293b;" title="Negro"></div>
                    <div class="swatch" data-color="#E65100" style="background:#E65100;" title="Naranja"></div>
                    <div class="swatch" data-color="#6A1B9A" style="background:#6A1B9A;" title="Morado"></div>
                    <input type="color" id="colorPicker" value="#C62828" title="Color personalizado" style="width:26px; height:26px; border:none; cursor:pointer; border-radius:50%;">
                </div>
                <div class="canvas-toolbar__divider"></div>
                <div class="canvas-toolbar__group">
                    <span class="canvas-toolbar__label">Grosor</span>
                    <input type="range" id="brushWidth" min="1" max="15" value="3" class="width-slider">
                    <span id="brushWidthLabel" style="font-size:0.8rem; color:var(--text-secondary); min-width:28px;">3px</span>
                </div>
                <div class="canvas-toolbar__divider"></div>
                <div class="canvas-toolbar__group">
                    <button type="button" class="tool-btn" id="btnEraser" title="Borrador">🧹 Borrador</button>
                    <button type="button" class="tool-btn" id="btnUndo" title="Deshacer">↩️ Deshacer</button>
                </div>
            </div>

            <div class="canvas-wrapper">
                <canvas id="clinicalCanvas" width="800" height="500" style="display:block; width: 100%; height: auto;"></canvas>
            </div>
            <div id="canvasCursor"></div>
            <div style="margin-top: 10px; display:flex; gap:8px;">
                <button id="btnSaveCanvas" class="btn btn--primary" data-consulta="<?php echo $consulta['id_consulta']; ?>">💾 Guardar Dibujo</button>
                <button id="btnClearCanvas" class="btn btn--ghost">🗑️ Limpiar</button>
            </div>
            <script src="<?php $bU = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'])); echo $bU === '/' ? '' : $bU; ?>/assets/js/canvas.js"></script>
        <?php else: ?>
            <?php if ($consulta['anotaciones_canvas']): ?>
                <p style="color:var(--text-secondary); margin-bottom:0.75rem;">Dibujo guardado por el médico:</p>
                <img src="<?php $bU = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'])); echo $bU === '/' ? '' : $bU; ?>/consultas/ver-canvas?id=<?php echo $consulta['id_consulta']; ?>" style="max-width: 100%; border:1px solid var(--border-light); border-radius: var(--radius-sm);" alt="Anotaciones">
            <?php else: ?>
                <p style="color:var(--text-muted);">El médico aún no ha realizado anotaciones gráficas.</p>
            <?php endif; ?>
        <?php endif; ?>
    </div>

    <!-- Archivos / Estudios Adjuntos -->
    <div class="card" style="flex:1; min-width: 300px;">
        <h3>📎 Estudios Adjuntos</h3>
        
        <form method="POST" action="<?php $bU = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'])); echo $bU === '/' ? '' : $bU; ?>/estudios/upload" enctype="multipart/form-data" style="background:#f8fafc; padding:14px; border-radius:var(--radius-sm); margin-bottom:1rem; border:1px solid var(--border-light);">
            <input type="hidden" name="id_cita" value="<?php echo $cita['id_cita']; ?>">
            <div class="form__group">
                <label class="form__label">Subir Archivo (PDF, JPG, PNG)</label>
                <input type="file" name="estudio_file" accept=".pdf, .jpg, .jpeg, .png" class="form__input" required>
            </div>
            <button type="submit" class="btn btn--primary" style="width:100%;">📤 Subir</button>
        </form>

        <?php if (empty($estudios)): ?>
            <p style="color:var(--text-muted);">No hay estudios adjuntos.</p>
        <?php else: ?>
            <ul style="list-style:none; padding:0;">
                <?php foreach ($estudios as $est): ?>
                    <li style="background:#f8fafc; border:1px solid var(--border-light); padding:10px 12px; margin-bottom:8px; border-radius:var(--radius-sm); display:flex; justify-content:space-between; align-items:center;">
                        <span style="font-size:0.85rem; word-break:break-all;">
                            <?php if($est['tipo_archivo']=='pdf') echo '📄 '; else echo '🖼️ '; ?>
                            <?php echo htmlspecialchars($est['nombre_archivo']); ?>
                        </span>
                        <a href="<?php $bU = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'])); echo $bU === '/' ? '' : $bU; ?>/estudios/download?id=<?php echo $est['id_estudio']; ?>" target="_blank" class="btn btn--success" style="padding: 4px 8px; font-size: 0.75rem;">👁️ Ver</a>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>

        <!-- Sección de Cobro -->
        <div style="margin-top:1.5rem; padding-top:1.25rem; border-top:2px solid var(--border-light);">
            <h3>💰 Cobro de Consulta</h3>
            <?php if (isset($cobro) && $cobro): ?>
                <div class="cobro-badge" style="margin-bottom:0.75rem;">✅ <?php echo $cobro['metodo_pago'] === 'meses' ? 'Plan a Meses' : 'Pagado'; ?></div>
                <div class="stat-row"><span class="stat-row__label">Monto</span><span class="stat-row__value" style="color:var(--color-success);">$<?php echo number_format($cobro['monto'], 2); ?></span></div>
                <div class="stat-row"><span class="stat-row__label">Método</span><span class="stat-row__value"><?php echo ucfirst($cobro['metodo_pago']); ?></span></div>
                <?php if ($cobro['notas']): ?>
                    <div class="stat-row"><span class="stat-row__label">Notas</span><span class="stat-row__value"><?php echo htmlspecialchars($cobro['notas']); ?></span></div>
                <?php endif; ?>
                <div class="stat-row"><span class="stat-row__label">Fecha</span><span class="stat-row__value"><?php echo date('d/m/Y H:i', strtotime($cobro['fecha_cobro'])); ?></span></div>
                
                <?php if ($cobro['metodo_pago'] === 'meses' && isset($planPagoData)): ?>
                    <div style="background:#eef2f6; padding:15px; border-radius:var(--radius-sm); margin-top:1rem;">
                        <p style="margin:0 0 5px 0;"><strong>Frecuencia:</strong> <?php echo ucfirst($planPagoData['frecuencia']); ?></p>
                        <p style="margin:0 0 5px 0;"><strong>Total a pagar:</strong> $<?php echo number_format($cobro['monto'], 2); ?></p>
                        <?php
                            $pagosRealizados = array_filter($planPagoData['amortizaciones'], fn($a) => $a['estado'] === 'pagado');
                            $montoPagado = array_reduce($pagosRealizados, fn($carry, $a) => $carry + $a['monto_pago'], 0);
                        ?>
                        <p style="margin:0;"><strong>Pagos parciales:</strong> $<?php echo number_format($montoPagado, 2); ?></p>
                    </div>
                    <h4 style="margin-top:1.5rem; text-align:center; color:var(--color-primary);">Tabla de Amortización</h4>
                    <div class="table-responsive" style="margin-top:10px;">
                        <table class="table" style="font-size:0.9rem; text-align:center;">
                            <thead>
                                <tr style="background:#f1f5f9;">
                                    <th>NP</th>
                                    <th>Deuda</th>
                                    <th>Pago</th>
                                    <th>Adeudo</th>
                                    <th>Pagado</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($planPagoData['amortizaciones'] as $amort): ?>
                                    <tr id="row-amort-<?php echo $amort['id_amortizacion']; ?>" style="<?php echo $amort['estado'] === 'pagado' ? 'background:#f8fafc; color:#94a3b8;' : ''; ?>">
                                        <td><?php echo $amort['numero_pago']; ?></td>
                                        <td>$<?php echo number_format($amort['deuda_inicial'], 2); ?></td>
                                        <td>$<?php echo number_format($amort['monto_pago'], 2); ?></td>
                                        <td>$<?php echo number_format($amort['adeudo_restante'], 2); ?></td>
                                        <td>
                                            <input type="checkbox" class="toggle-amort" data-id="<?php echo $amort['id_amortizacion']; ?>" <?php echo $amort['estado'] === 'pagado' ? 'checked' : ''; ?>>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            <?php elseif ($isMedico): ?>
                <form method="POST" action="<?php $bU = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'])); echo $bU === '/' ? '' : $bU; ?>/cobros/store" style="background:#f8fafc; padding:14px; border-radius:var(--radius-sm); border:1px solid var(--border-light);">
                    <input type="hidden" name="id_consulta" value="<?php echo $consulta['id_consulta']; ?>">
                    <input type="hidden" name="id_cita" value="<?php echo $cita['id_cita']; ?>">
                    <div class="form__group">
                        <label class="form__label">Monto ($)</label>
                        <input type="number" name="monto" class="form__input" step="0.01" min="0" placeholder="500.00" required>
                    </div>
                    <div class="form__group">
                        <label class="form__label">Método de Pago</label>
                        <select name="metodo_pago" id="metodo_pago_select" class="form__input">
                            <option value="efectivo">💵 Efectivo</option>
                            <option value="tarjeta">💳 Tarjeta</option>
                            <option value="transferencia">🏦 Transferencia</option>
                            <option value="meses">🗓️ Pagar a meses</option>
                        </select>
                    </div>

                    <div id="meses_fields" style="display:none; background:#f1f5f9; padding:10px; border-radius:var(--radius-sm); margin-bottom:1rem; border:1px solid #e2e8f0;">
                        <div class="form__group">
                            <label class="form__label">No. de Pagos</label>
                            <input type="number" name="no_pagos" id="no_pagos" class="form__input" min="1" max="60" placeholder="Ej: 12">
                        </div>
                        <div class="form__group" style="margin-bottom:0;">
                            <label class="form__label">Frecuencia</label>
                            <select name="frecuencia" class="form__input">
                                <option value="quincenal">Quincenal</option>
                                <option value="mensual">Mensual</option>
                            </select>
                        </div>
                    </div>
                    <div class="form__group">
                        <label class="form__label">Notas (opcional)</label>
                        <input type="text" name="notas" class="form__input" placeholder="Observaciones del cobro...">
                    </div>
                    <button type="submit" class="btn btn--success" style="width:100%;">💰 Registrar Cobro</button>
                </form>
            <?php else: ?>
                <p style="color:var(--text-muted);">No se ha registrado un cobro.</p>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php endif; ?>

<script>
document.addEventListener('DOMContentLoaded', () => {
    // 1. Mostrar/ocultar formulario de meses
    const selectMetodo = document.getElementById('metodo_pago_select');
    const mesesFields = document.getElementById('meses_fields');
    const inputNoPagos = document.getElementById('no_pagos');

    if (selectMetodo && mesesFields) {
        selectMetodo.addEventListener('change', (e) => {
            if (e.target.value === 'meses') {
                mesesFields.style.display = 'block';
                inputNoPagos.required = true;
            } else {
                mesesFields.style.display = 'none';
                inputNoPagos.required = false;
            }
        });
    }

    // 2. Toggling estado de amortización
    const toggles = document.querySelectorAll('.toggle-amort');
    toggles.forEach(chk => {
        chk.addEventListener('change', async (e) => {
            const idAmortizacion = e.target.getAttribute('data-id');
            const estado = e.target.checked ? 'pagado' : 'pendiente';
            const row = document.getElementById('row-amort-' + idAmortizacion);

            try {
                const baseUrl = window.location.pathname.replace(/\/consultas\/atender.*/, '');
                const res = await fetch(`${baseUrl}/api/amortizacion/toggle`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        id_amortizacion: idAmortizacion,
                        estado: estado
                    })
                });
                
                const data = await res.json();
                if (data.success) {
                    if (estado === 'pagado') {
                        row.style.background = '#f8fafc';
                        row.style.color = '#94a3b8';
                    } else {
                        row.style.background = '';
                        row.style.color = '';
                    }
                    // Opcional: recargar la página para actualizar totales
                    // window.location.reload(); 
                } else {
                    alert('Error al actualizar el estado.');
                    e.target.checked = !e.target.checked;
                }
            } catch (err) {
                alert('Error de conexión.');
                e.target.checked = !e.target.checked;
            }
        });
    });
});
</script>

<?php 
$content = ob_get_clean();
require __DIR__ . '/../layouts/main.php'; 
?>
