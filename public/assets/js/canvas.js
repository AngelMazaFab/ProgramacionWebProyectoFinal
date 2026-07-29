document.addEventListener('DOMContentLoaded', () => {
    const canvas = document.getElementById('clinicalCanvas');
    if (!canvas) return;

    const ctx = canvas.getContext('2d');
    let isDrawing = false;

    // Cargar imagen de fondo (Plantilla anatómica)
    const templateSelector = document.getElementById('canvasTemplate');
    let backgroundImage = new Image();

    function loadTemplate(url) {
        backgroundImage.src = url;
        backgroundImage.onload = () => {
            ctx.clearRect(0, 0, canvas.width, canvas.height);
            ctx.drawImage(backgroundImage, 0, 0, canvas.width, canvas.height);
        };
    }

    if (templateSelector) {
        templateSelector.addEventListener('change', (e) => {
            loadTemplate(e.target.value);
        });
        loadTemplate(templateSelector.value); // inicial
    }

    // Eventos de dibujo
    canvas.addEventListener('mousedown', startDrawing);
    canvas.addEventListener('mousemove', draw);
    canvas.addEventListener('mouseup', stopDrawing);
    canvas.addEventListener('mouseout', stopDrawing);

    function getScaledPos(e) {
        const rect = canvas.getBoundingClientRect();
        const scaleX = canvas.width / rect.width;
        const scaleY = canvas.height / rect.height;
        return {
            x: (e.clientX - rect.left) * scaleX,
            y: (e.clientY - rect.top) * scaleY
        };
    }

    function startDrawing(e) {
        isDrawing = true;
        ctx.beginPath();
        const pos = getScaledPos(e);
        ctx.moveTo(pos.x, pos.y);
    }

    function draw(e) {
        if (!isDrawing) return;
        ctx.lineWidth = 3;
        ctx.lineCap = 'round';
        ctx.strokeStyle = '#C62828'; // Rojo clínico para marcas
        const pos = getScaledPos(e);
        ctx.lineTo(pos.x, pos.y);
        ctx.stroke();
    }

    function stopDrawing() {
        if (!isDrawing) return;
        isDrawing = false;
        ctx.closePath();
    }

    const btnClear = document.getElementById('btnClearCanvas');
    if (btnClear) {
        btnClear.addEventListener('click', () => {
            ctx.clearRect(0, 0, canvas.width, canvas.height);
            if (backgroundImage.src) {
                ctx.drawImage(backgroundImage, 0, 0, canvas.width, canvas.height);
            }
        });
    }

    const btnSave = document.getElementById('btnSaveCanvas');
    if (btnSave) {
        btnSave.addEventListener('click', async () => {
            const idConsulta = btnSave.dataset.consulta;
            const dataURL = canvas.toDataURL('image/png');

            btnSave.disabled = true;
            btnSave.innerText = 'Guardando...';

            try {
                const url = window.baseUrl ? window.baseUrl + '/consultas/canvas' : '/consultas/canvas';
                const res = await fetch(url, {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ id_consulta: idConsulta, image: dataURL })
                });
                
                const data = await res.json();
                if (data.success) {
                    alert('Lienzo clínico guardado correctamente.');
                    window.location.reload();
                } else {
                    alert('Error: ' + data.error);
                }
            } catch (err) {
                alert('Error de conexión al guardar imagen');
            } finally {
                btnSave.disabled = false;
                btnSave.innerText = 'Guardar Dibujo';
            }
        });
    }
});
