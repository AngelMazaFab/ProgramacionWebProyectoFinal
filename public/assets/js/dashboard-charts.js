document.addEventListener('DOMContentLoaded', async () => {
    try {
        // Consumir el endpoint JSON para renderizar métricas dinámicamente
        const res = await fetch('/api/metrics');
        const data = await res.json();

        // 1. Gráfico de Pastel (Distribución de Estados de Cita)
        const ctxCitas = document.getElementById('chartCitas');
        if (ctxCitas) {
            new Chart(ctxCitas, {
                type: 'pie',
                data: {
                    labels: data.citas.map(c => c.estado.toUpperCase()),
                    datasets: [{
                        data: data.citas.map(c => c.total),
                        backgroundColor: ['#fff3e0', '#e8f5e9', '#e3f2fd', '#ffebee'],
                        borderColor: ['#e65100', '#2e7d32', '#1565c0', '#c62828'],
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    plugins: { legend: { position: 'bottom' } }
                }
            });
        }

        // 2. Gráfico de Barras (Tendencia de Consultas)
        const ctxConsultas = document.getElementById('chartConsultas');
        if (ctxConsultas) {
            new Chart(ctxConsultas, {
                type: 'bar',
                data: {
                    labels: data.consultas.map(c => c.mes),
                    datasets: [{
                        label: 'Total Consultas Atendidas',
                        data: data.consultas.map(c => c.total),
                        backgroundColor: '#1F4E79'
                    }]
                },
                options: {
                    responsive: true,
                    scales: {
                        y: { beginAtZero: true, ticks: { stepSize: 1 } }
                    }
                }
            });
        }
    } catch (err) {
        console.error("Error al cargar métricas para Dashboard:", err);
    }
});
