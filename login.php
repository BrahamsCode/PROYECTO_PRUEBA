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
$host = '127.0.0.1';
$port = '3306';
$dbname = 'login_db';
$db_user = 'root';
$db_pass = 'Root2020';

// Conectar a MySQL
try {
    $dsn = "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4";
    $pdo = new PDO($dsn, $db_user, $db_pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error de conexión a la base de datos',
        'error' => $e->getMessage(),
        'config' => [
            'host' => $host,
            'port' => $port,
            'database' => $dbname,
            'user' => $db_user
        ],
        'instrucciones' => [
            '1. Verifica que MySQL esté corriendo en el puerto ' . $port,
            '2. Crea la base de datos "login_db" en MySQL Workbench',
            '3. Verifica usuario y contraseña en este archivo PHP',
            '4. Asegúrate de que XAMPP pueda conectarse a MySQL Workbench'
        ]
    ]);
    exit();
}

// ============ MANEJO DE GET ============
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    
    // Si no hay parámetros, mostrar info de la API
    if (empty($_GET)) {
        http_response_code(200);
        echo json_encode([
            'api' => 'Login API con MySQL Workbench',
            'version' => '2.0',
            'database' => 'Conectado exitosamente',
            'config' => [
                'host' => $host,
                'port' => $port,
                'database' => $dbname
            ],
            'endpoints' => [
                'POST /login.php' => 'Login con JSON body',
                'GET /login.php?username=X&password=Y' => 'Login con parámetros URL'
            ],
            'nota' => 'Las contraseñas NO están hasheadas (solo para desarrollo)'
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
        
        // Verificar si el usuario existe y la contraseña es correcta (SIN HASH)
        if ($user && $password === $user['password']) {
            http_response_code(200);
            echo json_encode([
                'success' => true,
                'message' => 'Login exitoso',
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
        
        // Verificar si el usuario existe y la contraseña es correcta (SIN HASH)
        if ($user && $password === $user['password']) {
            http_response_code(200);
            echo json_encode([
                'success' => true,
                'message' => 'Login exitoso',
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