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
        * { box-sizing: border-box; }
        body {
            font-family: var(--font-base);
            background-color: #f4f6f8;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
            padding: 1.5rem 1rem;
        }
        .auth-card {
            background: #fff;
            padding: 2rem 1.5rem;
            border-radius: var(--radius-base);
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            width: 100%;
            max-width: 400px;
        }
        .auth-card__title { color: var(--color-primary); text-align: center; margin-bottom: 0.5rem; font-size: 1.75rem; }
        .form__group { margin-bottom: 1rem; }
        .form__label { display: block; margin-bottom: 0.5rem; color: #333; font-weight: 500; font-size: 0.9rem; }
        .form__input { width: 100%; padding: 0.75rem; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; font-size: 0.95rem; }
        .btn { width: 100%; padding: 0.75rem; border: none; border-radius: var(--radius-base); background-color: var(--color-primary); color: #fff; cursor: pointer; font-size: 1rem; font-weight: 600; transition: background 0.3s; }
        .btn:hover { background-color: var(--color-accent); }
        .auth-card__error { color: #C62828; font-size: 0.875rem; margin-top: 1rem; display: none; word-break: break-word; }
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

        // Cargar configuración inyectada por PHP desde el .env
        const firebaseConfig = {
            apiKey: "<?php echo $_ENV['FIREBASE_API_KEY'] ?? $_SERVER['FIREBASE_API_KEY'] ?? getenv('FIREBASE_API_KEY') ?? ''; ?>",
            authDomain: "<?php echo $_ENV['FIREBASE_AUTH_DOMAIN'] ?? $_SERVER['FIREBASE_AUTH_DOMAIN'] ?? getenv('FIREBASE_AUTH_DOMAIN') ?? ''; ?>",
            projectId: "<?php echo $_ENV['FIREBASE_PROJECT_ID'] ?? $_SERVER['FIREBASE_PROJECT_ID'] ?? getenv('FIREBASE_PROJECT_ID') ?? ''; ?>",
            storageBucket: "<?php echo $_ENV['FIREBASE_STORAGE_BUCKET'] ?? $_SERVER['FIREBASE_STORAGE_BUCKET'] ?? getenv('FIREBASE_STORAGE_BUCKET') ?? ''; ?>",
            messagingSenderId: "<?php echo $_ENV['FIREBASE_MESSAGING_SENDER_ID'] ?? $_SERVER['FIREBASE_MESSAGING_SENDER_ID'] ?? getenv('FIREBASE_MESSAGING_SENDER_ID') ?? ''; ?>",
            appId: "<?php echo $_ENV['FIREBASE_APP_ID'] ?? $_SERVER['FIREBASE_APP_ID'] ?? getenv('FIREBASE_APP_ID') ?? ''; ?>"
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
            const err = document.getElementById('errorMsg');
            err.style.display = 'none';
            
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
                    err.style.display = 'block';
                    err.innerText = data.error || 'Error de servidor backend';
                }
            } catch (error) {
                console.error("Error al autenticar con Firebase:", error);
                err.style.display = 'block';
                
                let message = 'Error de autenticación en Firebase.';
                if (error.code === 'auth/user-not-found' || error.code === 'auth/invalid-credential') {
                    message = 'Usuario o contraseña incorrectos en Firebase. Asegúrate de registrar el usuario en la Consola de Firebase.';
                } else if (error.code === 'auth/wrong-password') {
                    message = 'Contraseña incorrecta.';
                } else if (error.code === 'auth/invalid-email') {
                    message = 'El formato del correo electrónico no es válido.';
                } else if (error.code === 'auth/too-many-requests') {
                    message = 'Demasiados intentos fallidos. Intenta más tarde.';
                } else if (error.message && error.message.includes('API key')) {
                    message = 'La API Key de Firebase en tu archivo .env no es válida.';
                } else if (error.message) {
                    message = `Error de Firebase (${error.code || 'desconocido'}): ${error.message}`;
                }
                
                err.innerText = message;
            } finally {
                btn.disabled = false;
                btn.innerText = 'Ingresar';
            }
        });
    </script>
</body>
</html>
