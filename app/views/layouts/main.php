<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MediControl</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Outfit:wght@400;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --color-primary: #6366f1;
            --color-primary-dark: #4f46e5;
            --color-primary-light: #818cf8;
            --color-accent: #06b6d4;
            --color-success: #10b981;
            --color-success-dark: #059669;
            --color-danger: #ef4444;
            --color-danger-dark: #dc2626;
            --color-warning: #f59e0b;
            --color-warning-dark: #d97706;
            --color-info: #3b82f6;
            --font-base: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            --font-display: 'Outfit', sans-serif;
            --radius-sm: 8px;
            --radius-base: 12px;
            --radius-lg: 16px;
            --radius-xl: 20px;
            --shadow-sm: 0 1px 3px rgba(0,0,0,0.06), 0 1px 2px rgba(0,0,0,0.04);
            --shadow-base: 0 4px 6px -1px rgba(0,0,0,0.07), 0 2px 4px -2px rgba(0,0,0,0.05);
            --shadow-lg: 0 10px 25px -3px rgba(0,0,0,0.08), 0 4px 6px -4px rgba(0,0,0,0.04);
            --shadow-xl: 0 20px 40px -4px rgba(0,0,0,0.1);
            --sidebar-width: 260px;
            --bg-body: #f0f2f5;
            --bg-card: #ffffff;
            --text-primary: #1e293b;
            --text-secondary: #64748b;
            --text-muted: #94a3b8;
            --border-light: #e2e8f0;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: var(--font-base);
            background-color: var(--bg-body);
            display: flex;
            min-height: 100vh;
            color: var(--text-primary);
            -webkit-font-smoothing: antialiased;
        }

        /* ===== SIDEBAR ===== */
        .sidebar {
            width: var(--sidebar-width);
            background: linear-gradient(180deg, #0f172a 0%, #1e293b 100%);
            color: white;
            padding: 0;
            display: flex;
            flex-direction: column;
            position: fixed;
            top: 0;
            left: 0;
            height: 100vh;
            z-index: 100;
            overflow-y: auto;
        }

        .sidebar__header {
            padding: 1.5rem 1.5rem 1rem;
            border-bottom: 1px solid rgba(255,255,255,0.08);
        }

        .sidebar__brand {
            font-family: var(--font-display);
            font-size: 1.6rem;
            font-weight: 800;
            background: linear-gradient(135deg, #818cf8, #06b6d4);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            letter-spacing: -0.5px;
        }

        .sidebar__user {
            padding: 1rem 1.5rem;
            border-bottom: 1px solid rgba(255,255,255,0.08);
        }

        .sidebar__user-name {
            font-size: 0.9rem;
            font-weight: 500;
            color: #e2e8f0;
        }

        .sidebar__user-role {
            font-size: 0.75rem;
            color: var(--color-primary-light);
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-top: 2px;
        }

        .sidebar__nav {
            flex: 1;
            padding: 0.75rem 0;
        }

        .sidebar__link {
            color: #94a3b8;
            text-decoration: none;
            padding: 0.7rem 1.5rem;
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 0.95rem;
            font-weight: 500;
            transition: all 0.2s ease;
            border-left: 3px solid transparent;
        }

        .sidebar__link:hover {
            color: #f1f5f9;
            background: rgba(255,255,255,0.06);
            border-left-color: var(--color-primary-light);
        }

        .sidebar__link-icon {
            font-size: 1.15rem;
            width: 24px;
            text-align: center;
        }

        .sidebar__logout {
            padding: 1rem 1.5rem;
            border-top: 1px solid rgba(255,255,255,0.08);
            margin-top: auto;
        }

        .sidebar__logout a {
            color: #fca5a5;
            text-decoration: none;
            font-size: 0.9rem;
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 0.5rem 0;
            transition: color 0.2s;
        }

        .sidebar__logout a:hover { color: #fecaca; }

        /* ===== MAIN CONTENT ===== */
        .main-content {
            flex: 1;
            margin-left: var(--sidebar-width);
            padding: 2rem 2.5rem;
            min-height: 100vh;
            animation: fadeInContent 0.4s ease-out;
        }

        @keyframes fadeInContent {
            from { opacity: 0; transform: translateY(8px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* ===== PAGE TITLE ===== */
        h1 {
            font-family: var(--font-display);
            font-size: 1.75rem;
            font-weight: 700;
            color: var(--text-primary);
            margin-bottom: 1.5rem;
            letter-spacing: -0.3px;
        }

        h3 {
            font-family: var(--font-display);
            font-weight: 600;
            color: var(--text-primary);
            margin-bottom: 0.75rem;
        }

        /* ===== CARDS ===== */
        .card {
            background: var(--bg-card);
            border-radius: var(--radius-base);
            box-shadow: var(--shadow-base);
            padding: 1.5rem;
            margin-bottom: 1.25rem;
            border: 1px solid var(--border-light);
            transition: box-shadow 0.25s ease, transform 0.25s ease;
        }

        .card:hover {
            box-shadow: var(--shadow-lg);
        }

        /* ===== BUTTONS ===== */
        .btn {
            padding: 0.55rem 1.15rem;
            border: none;
            border-radius: var(--radius-sm);
            cursor: pointer;
            color: white;
            font-family: var(--font-base);
            font-size: 0.875rem;
            font-weight: 600;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            transition: all 0.2s ease;
            background: var(--color-primary);
        }

        .btn:hover {
            transform: translateY(-1px);
            box-shadow: var(--shadow-base);
            filter: brightness(1.1);
        }

        .btn:active {
            transform: translateY(0);
        }

        .btn--primary { background: linear-gradient(135deg, var(--color-primary), var(--color-primary-dark)); }
        .btn--danger { background: linear-gradient(135deg, var(--color-danger), var(--color-danger-dark)); }
        .btn--success { background: linear-gradient(135deg, var(--color-success), var(--color-success-dark)); }
        .btn--accent { background: linear-gradient(135deg, var(--color-accent), #0891b2); }
        .btn--warning { background: linear-gradient(135deg, var(--color-warning), var(--color-warning-dark)); color: #1e293b; }
        .btn--ghost {
            background: transparent;
            color: var(--text-secondary);
            border: 1px solid var(--border-light);
        }
        .btn--ghost:hover { background: #f8fafc; color: var(--text-primary); }

        /* ===== TABLES ===== */
        table { width: 100%; border-collapse: collapse; margin-top: 0.75rem; }
        table th, table td {
            padding: 0.85rem 1rem;
            text-align: left;
            border-bottom: 1px solid var(--border-light);
            font-size: 0.9rem;
        }
        table th {
            background-color: #f8fafc;
            color: var(--text-secondary);
            font-weight: 600;
            font-size: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        table tbody tr { transition: background-color 0.15s ease; }
        table tbody tr:hover { background-color: #f8fafc; }
        table tbody tr:nth-child(even) { background-color: #fafbfc; }
        table tbody tr:nth-child(even):hover { background-color: #f1f5f9; }

        /* ===== BADGES ===== */
        .badge {
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 0.78rem;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 4px;
        }
        .badge--solicitada { background: #fef3c7; color: #92400e; }
        .badge--confirmada { background: #d1fae5; color: #065f46; }
        .badge--atendida { background: #dbeafe; color: #1e40af; }
        .badge--cancelada { background: #fee2e2; color: #991b1b; }

        /* ===== FORMS ===== */
        .form__group { margin-bottom: 1rem; }
        .form__label {
            display: block;
            margin-bottom: 0.4rem;
            font-weight: 600;
            font-size: 0.875rem;
            color: var(--text-primary);
        }
        .form__input {
            width: 100%;
            padding: 0.6rem 0.85rem;
            border: 1.5px solid var(--border-light);
            border-radius: var(--radius-sm);
            font-family: var(--font-base);
            font-size: 0.9rem;
            color: var(--text-primary);
            background: #fff;
            transition: border-color 0.2s ease, box-shadow 0.2s ease;
        }
        .form__input:focus {
            outline: none;
            border-color: var(--color-primary-light);
            box-shadow: 0 0 0 3px rgba(99,102,241,0.1);
        }

        /* ===== TOAST ===== */
        .toast {
            position: fixed;
            top: 1.5rem;
            right: 1.5rem;
            background: linear-gradient(135deg, var(--color-success), var(--color-success-dark));
            color: white;
            padding: 0.85rem 1.5rem;
            border-radius: var(--radius-sm);
            box-shadow: var(--shadow-lg);
            font-weight: 500;
            font-size: 0.9rem;
            z-index: 9999;
            display: flex;
            align-items: center;
            gap: 8px;
            animation: toastIn 0.4s ease-out, toastOut 0.4s ease-in 3.5s forwards;
        }
        @keyframes toastIn {
            from { opacity: 0; transform: translateX(100px); }
            to { opacity: 1; transform: translateX(0); }
        }
        @keyframes toastOut {
            from { opacity: 1; transform: translateX(0); }
            to { opacity: 0; transform: translateX(100px); }
        }

        /* ===== MODAL ===== */
        .modal-overlay {
            display: none;
            position: fixed;
            top: 0; left: 0;
            width: 100%; height: 100%;
            background: rgba(15,23,42,0.5);
            backdrop-filter: blur(4px);
            z-index: 1000;
            justify-content: center;
            align-items: center;
        }
        .modal-overlay.active { display: flex; }
        .modal {
            background: white;
            border-radius: var(--radius-lg);
            padding: 2rem;
            width: 100%;
            max-width: 440px;
            box-shadow: var(--shadow-xl);
            animation: modalIn 0.3s ease-out;
        }
        @keyframes modalIn {
            from { opacity: 0; transform: scale(0.95) translateY(10px); }
            to { opacity: 1; transform: scale(1) translateY(0); }
        }
        .modal__title {
            font-family: var(--font-display);
            font-size: 1.25rem;
            font-weight: 700;
            margin-bottom: 1.25rem;
            color: var(--text-primary);
        }
        .modal__actions {
            display: flex;
            gap: 10px;
            justify-content: flex-end;
            margin-top: 1.5rem;
        }

        /* ===== UTILITIES ===== */
        .text-muted { color: var(--text-muted); }
        .text-secondary { color: var(--text-secondary); }
        .flex { display: flex; }
        .gap-sm { gap: 8px; }
        .gap-md { gap: 16px; }
        .gap-lg { gap: 20px; }
    </style>
    <script>
        window.baseUrl = "<?php $bU = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'])); echo $bU === '/' ? '' : $bU; ?>";
    </script>
</head>
<body>
    <aside class="sidebar">
        <div class="sidebar__header">
            <div class="sidebar__brand">MediControl</div>
        </div>
        <?php 
        $rol = \App\Core\Session::get('usuario_rol'); 
        $baseUrl = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
        if ($baseUrl === '/') $baseUrl = '';
        ?>
        <div class="sidebar__user">
            <div class="sidebar__user-name"><?php echo htmlspecialchars(\App\Core\Session::get('usuario_nombre')); ?></div>
            <div class="sidebar__user-role"><?php echo ucfirst($rol); ?></div>
        </div>
        
        <nav class="sidebar__nav">
            <?php if ($rol === 'admin'): ?>
                <a href="<?php echo $baseUrl; ?>/admin/usuarios/nuevo" class="sidebar__link">
                    <span class="sidebar__link-icon">👤</span> Registrar Usuarios
                </a>
            <?php elseif ($rol === 'medico'): ?>
                <a href="<?php echo $baseUrl; ?>/dashboard" class="sidebar__link">
                    <span class="sidebar__link-icon">📊</span> Dashboard
                </a>
                <a href="<?php echo $baseUrl; ?>/pacientes" class="sidebar__link">
                    <span class="sidebar__link-icon">🩺</span> Pacientes
                </a>
                <a href="<?php echo $baseUrl; ?>/citas" class="sidebar__link">
                    <span class="sidebar__link-icon">📅</span> Agenda de Citas
                </a>
                <a href="<?php echo $baseUrl; ?>/comentarios" class="sidebar__link">
                    <span class="sidebar__link-icon">📝</span> Bitácora
                </a>
            <?php else: ?>
                <a href="<?php echo $baseUrl; ?>/citas" class="sidebar__link">
                    <span class="sidebar__link-icon">📅</span> Mis Citas
                </a>
            <?php endif; ?>
        </nav>

        <div class="sidebar__logout">
            <a href="#" onclick="logout()">
                <span>🚪</span> Cerrar Sesión
            </a>
        </div>
    </aside>

    <main class="main-content">
        <?php if (isset($_GET['msg'])): ?>
            <div class="toast">
                ✅ <?php echo htmlspecialchars($_GET['msg']); ?>
            </div>
        <?php endif; ?>
        
        <?php echo $content ?? ''; ?>
    </main>

    <script>
        function logout() {
            fetch('<?php echo $baseUrl; ?>/api/logout', {method:'POST'}).then(()=>window.location='<?php echo $baseUrl; ?>/login');
        }
    </script>
</body>
</html>
