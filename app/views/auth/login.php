<?php
$baseUrl = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
if ($baseUrl === '/') $baseUrl = '';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MediControl - Iniciar Sesión</title>
    <!-- CSS BEM Minimalista -->
    <style>
        :root { --color-primary: #1F4E79; --color-accent: #2E75B6; --font-base: 'Inter', sans-serif; --radius-base: 8px; }
        body { font-family: var(--font-base); background-color: #f4f6f8; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; }
        .auth-card { background: #fff; padding: 2rem; border-radius: var(--radius-base); box-shadow: 0 4px 12px rgba(0,0,0,0.1); width: 100%; max-width: 400px; }
        .auth-card__title { color: var(--color-primary); text-align: center; margin-bottom: 1.5rem; }
        .form__group { margin-bottom: 1rem; }
        .form__label { display: block; margin-bottom: 0.5rem; color: #333; }
        .form__input { width: 100%; padding: 0.75rem; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; }
        .btn { width: 100%; padding: 0.75rem; border: none; border-radius: var(--radius-base); background-color: var(--color-primary); color: #fff; cursor: pointer; font-size: 1rem; transition: background 0.3s; }
        .btn:hover { background-color: var(--color-accent); }
        .auth-card__error { color: #C62828; font-size: 0.875rem; margin-top: 1rem; display: none; }
    </style>
</head>
<body>
    <div class="auth-card">
        <h1 class="auth-card__title">MediControl</h1>
        <p style="text-align:center; color:#666; margin-bottom:20px;">Acceso de Usuarios</p>
        
        <form id="loginForm" class="form">
            <div class="form__group">
                <label class="form__label" for="email">Correo Electrónico</label>
                <input class="form__input" type="email" id="email" required>
            </div>
            <div class="form__group">
                <label class="form__label" for="password">Contraseña</label>
                <input class="form__input" type="password" id="password" required>
            </div>
            <button type="submit" class="btn btn--primary">Ingresar</button>
            <div id="errorMsg" class="auth-card__error">Error al iniciar sesión</div>
        </form>
    </div>

    <!-- Firebase SDK Real -->
    <script type="module">
        import { initializeApp } from "https://www.gstatic.com/firebasejs/10.7.1/firebase-app.js";
        import { getAuth, signInWithEmailAndPassword } from "https://www.gstatic.com/firebasejs/10.7.1/firebase-auth.js";

        // Cargar configuración inyectada por PHP desde el .env usando $_SERVER que es más seguro en XAMPP
        const firebaseConfig = {
            apiKey: "<?php echo $_SERVER['FIREBASE_API_KEY'] ?? ''; ?>",
            authDomain: "<?php echo $_SERVER['FIREBASE_AUTH_DOMAIN'] ?? ''; ?>",
            projectId: "<?php echo $_SERVER['FIREBASE_PROJECT_ID'] ?? ''; ?>",
            storageBucket: "<?php echo $_SERVER['FIREBASE_STORAGE_BUCKET'] ?? ''; ?>",
            messagingSenderId: "<?php echo $_SERVER['FIREBASE_MESSAGING_SENDER_ID'] ?? ''; ?>",
            appId: "<?php echo $_SERVER['FIREBASE_APP_ID'] ?? ''; ?>"
        };

        let app, auth;
        try {
            app = initializeApp(firebaseConfig);
            auth = getAuth(app);
        } catch (e) {
            console.error("Firebase no configurado correctamente. Revisa tu archivo .env", e);
        }

        document.getElementById('loginForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            const email = document.getElementById('email').value;
            const password = document.getElementById('password').value;
            const btn = document.querySelector('button[type="submit"]');
            
            btn.disabled = true;
            btn.innerText = 'Autenticando...';
            
            try {
                // Autenticación real contra los servidores de Firebase
                const userCredential = await signInWithEmailAndPassword(auth, email, password);
                const token = await userCredential.user.getIdToken();
                
                // Enviar el token al backend de PHP
                const response = await fetch('<?php echo $baseUrl; ?>/api/login', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        token: token,
                        uid: userCredential.user.uid,
                        email: userCredential.user.email,
                        name: userCredential.user.displayName || 'Usuario'
                    })
                });

                const data = await response.json();
                if (data.success) {
                    window.location.href = '<?php echo $baseUrl; ?>' + data.redirect;
                } else {
                    const err = document.getElementById('errorMsg');
                    err.style.display = 'block';
                    err.innerText = data.error || 'Error de servidor backend';
                }
            } catch (error) {
                console.error(error);
                const err = document.getElementById('errorMsg');
                err.style.display = 'block';
                err.innerText = 'Credenciales inválidas en Firebase o servidor caído.';
            } finally {
                btn.disabled = false;
                btn.innerText = 'Ingresar';
            }
        });
    </script>
</body>
</html>
