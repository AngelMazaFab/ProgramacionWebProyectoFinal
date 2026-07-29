document.addEventListener('DOMContentLoaded', async () => {
    const base = window.baseUrl || '';
    
    try {
        const res = await fetch(base + '/api/metrics');
        if (!res.ok) throw new Error('HTTP ' + res.status);
        const data = await res.json();

        // ===== POPULATE KPIs =====
        animateValue('kpiPacientes', data.total_pacientes);
        animateValue('kpiCitasMes', data.citas_mes);
        animateValue('kpiAtendidas', data.atendidas_mes);
        animateValue('kpiPendientes', data.pendientes);
        
        document.getElementById('kpiIngresos').textContent = '$' + Number(data.ingresos_mes).toLocaleString('es-MX', {minimumFractionDigits: 2});
        document.getElementById('kpiTicket').textContent = '$' + Number(data.ticket_promedio).toLocaleString('es-MX', {minimumFractionDigits: 2});
        
        document.getElementById('kpiTasaAtencion').textContent = data.tasa_atencion + '%';
        document.getElementById('barAtencion').style.width = data.tasa_atencion + '%';
        
        document.getElementById('kpiTasaCancelacion').textContent = data.tasa_cancelacion + '%';
        document.getElementById('barCancelacion').style.width = data.tasa_cancelacion + '%';

        // Resumen del mes
        document.getElementById('resCitasTotal').textContent = data.citas_mes;
        document.getElementById('resAtendidas').textContent = data.atendidas_mes;
        document.getElementById('resCanceladas').textContent = data.canceladas_mes;
        document.getElementById('resPendientes').textContent = data.pendientes;
        document.getElementById('resIngresos').textContent = '$' + Number(data.ingresos_mes).toLocaleString('es-MX', {minimumFractionDigits: 2});

        // ===== PRÓXIMAS CITAS TABLE =====
        const tbody = document.getElementById('proximasBody');
        if (data.proximas_citas && data.proximas_citas.length > 0) {
            tbody.innerHTML = data.proximas_citas.map(c => {
                const fecha = new Date(c.fecha_hora);
                const fechaStr = fecha.toLocaleDateString('es-MX', { day: '2-digit', month: 'short', hour: '2-digit', minute: '2-digit' });
                return `<tr>
                    <td>${fechaStr}</td>
                    <td>${escapeHtml(c.paciente)}</td>
                    <td>${escapeHtml(c.motivo || 'Sin motivo')}</td>
                </tr>`;
            }).join('');
        } else {
            tbody.innerHTML = '<tr><td colspan="3" style="color:var(--text-muted); text-align:center; padding:2rem;">Sin citas próximas</td></tr>';
        }

        // ===== CHART: PIE (Citas por Estado) =====
        const ctxCitas = document.getElementById('chartCitas');
        if (ctxCitas && data.citas.length > 0) {
            const estadoColors = {
                'solicitada': { bg: '#fef3c7', border: '#f59e0b' },
                'confirmada': { bg: '#d1fae5', border: '#10b981' },
                'atendida':   { bg: '#dbeafe', border: '#3b82f6' },
                'cancelada':  { bg: '#fee2e2', border: '#ef4444' }
            };
            new Chart(ctxCitas, {
                type: 'doughnut',
                data: {
                    labels: data.citas.map(c => c.estado.charAt(0).toUpperCase() + c.estado.slice(1)),
                    datasets: [{
                        data: data.citas.map(c => c.total),
                        backgroundColor: data.citas.map(c => estadoColors[c.estado]?.border || '#94a3b8'),
                        borderColor: '#ffffff',
                        borderWidth: 3,
                        hoverOffset: 8
                    }]
                },
                options: {
                    responsive: true,
                    cutout: '60%',
                    plugins: {
                        legend: { position: 'bottom', labels: { padding: 16, usePointStyle: true, font: { size: 12, family: 'Inter' } } },
                        tooltip: {
                            backgroundColor: '#1e293b',
                            titleFont: { family: 'Inter', weight: '600' },
                            bodyFont: { family: 'Inter' },
                            padding: 12,
                            cornerRadius: 8,
                            callbacks: {
                                label: function(ctx) {
                                    const total = ctx.dataset.data.reduce((a, b) => a + b, 0);
                                    const pct = ((ctx.parsed / total) * 100).toFixed(1);
                                    return ` ${ctx.label}: ${ctx.parsed} (${pct}%)`;
                                }
                            }
                        }
                    },
                    animation: { animateRotate: true, duration: 1000 }
                }
            });
        }

        // ===== CHART: BAR (Consultas por Mes) =====
        const ctxConsultas = document.getElementById('chartConsultas');
        if (ctxConsultas && data.consultas.length > 0) {
            const ctxBar = ctxConsultas.getContext('2d');
            const gradient = ctxBar.createLinearGradient(0, 0, 0, 300);
            gradient.addColorStop(0, '#6366f1');
            gradient.addColorStop(1, '#818cf8');
            
            new Chart(ctxConsultas, {
                type: 'bar',
                data: {
                    labels: data.consultas.map(c => formatMonth(c.mes)),
                    datasets: [{
                        label: 'Consultas',
                        data: data.consultas.map(c => c.total),
                        backgroundColor: gradient,
                        borderRadius: 8,
                        borderSkipped: false,
                        barThickness: 32
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: { display: false },
                        tooltip: { backgroundColor: '#1e293b', cornerRadius: 8, padding: 12, titleFont: { family: 'Inter' }, bodyFont: { family: 'Inter' } }
                    },
                    scales: {
                        y: { beginAtZero: true, ticks: { stepSize: 1, font: { family: 'Inter', size: 11 } }, grid: { color: '#f1f5f9' } },
                        x: { ticks: { font: { family: 'Inter', size: 11 } }, grid: { display: false } }
                    },
                    animation: { duration: 1200, easing: 'easeOutQuart' }
                }
            });
        }

        // ===== CHART: LINE (Ingresos por Mes) =====
        const ctxIngresos = document.getElementById('chartIngresos');
        if (ctxIngresos) {
            const ctxLine = ctxIngresos.getContext('2d');
            const gradientFill = ctxLine.createLinearGradient(0, 0, 0, 280);
            gradientFill.addColorStop(0, 'rgba(16, 185, 129, 0.2)');
            gradientFill.addColorStop(1, 'rgba(16, 185, 129, 0)');

            const ingresosData = data.ingresos_por_mes || [];
            new Chart(ctxIngresos, {
                type: 'line',
                data: {
                    labels: ingresosData.map(i => formatMonth(i.mes)),
                    datasets: [{
                        label: 'Ingresos ($)',
                        data: ingresosData.map(i => parseFloat(i.total)),
                        borderColor: '#10b981',
                        backgroundColor: gradientFill,
                        borderWidth: 3,
                        fill: true,
                        tension: 0.4,
                        pointBackgroundColor: '#10b981',
                        pointBorderColor: '#fff',
                        pointBorderWidth: 2,
                        pointRadius: 5,
                        pointHoverRadius: 8
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            backgroundColor: '#1e293b', cornerRadius: 8, padding: 12,
                            callbacks: { label: ctx => ` $${Number(ctx.parsed.y).toLocaleString('es-MX', {minimumFractionDigits: 2})}` }
                        }
                    },
                    scales: {
                        y: { beginAtZero: true, ticks: { font: { family: 'Inter', size: 11 }, callback: v => '$' + v }, grid: { color: '#f1f5f9' } },
                        x: { ticks: { font: { family: 'Inter', size: 11 } }, grid: { display: false } }
                    },
                    animation: { duration: 1400, easing: 'easeInOutQuart' }
                }
            });
        }

        // ===== CHART: LINE (Tendencia de Citas) =====
        const ctxTendencia = document.getElementById('chartTendencia');
        if (ctxTendencia && data.tendencia_citas) {
            const ctxT = ctxTendencia.getContext('2d');
            const gradT = ctxT.createLinearGradient(0, 0, 0, 280);
            gradT.addColorStop(0, 'rgba(99, 102, 241, 0.15)');
            gradT.addColorStop(1, 'rgba(99, 102, 241, 0)');

            new Chart(ctxTendencia, {
                type: 'line',
                data: {
                    labels: data.tendencia_citas.map(t => formatMonth(t.mes)),
                    datasets: [{
                        label: 'Citas',
                        data: data.tendencia_citas.map(t => t.total),
                        borderColor: '#6366f1',
                        backgroundColor: gradT,
                        borderWidth: 3,
                        fill: true,
                        tension: 0.4,
                        pointBackgroundColor: '#6366f1',
                        pointBorderColor: '#fff',
                        pointBorderWidth: 2,
                        pointRadius: 5,
                        pointHoverRadius: 8
                    }]
                },
                options: {
                    responsive: true,
                    plugins: { legend: { display: false }, tooltip: { backgroundColor: '#1e293b', cornerRadius: 8, padding: 12 } },
                    scales: {
                        y: { beginAtZero: true, ticks: { stepSize: 1, font: { family: 'Inter', size: 11 } }, grid: { color: '#f1f5f9' } },
                        x: { ticks: { font: { family: 'Inter', size: 11 } }, grid: { display: false } }
                    },
                    animation: { duration: 1300, easing: 'easeOutQuart' }
                }
            });
        }

    } catch (err) {
        console.error("Error al cargar métricas para Dashboard:", err);
        document.querySelectorAll('.kpi-card__value').forEach(el => el.textContent = 'Error');
    }
});

// ===== HELPERS =====
function animateValue(elementId, endValue, duration = 800) {
    const el = document.getElementById(elementId);
    if (!el) return;
    endValue = parseInt(endValue) || 0;
    if (endValue === 0) { el.textContent = '0'; return; }
    let start = 0;
    const startTime = performance.now();
    function update(currentTime) {
        const elapsed = currentTime - startTime;
        const progress = Math.min(elapsed / duration, 1);
        const eased = 1 - Math.pow(1 - progress, 3);
        start = Math.round(eased * endValue);
        el.textContent = start.toLocaleString();
        if (progress < 1) requestAnimationFrame(update);
    }
    requestAnimationFrame(update);
}

function formatMonth(mesStr) {
    if (!mesStr) return '';
    const meses = ['Ene','Feb','Mar','Abr','May','Jun','Jul','Ago','Sep','Oct','Nov','Dic'];
    const parts = mesStr.split('-');
    return meses[parseInt(parts[1]) - 1] + ' ' + parts[0].slice(2);
}

function escapeHtml(str) {
    if (!str) return '';
    const div = document.createElement('div');
    div.textContent = str;
    return div.innerHTML;
}
