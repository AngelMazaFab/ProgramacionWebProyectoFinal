<?php
/** @var array $pacientes — Provided by CitaController::create() */
ob_start();
$baseUrl = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
if ($baseUrl === '/') $baseUrl = '';
?>
<h1>Agendar Nueva Cita</h1>

<style>
    .patient-search-wrapper {
        position: relative;
    }

    .patient-search-wrapper .search-icon {
        position: absolute;
        left: 10px;
        top: 50%;
        transform: translateY(-50%);
        color: var(--text-muted);
        pointer-events: none;
        display: flex;
        align-items: center;
    }

    .patient-search-wrapper input#paciente-search {
        padding-left: 2.2rem;
    }

    .patient-search-wrapper input#paciente-search:focus {
        outline: none;
        border-color: var(--color-primary-light);
        box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
    }
</style>

<div class="card" style="max-width: 500px;">
    <form method="POST" action="<?php echo $baseUrl; ?>/citas/store">
        <div class="form__group">
            <label class="form__label">Seleccionar Paciente</label>

            <!-- Campo de búsqueda con lupa -->
            <div class="patient-search-wrapper">
                <span class="search-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"
                        fill="none" stroke="currentColor" stroke-width="2.5"
                        stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="11" cy="11" r="8"></circle>
                        <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                    </svg>
                </span>
                <input
                    type="text"
                    id="paciente-search"
                    class="form__input"
                    placeholder="Buscar paciente..."
                    autocomplete="off"
                    style="margin-bottom: 0.4rem;">
            </div>

            <!-- Select real que se envía en el formulario -->
            <select name="id_paciente" id="select-paciente" class="form__input" required size="5"
                style="height: auto; overflow-y: auto;">
                <option value="">-- Elija un paciente --</option>
                <?php foreach ($pacientes as $p): ?>
                    <option value="<?php echo $p['id_usuario']; ?>">
                        <?php echo htmlspecialchars($p['nombre']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form__group">
            <label class="form__label">Fecha y Hora</label>
            <input type="datetime-local" name="fecha_hora" class="form__input" required>
        </div>

        <div class="form__group">
            <label class="form__label">Motivo de la Cita</label>
            <textarea name="motivo" class="form__input" rows="3" required></textarea>
        </div>

        <button type="submit" class="btn btn--primary">Agendar</button>
        <a href="<?php echo $baseUrl; ?>/citas" class="btn" style="background:#666; margin-left:10px;">Cancelar</a>
    </form>
</div>

<script>
    (function() {
        var searchInput = document.getElementById('paciente-search');
        var select = document.getElementById('select-paciente');
        // Guardar todas las opciones originales (sin la primera vacía)
        var allOptions = Array.from(select.options).slice(1).map(function(opt) {
            return {
                value: opt.value,
                text: opt.text
            };
        });

        searchInput.addEventListener('input', function() {
            var query = this.value.toLowerCase().trim();
            // Limpiar opciones actuales (excepto la primera)
            while (select.options.length > 1) {
                select.remove(1);
            }
            // Re-agregar solo las que coincidan
            allOptions.forEach(function(opt) {
                if (!query || opt.text.toLowerCase().includes(query)) {
                    var newOpt = document.createElement('option');
                    newOpt.value = opt.value;
                    newOpt.textContent = opt.text;
                    select.appendChild(newOpt);
                }
            });
        });
    })();
</script>

<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/main.php';
?>