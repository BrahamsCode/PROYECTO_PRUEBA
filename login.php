<?php
// login.php - API con MySQL

// Configurar headers para permitir CORS y JSON
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Manejar peticiones OPTIONS (preflight)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// ============ CONFIGURACIÓN DE BASE DE DATOS ============
$host = 'localhost';
$dbname = 'login_db';
$db_user = 'root';
$db_pass = ''; // Por defecto XAMPP no tiene contraseña

// Conectar a MySQL
try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $db_user, $db_pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error de conexión a la base de datos',
        'error' => $e->getMessage(),
        'instrucciones' => 'Verifica que MySQL esté corriendo y que la base de datos "login_db" exista'
    ]);
    exit();
}

// ============ MANEJO DE GET ============
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    
    // Si no hay parámetros, mostrar info de la API
    if (empty($_GET)) {
        http_response_code(200);
        echo json_encode([
            'api' => 'Login API con MySQL',
            'version' => '2.0',
            'database' => 'Conectado',
            'endpoints' => [
                'POST /login.php' => 'Login con JSON body',
                'GET /login.php?username=X&password=Y' => 'Login con parámetros URL'
            ],
            'nota' => 'Las contraseñas están hasheadas con password_hash()'
        ]);
        exit();
    }
    
    // Obtener datos de los parámetros GET
    $username = isset($_GET['username']) ? trim($_GET['username']) : '';
    $password = isset($_GET['password']) ? trim($_GET['password']) : '';
    
    // Validar que se recibieron los datos
    if (empty($username) || empty($password)) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Faltan parámetros: username y password',
            'ejemplo' => 'login.php?username=admin&password=1234'
        ]);
        exit();
    }
    
    // Buscar usuario en la base de datos
    try {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = :username");
        $stmt->execute(['username' => $username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Verificar si el usuario existe y la contraseña es correcta
        if ($user && password_verify($password, $user['password'])) {
            http_response_code(200);
            echo json_encode([
                'success' => true,
                'message' => 'Login exitoso (GET)',
                'user' => [
                    'id' => $user['id'],
                    'username' => $user['username'],
                    'name' => $user['name'],
                    'email' => $user['email'],
                    'role' => $user['role']
                ],
                'timestamp' => date('Y-m-d H:i:s')
            ]);
        } else {
            http_response_code(401);
            echo json_encode([
                'success' => false,
                'message' => 'Usuario o contraseña incorrectos',
                'timestamp' => date('Y-m-d H:i:s')
            ]);
        }
    } catch(PDOException $e) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Error al consultar la base de datos',
            'error' => $e->getMessage()
        ]);
    }
    exit();
}

// ============ MANEJO DE POST ============
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // Obtener datos del request
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);

    // Validar que se recibieron los datos
    if (!isset($data['username']) || !isset($data['password'])) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Faltan campos requeridos: username y password'
        ]);
        exit();
    }

    $username = trim($data['username']);
    $password = trim($data['password']);

    // Validar que no estén vacíos
    if (empty($username) || empty($password)) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Usuario y contraseña no pueden estar vacíos'
        ]);
        exit();
    }

    // Buscar usuario en la base de datos
    try {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = :username");
        $stmt->execute(['username' => $username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Verificar si el usuario existe y la contraseña es correcta
        if ($user && password_verify($password, $user['password'])) {
            http_response_code(200);
            echo json_encode([
                'success' => true,
                'message' => 'Login exitoso (POST)',
                'user' => [
                    'id' => $user['id'],
                    'username' => $user['username'],
                    'name' => $user['name'],
                    'email' => $user['email'],
                    'role' => $user['role']
                ],
                'timestamp' => date('Y-m-d H:i:s')
            ]);
        } else {
            http_response_code(401);
            echo json_encode([
                'success' => false,
                'message' => 'Usuario o contraseña incorrectos',
                'timestamp' => date('Y-m-d H:i:s')
            ]);
        }
    } catch(PDOException $e) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Error al consultar la base de datos',
            'error' => $e->getMessage()
        ]);
    }
    exit();
}

// Método no permitido
http_response_code(405);
echo json_encode([
    'success' => false,
    'message' => 'Método no permitido. Usa GET o POST.'
]);
?>