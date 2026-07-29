<?php 
ob_start(); 
$baseUrl = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
if ($baseUrl === '/') $baseUrl = '';
?>
<h1>Registrar Nuevo Usuario</h1>

<div class="card" style="max-width: 500px;">
    <form id="registerForm">
        <div class="form__group">
            <label class="form__label">Nombre Completo</label>
            <input type="text" id="regName" class="form__input" required>
        </div>
        <div class="form__group">
            <label class="form__label">Correo Electrónico</label>
            <input type="email" id="regEmail" class="form__input" required>
        </div>
        <div class="form__group">
            <label class="form__label">Teléfono</label>
            <input type="tel" id="regTelefono" class="form__input">
        </div>
        <div class="form__group">
            <label class="form__label">Contraseña</label>
            <input type="password" id="regPassword" class="form__input" required minlength="6">
        </div>
        <div class="form__group">
            <label class="form__label">Rol del Sistema</label>
            <select id="regRole" class="form__input" required>
                <option value="paciente">Paciente</option>
                <option value="medico">Médico</option>
                <option value="admin">Administrador</option>
            </select>
        </div>
        <button type="submit" class="btn btn--primary" id="btnRegister">Registrar Usuario</button>
        <div id="regMsg" style="margin-top: 15px; font-weight: bold; display: none;"></div>
    </form>
</div>

<!-- Firebase SDK Real -->
<script type="module">
    import { initializeApp } from "https://www.gstatic.com/firebasejs/10.7.1/firebase-app.js";
    import { getAuth, createUserWithEmailAndPassword } from "https://www.gstatic.com/firebasejs/10.7.1/firebase-auth.js";

    const firebaseConfig = {
        apiKey: "<?php echo $_ENV['FIREBASE_API_KEY'] ?? $_SERVER['FIREBASE_API_KEY'] ?? ''; ?>",
        authDomain: "<?php echo $_ENV['FIREBASE_AUTH_DOMAIN'] ?? $_SERVER['FIREBASE_AUTH_DOMAIN'] ?? ''; ?>",
        projectId: "<?php echo $_ENV['FIREBASE_PROJECT_ID'] ?? $_SERVER['FIREBASE_PROJECT_ID'] ?? ''; ?>",
        storageBucket: "<?php echo $_ENV['FIREBASE_STORAGE_BUCKET'] ?? $_SERVER['FIREBASE_STORAGE_BUCKET'] ?? ''; ?>",
        messagingSenderId: "<?php echo $_ENV['FIREBASE_MESSAGING_SENDER_ID'] ?? $_SERVER['FIREBASE_MESSAGING_SENDER_ID'] ?? ''; ?>",
        appId: "<?php echo $_ENV['FIREBASE_APP_ID'] ?? $_SERVER['FIREBASE_APP_ID'] ?? ''; ?>"
    };

    // Inicializar una app SECUNDARIA de Firebase para que no cierre la sesión del administrador
    const secondaryApp = initializeApp(firebaseConfig, "SecondaryApp");
    const secondaryAuth = getAuth(secondaryApp);

    document.getElementById('registerForm').addEventListener('submit', async (e) => {
        e.preventDefault();
        
        const name = document.getElementById('regName').value;
        const email = document.getElementById('regEmail').value;
        const telefono = document.getElementById('regTelefono').value;
        const password = document.getElementById('regPassword').value;
        const role = document.getElementById('regRole').value;
        const btn = document.getElementById('btnRegister');
        const msg = document.getElementById('regMsg');
        
        btn.disabled = true;
        btn.innerText = 'Registrando en Firebase...';
        msg.style.display = 'none';

        try {
            // Crear usuario en Firebase Auth sin alterar la sesión principal
            const userCredential = await createUserWithEmailAndPassword(secondaryAuth, email, password);
            const firebaseUid = userCredential.user.uid;
            
            // Cerrar sesión silenciosamente en la app secundaria por limpieza
            await secondaryAuth.signOut();

            btn.innerText = 'Guardando en MySQL...';

            // Enviar los datos a nuestro backend PHP para guardarlo en la base de datos
            const response = await fetch('<?php echo $baseUrl; ?>/api/admin/usuarios', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    uid: firebaseUid,
                    email: email,
                    name: name,
                    telefono: telefono,
                    role: role
                })
            });

            const data = await response.json();
            
            if (data.success) {
                msg.style.display = 'block';
                msg.style.color = 'green';
                msg.innerText = '¡Usuario registrado con éxito en Firebase y MySQL!';
                document.getElementById('registerForm').reset();
            } else {
                throw new Error(data.error || 'Error al guardar en BD');
            }
        } catch (error) {
            console.error(error);
            msg.style.display = 'block';
            msg.style.color = 'red';
            msg.innerText = 'Error: ' + error.message;
        } finally {
            btn.disabled = false;
            btn.innerText = 'Registrar Usuario';
        }
    });
</script>

<?php 
$content = ob_get_clean();
require __DIR__ . '/../layouts/main.php'; 
?>
