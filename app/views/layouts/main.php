<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MediControl</title>
    <style>
        :root {
            --color-primary: #1F4E79; 
            --color-accent: #2E75B6; 
            --color-success: #2E7D32;
            --color-danger: #C62828;
            --font-base: 'Inter', sans-serif;
            --radius-base: 8px;
        }
        body { margin: 0; font-family: var(--font-base); background-color: #f4f6f8; display: flex; min-height: 100vh;}
        .sidebar { width: 250px; background-color: var(--color-primary); color: white; padding: 20px; display: flex; flex-direction: column; }
        .sidebar h2 { font-size: 1.5rem; margin-top:0; margin-bottom: 2rem; border-bottom: 1px solid rgba(255,255,255,0.2); padding-bottom: 1rem;}
        .sidebar a { color: white; text-decoration: none; padding: 10px 0; display: block; font-size: 1.1rem; }
        .sidebar a:hover { color: #a5c3e6; }
        .main-content { flex: 1; padding: 30px; box-sizing: border-box; }
        
        .card { background: white; border-radius: var(--radius-base); box-shadow: 0 2px 8px rgba(0,0,0,0.05); padding: 20px; margin-bottom: 20px;}
        .btn { padding: 0.5rem 1rem; border: none; border-radius: var(--radius-base); cursor: pointer; color: white; background: var(--color-primary); text-decoration: none; display: inline-block;}
        .btn--danger { background: var(--color-danger); }
        .btn--success { background: var(--color-success); }
        .btn--accent { background: var(--color-accent); }
        
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        table th, table td { padding: 12px; text-align: left; border-bottom: 1px solid #eee; }
        table th { background-color: #f9f9f9; color: var(--color-primary); }
        
        .badge { padding: 4px 8px; border-radius: 12px; font-size: 0.85rem; font-weight: bold; }
        .badge--solicitada { background: #fff3e0; color: #e65100; }
        .badge--confirmada { background: #e8f5e9; color: #2e7d32; }
        .badge--atendida { background: #e3f2fd; color: #1565c0; }
        .badge--cancelada { background: #ffebee; color: #c62828; }
        
        .form__group { margin-bottom: 1rem; }
        .form__label { display: block; margin-bottom: 0.5rem; font-weight: bold;}
        .form__input { width: 100%; padding: 0.5rem; border: 1px solid #ccc; border-radius: 4px; }
    </style>
    <script>
        window.baseUrl = "<?php $bU = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'])); echo $bU === '/' ? '' : $bU; ?>";
    </script>
</head>
<body>
    <aside class="sidebar">
        <h2>MediControl</h2>
        <?php 
        $rol = \App\Core\Session::get('usuario_rol'); 
        $baseUrl = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
        if ($baseUrl === '/') $baseUrl = '';
        ?>
        <p>Hola, <?php echo htmlspecialchars(\App\Core\Session::get('usuario_nombre')); ?> (<?php echo ucfirst($rol); ?>)</p>
        <hr style="border-color: rgba(255,255,255,0.2); width:100%;">
        
        <?php if ($rol === 'admin'): ?>
            <a href="<?php echo $baseUrl; ?>/admin/usuarios/nuevo">Registrar Usuarios</a>
        <?php elseif ($rol === 'medico'): ?>
            <a href="<?php echo $baseUrl; ?>/dashboard">Dashboard</a>
            <a href="<?php echo $baseUrl; ?>/pacientes">Pacientes</a>
            <a href="<?php echo $baseUrl; ?>/citas">Agenda de Citas</a>
            <a href="<?php echo $baseUrl; ?>/comentarios">Bitácora</a>
        <?php else: ?>
            <a href="<?php echo $baseUrl; ?>/citas">Mis Citas</a>
        <?php endif; ?>
        
        <a href="#" onclick="logout()" style="margin-top:auto; color: #ffcccc;">Cerrar Sesión</a>
    </aside>

    <main class="main-content">
        <?php if (isset($_GET['msg'])): ?>
            <div style="background: var(--color-success); color: white; padding: 10px; border-radius: 4px; margin-bottom: 20px;">
                <?php echo htmlspecialchars($_GET['msg']); ?>
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
