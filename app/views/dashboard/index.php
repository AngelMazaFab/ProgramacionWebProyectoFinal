<?php ob_start(); ?>
<style>
    .dashboard-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; margin-bottom: 1.5rem; }
    .kpi-card {
        background: var(--bg-card);
        border-radius: var(--radius-base);
        padding: 1.25rem;
        border: 1px solid var(--border-light);
        box-shadow: var(--shadow-sm);
        display: flex;
        align-items: flex-start;
        gap: 1rem;
        transition: all 0.25s ease;
    }
    .kpi-card:hover { box-shadow: var(--shadow-lg); transform: translateY(-2px); }
    .kpi-card__icon {
        width: 48px; height: 48px;
        border-radius: var(--radius-sm);
        display: flex; align-items: center; justify-content: center;
        font-size: 1.4rem;
        flex-shrink: 0;
    }
    .kpi-card__icon--blue { background: #ede9fe; }
    .kpi-card__icon--green { background: #d1fae5; }
    .kpi-card__icon--amber { background: #fef3c7; }
    .kpi-card__icon--red { background: #fee2e2; }
    .kpi-card__icon--cyan { background: #cffafe; }
    .kpi-card__icon--purple { background: #f3e8ff; }
    .kpi-card__value {
        font-family: var(--font-display);
        font-size: 1.75rem;
        font-weight: 800;
        color: var(--text-primary);
        line-height: 1;
    }
    .kpi-card__label { font-size: 0.8rem; color: var(--text-muted); font-weight: 500; margin-top: 2px; }

    .charts-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 1.25rem; margin-bottom: 1.5rem; }
    .chart-card { background: var(--bg-card); border-radius: var(--radius-base); padding: 1.5rem; border: 1px solid var(--border-light); box-shadow: var(--shadow-sm); }
    .chart-card__title { font-family: var(--font-display); font-size: 1rem; font-weight: 600; color: var(--text-primary); margin-bottom: 1rem; }
    
    .bottom-grid { display: grid; grid-template-columns: 1.2fr 0.8fr; gap: 1.25rem; }
    
    .progress-bar { width: 100%; height: 8px; background: #f1f5f9; border-radius: 4px; overflow: hidden; margin-top: 6px; }
    .progress-bar__fill { height: 100%; border-radius: 4px; transition: width 0.8s ease; }
    .progress-bar__fill--green { background: linear-gradient(90deg, #10b981, #059669); }
    .progress-bar__fill--red { background: linear-gradient(90deg, #ef4444, #dc2626); }

    .stat-row { display: flex; justify-content: space-between; align-items: center; padding: 0.75rem 0; border-bottom: 1px solid var(--border-light); }
    .stat-row:last-child { border-bottom: none; }
    .stat-row__label { font-size: 0.875rem; color: var(--text-secondary); }
    .stat-row__value { font-weight: 700; font-size: 0.95rem; }

    .proximas-table { font-size: 0.85rem; }
    .proximas-table td, .proximas-table th { padding: 0.65rem 0.75rem; }

    .section-title {
        font-family: var(--font-display);
        font-size: 0.8rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 1.5px;
        color: var(--text-muted);
        margin-bottom: 1rem;
        padding-bottom: 0.5rem;
        border-bottom: 2px solid var(--border-light);
    }

    .header-row { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; }

    @media (max-width: 768px) {
        .charts-grid, .bottom-grid { grid-template-columns: 1fr; }
        .dashboard-grid { grid-template-columns: repeat(2, 1fr); }
    }
</style>

<div class="header-row">
    <h1>📊 Dashboard</h1>
    <a href="<?php $bU = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'])); echo $bU === '/' ? '' : $bU; ?>/dashboard/reporte" class="btn btn--success" target="_blank">📄 Descargar Reporte PDF</a>
</div>

<!-- KPIs Clínicos -->
<div class="section-title">Indicadores Clínicos</div>
<div class="dashboard-grid" id="kpiGrid">
    <div class="kpi-card">
        <div class="kpi-card__icon kpi-card__icon--blue">🩺</div>
        <div><div class="kpi-card__value" id="kpiPacientes">--</div><div class="kpi-card__label">Pacientes</div></div>
    </div>
    <div class="kpi-card">
        <div class="kpi-card__icon kpi-card__icon--cyan">📅</div>
        <div><div class="kpi-card__value" id="kpiCitasMes">--</div><div class="kpi-card__label">Citas este mes</div></div>
    </div>
    <div class="kpi-card">
        <div class="kpi-card__icon kpi-card__icon--green">✅</div>
        <div><div class="kpi-card__value" id="kpiAtendidas">--</div><div class="kpi-card__label">Consultas atendidas</div></div>
    </div>
    <div class="kpi-card">
        <div class="kpi-card__icon kpi-card__icon--amber">⏳</div>
        <div><div class="kpi-card__value" id="kpiPendientes">--</div><div class="kpi-card__label">Citas pendientes</div></div>
    </div>
</div>

<!-- KPIs Financieros -->
<div class="section-title">Indicadores Financieros</div>
<div class="dashboard-grid">
    <div class="kpi-card">
        <div class="kpi-card__icon kpi-card__icon--green">💰</div>
        <div><div class="kpi-card__value" id="kpiIngresos">--</div><div class="kpi-card__label">Ingresos del mes</div></div>
    </div>
    <div class="kpi-card">
        <div class="kpi-card__icon kpi-card__icon--purple">🎫</div>
        <div><div class="kpi-card__value" id="kpiTicket">--</div><div class="kpi-card__label">Ticket promedio</div></div>
    </div>
    <div class="kpi-card">
        <div class="kpi-card__icon kpi-card__icon--green">📈</div>
        <div>
            <div class="kpi-card__value" id="kpiTasaAtencion">--</div>
            <div class="kpi-card__label">Tasa de atención</div>
            <div class="progress-bar"><div class="progress-bar__fill progress-bar__fill--green" id="barAtencion" style="width:0%"></div></div>
        </div>
    </div>
    <div class="kpi-card">
        <div class="kpi-card__icon kpi-card__icon--red">📉</div>
        <div>
            <div class="kpi-card__value" id="kpiTasaCancelacion">--</div>
            <div class="kpi-card__label">Tasa de cancelación</div>
            <div class="progress-bar"><div class="progress-bar__fill progress-bar__fill--red" id="barCancelacion" style="width:0%"></div></div>
        </div>
    </div>
</div>

<!-- Gráficas -->
<div class="charts-grid">
    <div class="chart-card">
        <div class="chart-card__title">Distribución de Citas por Estado</div>
        <canvas id="chartCitas"></canvas>
    </div>
    <div class="chart-card">
        <div class="chart-card__title">Consultas Atendidas por Mes</div>
        <canvas id="chartConsultas"></canvas>
    </div>
    <div class="chart-card">
        <div class="chart-card__title">Tendencia de Ingresos</div>
        <canvas id="chartIngresos"></canvas>
    </div>
    <div class="chart-card">
        <div class="chart-card__title">Volumen de Citas por Mes</div>
        <canvas id="chartTendencia"></canvas>
    </div>
</div>

<!-- Próximas Citas + Resumen Actividad -->
<div class="bottom-grid">
    <div class="card">
        <h3>📅 Próximas Citas</h3>
        <table class="proximas-table" id="tablaCitas">
            <thead>
                <tr><th>Fecha</th><th>Paciente</th><th>Motivo</th></tr>
            </thead>
            <tbody id="proximasBody">
                <tr><td colspan="3" style="color:var(--text-muted); text-align:center;">Cargando...</td></tr>
            </tbody>
        </table>
    </div>
    <div class="card">
        <h3>📋 Resumen del Mes</h3>
        <div id="resumenMes">
            <div class="stat-row"><span class="stat-row__label">Citas totales</span><span class="stat-row__value" id="resCitasTotal">--</span></div>
            <div class="stat-row"><span class="stat-row__label">Atendidas</span><span class="stat-row__value" style="color:var(--color-success)" id="resAtendidas">--</span></div>
            <div class="stat-row"><span class="stat-row__label">Canceladas</span><span class="stat-row__value" style="color:var(--color-danger)" id="resCanceladas">--</span></div>
            <div class="stat-row"><span class="stat-row__label">Pendientes</span><span class="stat-row__value" style="color:var(--color-warning)" id="resPendientes">--</span></div>
            <div class="stat-row"><span class="stat-row__label">Ingresos</span><span class="stat-row__value" style="color:var(--color-success)" id="resIngresos">--</span></div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="<?php $bU = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'])); echo $bU === '/' ? '' : $bU; ?>/assets/js/dashboard-charts.js"></script>

<?php 
$content = ob_get_clean();
require __DIR__ . '/../layouts/main.php'; 
?>
