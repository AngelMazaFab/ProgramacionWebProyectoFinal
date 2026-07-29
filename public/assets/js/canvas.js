document.addEventListener('DOMContentLoaded', () => {
    const canvas = document.getElementById('clinicalCanvas');
    if (!canvas) return;

    const ctx = canvas.getContext('2d');
    let isDrawing = false;
    let currentColor = '#C62828';
    let currentWidth = 3;
    let isEraser = false;
    let history = [];
    const MAX_HISTORY = 30;

    // Cargar imagen de fondo (Plantilla anatómica)
    const templateSelector = document.getElementById('canvasTemplate');
    let backgroundImage = new Image();

    function saveState() {
        if (history.length >= MAX_HISTORY) history.shift();
        history.push(canvas.toDataURL());
    }

    function loadTemplate(url) {
        backgroundImage = new Image();
        backgroundImage.crossOrigin = 'anonymous';
        backgroundImage.src = url;
        backgroundImage.onload = () => {
            ctx.clearRect(0, 0, canvas.width, canvas.height);
            ctx.drawImage(backgroundImage, 0, 0, canvas.width, canvas.height);
            history = [];
            saveState();
        };
    }

    if (templateSelector) {
        templateSelector.addEventListener('change', (e) => {
            loadTemplate(e.target.value);
        });
        loadTemplate(templateSelector.value);
    }

    // ===== TOOLBAR SETUP =====
    const toolbar = document.getElementById('canvasToolbar');
    if (toolbar) {
        // Color swatches
        const swatches = toolbar.querySelectorAll('.swatch');
        swatches.forEach(s => {
            s.addEventListener('click', () => {
                swatches.forEach(sw => sw.classList.remove('active'));
                s.classList.add('active');
                currentColor = s.dataset.color;
                isEraser = false;
                const eraserBtn = document.getElementById('btnEraser');
                if (eraserBtn) eraserBtn.classList.remove('active');
            });
        });

        // Custom color picker
        const picker = document.getElementById('colorPicker');
        if (picker) {
            picker.addEventListener('input', (e) => {
                currentColor = e.target.value;
                isEraser = false;
                swatches.forEach(sw => sw.classList.remove('active'));
                const eraserBtn = document.getElementById('btnEraser');
                if (eraserBtn) eraserBtn.classList.remove('active');
            });
        }

        // Width slider
        const widthSlider = document.getElementById('brushWidth');
        const widthLabel = document.getElementById('brushWidthLabel');
        if (widthSlider) {
            widthSlider.addEventListener('input', (e) => {
                currentWidth = parseInt(e.target.value);
                if (widthLabel) widthLabel.textContent = currentWidth + 'px';
            });
        }

        // Eraser
        const eraserBtn = document.getElementById('btnEraser');
        if (eraserBtn) {
            eraserBtn.addEventListener('click', () => {
                isEraser = !isEraser;
                eraserBtn.classList.toggle('active', isEraser);
                if (isEraser) swatches.forEach(sw => sw.classList.remove('active'));
            });
        }

        // Undo
        const undoBtn = document.getElementById('btnUndo');
        if (undoBtn) {
            undoBtn.addEventListener('click', () => {
                if (history.length > 1) {
                    history.pop();
                    const img = new Image();
                    img.src = history[history.length - 1];
                    img.onload = () => {
                        ctx.clearRect(0, 0, canvas.width, canvas.height);
                        ctx.drawImage(img, 0, 0);
                    };
                }
            });
        }
    }

    // ===== DRAWING EVENTS =====
    function getPos(e) {
        const rect = canvas.getBoundingClientRect();
        const scaleX = canvas.width / rect.width;
        const scaleY = canvas.height / rect.height;
        
        let clientX, clientY;
        if (e.touches) {
            clientX = e.touches[0].clientX;
            clientY = e.touches[0].clientY;
        } else {
            clientX = e.clientX;
            clientY = e.clientY;
        }
        return {
            x: (clientX - rect.left) * scaleX,
            y: (clientY - rect.top) * scaleY
        };
    }

    function startDrawing(e) {
        e.preventDefault();
        isDrawing = true;
        saveState();
        ctx.beginPath();
        const pos = getPos(e);
        ctx.moveTo(pos.x, pos.y);
    }

    function draw(e) {
        e.preventDefault();
        if (!isDrawing) return;
        
        ctx.lineWidth = isEraser ? currentWidth * 3 : currentWidth;
        ctx.lineCap = 'round';
        ctx.lineJoin = 'round';
        
        if (isEraser) {
            ctx.globalCompositeOperation = 'destination-out';
            ctx.strokeStyle = 'rgba(0,0,0,1)';
        } else {
            ctx.globalCompositeOperation = 'source-over';
            ctx.strokeStyle = currentColor;
        }
        
        const pos = getPos(e);
        ctx.lineTo(pos.x, pos.y);
        ctx.stroke();
    }

    function stopDrawing(e) {
        if (!isDrawing) return;
        isDrawing = false;
        ctx.closePath();
        ctx.globalCompositeOperation = 'source-over';
    }

    // Mouse events
    canvas.addEventListener('mousedown', startDrawing);
    canvas.addEventListener('mousemove', draw);
    canvas.addEventListener('mouseup', stopDrawing);
    canvas.addEventListener('mouseout', stopDrawing);

    // Touch events
    canvas.addEventListener('touchstart', startDrawing, { passive: false });
    canvas.addEventListener('touchmove', draw, { passive: false });
    canvas.addEventListener('touchend', stopDrawing);
    canvas.addEventListener('touchcancel', stopDrawing);

    // Cursor visualization
    const cursorCircle = document.getElementById('canvasCursor');
    if (cursorCircle) {
        canvas.addEventListener('mousemove', (e) => {
            const rect = canvas.getBoundingClientRect();
            const size = isEraser ? currentWidth * 3 : currentWidth;
            const scale = rect.width / canvas.width;
            const displaySize = size * scale;
            cursorCircle.style.width = displaySize + 'px';
            cursorCircle.style.height = displaySize + 'px';
            cursorCircle.style.left = (e.clientX - displaySize / 2) + 'px';
            cursorCircle.style.top = (e.clientY - displaySize / 2) + 'px';
            cursorCircle.style.display = 'block';
            cursorCircle.style.borderColor = isEraser ? '#94a3b8' : currentColor;
        });
        canvas.addEventListener('mouseout', () => {
            cursorCircle.style.display = 'none';
        });
    }

    // Clear
    const btnClear = document.getElementById('btnClearCanvas');
    if (btnClear) {
        btnClear.addEventListener('click', () => {
            if (!confirm('¿Limpiar todo el lienzo?')) return;
            ctx.clearRect(0, 0, canvas.width, canvas.height);
            if (backgroundImage.src) {
                ctx.drawImage(backgroundImage, 0, 0, canvas.width, canvas.height);
            }
            saveState();
        });
    }

    // Save
    const btnSave = document.getElementById('btnSaveCanvas');
    if (btnSave) {
        btnSave.addEventListener('click', async () => {
            const idConsulta = btnSave.dataset.consulta;
            const dataURL = canvas.toDataURL('image/png');

            btnSave.disabled = true;
            btnSave.innerText = '⏳ Guardando...';

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
                    alert('✅ Lienzo clínico guardado correctamente.');
                    window.location.reload();
                } else {
                    alert('Error: ' + data.error);
                }
            } catch (err) {
                alert('Error de conexión al guardar imagen');
            } finally {
                btnSave.disabled = false;
                btnSave.innerText = '💾 Guardar Dibujo';
            }
        });
    }
});
