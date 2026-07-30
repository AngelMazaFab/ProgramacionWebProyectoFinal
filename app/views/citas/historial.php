<?php ob_start(); ?>
<style>
    .patient-header {
        background: var(--bg-card);
        padding: 1.5rem;
        border-radius: var(--radius-base);
        border: 1px solid var(--border-light);
        box-shadow: var(--shadow-sm);
        margin-bottom: 2rem;
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 1rem;
    }
    .patient-header__info h1 {
        font-family: var(--font-display);
        font-size: 1.5rem;
        margin: 0 0 0.25rem 0;
        color: var(--text-primary);
    }
    .patient-header__info p {
        margin: 0;
        color: var(--text-muted);
        font-size: 0.9rem;
    }
    
    .timeline {
        position: relative;
        max-width: 800px;
        margin: 0 auto;
        padding-left: 2rem;
    }
    .timeline::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        height: 100%;
        width: 2px;
        background: var(--border-light);
    }
    
    .timeline-item {
        position: relative;
        margin-bottom: 2rem;
    }
    .timeline-item:last-child {
        margin-bottom: 0;
    }
    
    .timeline-item__dot {
        position: absolute;
        left: -2.35rem;
        top: 0;
        width: 16px;
        height: 16px;
        border-radius: 50%;
        background: var(--primary);
        border: 3px solid var(--bg-body);
        box-shadow: 0 0 0 2px var(--primary-light);
    }
    
    .timeline-item__dot--atendida { background: #10b981; box-shadow: 0 0 0 2px #d1fae5; }
    .timeline-item__dot--cancelada { background: #ef4444; box-shadow: 0 0 0 2px #fee2e2; }
    .timeline-item__dot--solicitada { background: #f59e0b; box-shadow: 0 0 0 2px #fef3c7; }
    
    .timeline-item__content {
        background: var(--bg-card);
        border-radius: var(--radius-base);
        padding: 1.5rem;
        border: 1px solid var(--border-light);
        box-shadow: var(--shadow-sm);
        transition: all 0.2s ease;
    }
    .timeline-item__content:hover {
        box-shadow: var(--shadow-md);
        transform: translateY(-2px);
    }
    
    .timeline-item__header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        flex-wrap: wrap;
        gap: 0.5rem;
        margin-bottom: 1rem;
        padding-bottom: 0.75rem;
        border-bottom: 1px solid var(--border-light);
    }
    .timeline-item__date {
        font-family: var(--font-display);
        font-weight: 700;
        color: var(--text-primary);
        font-size: 1.1rem;
    }
    
    .timeline-item__body h4 {
        margin: 0 0 0.5rem 0;
        font-size: 0.85rem;
        text-transform: uppercase;
        color: var(--text-muted);
        letter-spacing: 1px;
    }
    .timeline-item__body p {
        margin: 0 0 1.25rem 0;
        color: var(--text-secondary);
        font-size: 0.95rem;
        line-height: 1.5;
    }
    .timeline-item__body p:last-child {
        margin-bottom: 0;
    }
    
    .empty-state {
        text-align: center;
        padding: 4rem 2rem;
        background: var(--bg-card);
        border-radius: var(--radius-base);
        border: 1px dashed var(--border-light);
        color: var(--text-muted);
    }
    .empty-state__icon {
        font-size: 3rem;
        margin-bottom: 1rem;
        opacity: 0.5;
    }

    @media (max-width: 600px) {
        .patient-header {
            flex-direction: column;
            align-items: flex-start;
        }
        .timeline {
            padding-left: 1.25rem;
        }
        .timeline-item__dot {
            left: -1.6rem;
        }
        .timeline-item__content {
            padding: 1rem;
        }
    }
</style>

<div class="patient-header">
    <div class="patient-header__info">
        <h1><?php echo htmlspecialchars($paciente['nombre'] ?? 'Paciente'); ?></h1>
        <p>
            📧 <?php echo htmlspecialchars($paciente['correo'] ?? 'Sin correo'); ?> &nbsp;|&nbsp;
            📱 <?php echo htmlspecialchars($paciente['telefono'] ?? 'Sin teléfono'); ?>
        </p>
    </div>
    <a href="<?php $bU = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'])); echo $bU === '/' ? '' : $bU; ?>/pacientes" class="btn btn--outline">
        ⬅ Volver al Directorio
    </a>
</div>

<?php if (empty($historial)): ?>
    <div class="empty-state">
        <div class="empty-state__icon">📁</div>
        <h2>Sin Historial Clínico</h2>
        <p>Este paciente aún no tiene citas ni consultas registradas con usted.</p>
    </div>
<?php else: ?>
    <div class="timeline">
        <?php foreach ($historial as $cita): 
            $fecha = date('d M Y - h:i A', strtotime($cita['fecha_hora']));
            $estado = strtolower($cita['estado']);
            $claseDot = "timeline-item__dot--{$estado}";
        ?>
            <div class="timeline-item">
                <div class="timeline-item__dot <?php echo $claseDot; ?>"></div>
                <div class="timeline-item__content">
                    <div class="timeline-item__header">
                        <div class="timeline-item__date">📅 <?php echo $fecha; ?></div>
                        <div>
                            <?php if ($estado === 'atendida'): ?>
                                <span class="badge badge--success">✅ Atendida</span>
                            <?php elseif ($estado === 'cancelada'): ?>
                                <span class="badge badge--danger">❌ Cancelada</span>
                            <?php elseif ($estado === 'confirmada'): ?>
                                <span class="badge badge--primary">👍 Confirmada</span>
                            <?php else: ?>
                                <span class="badge badge--warning">⏳ Solicitada</span>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="timeline-item__body">
                        <h4>Motivo de Consulta</h4>
                        <p><?php echo nl2br(htmlspecialchars($cita['motivo'] ?: 'No especificado')); ?></p>
                        
                        <?php if ($estado === 'atendida' && !empty($cita['diagnostico'])): ?>
                            <h4>Diagnóstico Clínico</h4>
                            <p><?php echo nl2br(htmlspecialchars($cita['diagnostico'])); ?></p>
                            
                            <h4>Plan de Tratamiento</h4>
                            <p><?php echo nl2br(htmlspecialchars($cita['tratamiento'] ?: 'Ninguno')); ?></p>
                        <?php endif; ?>

                        <?php if ($estado !== 'cancelada'): ?>
                            <div style="margin-top: 1.25rem;">
                                <?php $bU = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'])); if ($bU === '/') $bU = ''; ?>
                                <a href="<?php echo $bU; ?>/consultas/atender?id_cita=<?php echo $cita['id_cita']; ?>" class="btn btn--outline" style="padding: 0.4rem 0.8rem; font-size: 0.85rem;">
                                    🩺 Abrir Expediente de esta Cita
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<?php 
$content = ob_get_clean();
require __DIR__ . '/../layouts/main.php'; 
?>
