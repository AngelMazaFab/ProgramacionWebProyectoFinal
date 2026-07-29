# Scripts de Provisión para MediControl

Esta carpeta contiene los scripts e instrucciones necesarias para preparar el entorno de base de datos MySQL y el proyecto de Firebase Authentication.

**IMPORTANTE:** Estos pasos deben ser ejecutados por el equipo responsable de la infraestructura (nosotros). No incluya contraseñas reales ni claves aquí.

## 1. Configuración de Base de Datos MySQL

1. Abra su cliente MySQL preferido (por ejemplo, phpMyAdmin, MySQL Workbench o la línea de comandos de MySQL).
2. Asegúrese de que el servidor MySQL esté en ejecución.
3. Ejecute el script `schema.sql` que se encuentra en esta misma carpeta:
   - Puede copiar y pegar su contenido, o importarlo directamente.
   - El script creará la base de datos `medicontrol_db` y las 5 tablas relacionales respetando la integridad referencial.
4. Antes de probar, cree un archivo `.env` en la raíz de su proyecto (copiando `.env.example`) y configure sus credenciales locales de MySQL.
5. Verifique la conexión usando el script `check_db.php`.
   ```bash
   php Scripts/check_db.php
   ```

## 2. Configuración de Firebase Authentication

1. Vaya a la [Consola de Firebase](https://console.firebase.google.com/).
2. Haga clic en **Agregar proyecto** (Add project) y asigne el nombre "MediControl".
3. Deshabilite Google Analytics (no es necesario para este proyecto).
4. Una vez creado el proyecto, vaya a **Compilación > Authentication**.
5. Haga clic en **Comenzar** y habilite el proveedor de acceso de **Correo electrónico/Contraseña** (Email/Password).
6. Registre su aplicación web en Firebase (ícono de la web `</>` en la vista general del proyecto).
7. Copie la configuración (configuración de SDK).
8. Copie esos valores (API Key, Auth Domain, Project ID, etc.) al archivo `.env` en la raíz de nuestro proyecto basándose en `.env.example`.

**NOTA:** Ninguna credencial real de su servidor o de Firebase debe subirse al repositorio. Mantenga los valores reales exclusivamente en su archivo `.env` local.
