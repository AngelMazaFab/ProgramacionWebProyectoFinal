<?php
// public/index.php - Front Controller

if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
    require_once __DIR__ . '/../vendor/autoload.php';
} else {
    // Basic PSR-4 autoloader fallback
    spl_autoload_register(function ($class) {
        $prefix = 'App\\';
        $base_dir = __DIR__ . '/../app/';
        $len = strlen($prefix);
        if (strncmp($prefix, $class, $len) !== 0) return;
        $relative_class = substr($class, $len);
        $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';
        if (file_exists($file)) require $file;
    });
}

use App\Core\Router;
use App\Core\Session;

// Iniciar sesión
Session::start();

// Cargar variables de entorno
if (file_exists(__DIR__ . '/../.env')) {
    if (class_exists('Dotenv\Dotenv')) {
        $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
        $dotenv->safeLoad();
    }
    // Asegurar que las variables estén en $_ENV, $_SERVER y getenv()
    $lines = file(__DIR__ . '/../.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $line = trim($line);
        if (empty($line) || strpos($line, '#') === 0) continue;
        if (strpos($line, '=') !== false) {
            list($name, $value) = explode('=', $line, 2);
            $name = trim($name);
            $value = trim($value);
            $_ENV[$name] = $value;
            $_SERVER[$name] = $value;
            putenv("$name=$value");
        }
    }
}

// Inicializar el Router
$router = new Router();

// Definir rutas básicas
$router->get('/', 'AuthController@showLogin');
$router->get('/login', 'AuthController@showLogin');
$router->post('/api/login', 'AuthController@login');
$router->post('/api/logout', 'AuthController@logout');

// Rutas para Citas y Pacientes (Módulo 2)
$router->get('/citas', 'CitaController@index');
$router->get('/citas/nueva', 'CitaController@create');
$router->post('/citas/store', 'CitaController@store');
$router->post('/citas/update', 'CitaController@update');
$router->post('/citas/cancelar', 'CitaController@cancel');
$router->get('/pacientes', 'CitaController@pacientes');

// Rutas para Consultas y Recetas (Módulo 3)
$router->get('/consultas/atender', 'ConsultaController@atender');
$router->post('/consultas/store', 'ConsultaController@store');
$router->post('/recetas/store', 'RecetaController@store');
$router->post('/recetas/delete', 'RecetaController@delete');

// Rutas para Estudios (RF08, RF09) y Canvas (RF10, RF11)
$router->post('/estudios/upload', 'EstudioController@upload');
$router->get('/estudios/download', 'EstudioController@download');
$router->post('/consultas/canvas', 'ConsultaController@guardarCanvas');
$router->get('/consultas/ver-canvas', 'ConsultaController@viewCanvas');

// Rutas para Comentarios (Módulo 5)
$router->get('/comentarios', 'ComentarioController@index');
$router->get('/comentarios/nuevo', 'ComentarioController@create');
$router->post('/comentarios/store', 'ComentarioController@store');

// Rutas de Dashboard y Reportes (Módulo 6)
$router->get('/dashboard', 'DashboardController@index');
$router->get('/api/metrics', 'DashboardController@metrics');
$router->get('/dashboard/reporte', 'DashboardController@reporte');
$router->get('/receta/pdf', 'DashboardController@receta');

// Rutas de Administrador
$router->get('/admin/usuarios/nuevo', 'AdminController@createUsuario');
$router->post('/api/admin/usuarios', 'AdminController@storeUsuario');

// Despachar la petición
$router->dispatch($_SERVER['REQUEST_URI'], $_SERVER['REQUEST_METHOD']);
