<?php ob_start(); ?>
<h1>Dashboard de Control (KPIs)</h1>

<div style="display: flex; gap: 20px; margin-bottom: 20px;">
    <div class="card" style="flex: 1; display:flex; flex-direction:column; justify-content:center; align-items:flex-start;">
        <h3>Reporte Ejecutivo Mensual</h3>
        <p style="color:#666;">Exporte el resumen de la actividad clínica y consultas del mes actual para auditoría o administración.</p>
        <a href="<?php $bU = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'])); echo $bU === '/' ? '' : $bU; ?>/dashboard/reporte" class="btn btn--success" target="_blank">📄 Descargar PDF Mensual</a>
    </div>
</div>

<div style="display: flex; gap: 20px; flex-wrap: wrap;">
    <div class="card" style="flex: 1; min-width: 300px;">
        <h3>Distribución de Citas por Estado</h3>
        <canvas id="chartCitas"></canvas>
    </div>
    <div class="card" style="flex: 1; min-width: 300px;">
        <h3>Consultas Atendidas (Últimos meses)</h3>
        <canvas id="chartConsultas"></canvas>
    </div>
</div>

<!-- Chart.js consumido vía CDN tal como lo define el estándar FrontEnd -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="<?php $bU = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'])); echo $bU === '/' ? '' : $bU; ?>/assets/js/dashboard-charts.js"></script>

<?php 
$content = ob_get_clean();
require __DIR__ . '/../layouts/main.php'; 
?>
