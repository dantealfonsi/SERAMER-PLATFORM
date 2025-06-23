<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: X-API-KEY, Origin, X-Requested-With, Content-Type, Accept, Access-Control-Request-Method');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE');
header('Allow: GET, POST, OPTIONS, PUT, DELETE');
header('Content-Type: application/json');
header("Content-Security-Policy: default-src 'self'; script-src 'self' https://apis.google.com");
header("X-Content-Type-Options: nosniff");
header("X-Frame-Options: DENY");
header("Strict-Transport-Security: max-age=31536000; includeSubDomains");

$method = $_SERVER['REQUEST_METHOD'];

date_default_timezone_set("UTC");

define('PGSQL_VERSION', '14.0'); // Define la versión de PostgreSQL que estás utilizando
define('HOST','localhost');
define('PORT', '5432');
define('DBNAME', 'postgres'); // Reemplaza con el nombre de tu base de datos
define('USER','postgres');     // Reemplaza con tu usuario de PostgreSQL
define('PASSWORD', 'a10882990'); // Reemplaza con tu contraseña de PostgreSQL

// Cadena de conexión
$conn_string = "host=".HOST." port=".PORT." dbname=".DBNAME." user=".USER." password=".PASSWORD."";

// Intentar la conexión
$dbconn = pg_connect($conn_string);

// Verificar si la conexión fue exitosa
if (!$dbconn) {
    echo json_encode(array('result' => false, 'order' => null, 'message' => 'Error conexion.!','error' => pg_last_error()));
    die();
} else {
    // Si la conexión es exitosa, puedes realizar consultas a la base de datos, 
    // NOTA: El return 'echo' es opcional y debe permanecer comentado! a menos que sea para pruebas    
    echo json_encode(array('result' => true, 'order' => null, 'message' => 'Conexion exitosa.!'));
}

// Métodos de comunicación con el front

if ($method == "OPTIONS") {
    die();
}

function getPgPDOConnection() {
    static $pdo = null; // Usamos 'static' para mantener la conexión abierta y reutilizarla
    if ($pdo === null) {
        $dsn = "pgsql:host=".HOST.";port=".PORT.";dbname=".DBNAME;
        try {
            $pdo = new PDO($dsn, USER, PASSWORD, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, // Lanza excepciones para errores
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC // Por defecto, devuelve arrays asociativos
            ]);
            // Para asegurar UTF-8 si tu base de datos no lo fuerza por defecto
            // $pdo->exec("SET NAMES 'UTF8'"); // Esto es más común en MySQL. PostgreSQL maneja UTF8 en DSN.
        } catch (PDOException $e) {
            // Manejo de errores más robusto: podrías loggear el error en un archivo
            // en lugar de mostrarlo directamente al usuario en producción.
            die("Failed to connect to Data: " . $e->getMessage());
        }
    }
    return $pdo;
}

function pg_sqlconector($consulta) {
    $pdo = getPgPDOConnection();
    try {
        // Para SELECT, INSERT, UPDATE, DELETE
        $resultado = $pdo->query($consulta);
        return $resultado; // Devuelve el objeto PDOStatement
    } catch (PDOException $e) {
        // Puedes loggear el error para depuración
        die("Error in query: " . $e->getMessage());
    }
}

function pg_row_sqlconector($consulta) {
    $row = array();
    try {
        $pdo = getPgPDOConnection();
        $stmt = $pdo->query($consulta);
        // fetch() con PDO::FETCH_ASSOC (establecido en getPgPDOConnection)
        // obtendrá la fila como un array asociativo.
        $row = $stmt->fetch();
    } catch (PDOException $e) {
        // En tu original, manejabas el error y salías. Aquí, lanzamos.
        // Si no quieres que detenga la ejecución, puedes return [] o loggear.
        echo "Refresh page, Failed to connect or query: " . $e->getMessage();
        exit(); // O return []; para manejar el error de otra manera.
    }
    return $row ? $row : []; // Asegura que siempre devuelve un array (vacío si no hay resultados)
}

function pg_array_sqlconector($consulta) {
    $obj = array();
    try {
        $pdo = getPgPDOConnection();
        $stmt = $pdo->query($consulta);
        // fetchAll() con PDO::FETCH_ASSOC (establecido en getPgPDOConnection)
        // obtendrá todas las filas como un array de arrays asociativos.
        $obj = $stmt->fetchAll();
    } catch (PDOException $e) {
        // Manejo de errores
        die("Error fetching all rows: " . $e->getMessage());
    }
    return $obj;
}

if ($method == "POST") {
    try {
        $jsonData = file_get_contents('php://input');
        $data = json_decode($jsonData, true);

        if (isset($data['example'])) {
            $obj = array('result' => false, 'order' => null);

            $monto = $data['monto'];
            $moneda = $data['moneda'];
            
            if (isset($order['orderId'])) {
                $obj['result'] = true;
                $obj['order'] = $order['orderId'];
            }
            echo json_encode($obj);
        }

    } catch (Exception $e) {
        $response = array('Error' => $e->getMessage());
        echo json_encode($response);
    }
}

if ($method == "GET") {
    
    if(isset($_GET['cerrarSesion'])){
        // Destruir todas las variables de sesión
        $_SESSION = array();
        
        // Si se desea destruir la sesión completamente, también se debe destruir la cookie de sesión
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        
        // Finalmente, destruir la sesión
        session_destroy();
        echo json_encode(array('result' => true, 'order' => null, 'message' => 'Cambios realizados con exito.!'));
    }
        
}
