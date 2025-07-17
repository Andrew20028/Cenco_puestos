<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include 'conexion.php';
session_start(); // Iniciar sesión para acceder a $_SESSION['nombre_usuario']

// Configurar headers para JSON
header('Content-Type: application/json');

// Verificar conexión
if ($conn->connect_error) {
    echo json_encode(["success" => false, "error" => "Conexión fallida: " . $conn->connect_error]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'register') {
        $puesto = isset($_POST['puesto']) ? intval($_POST['puesto']) : 0;
        $nickname = isset($_POST['nickname']) ? trim($_POST['nickname']) : '';
        $fechas_string = isset($_POST['fechas']) ? trim($_POST['fechas']) : '';

        // Validación mejorada
        if ($puesto <= 0 || empty($nickname) || empty($fechas_string)) {
            echo json_encode(["success" => false, "error" => "Datos inválidos"]);
            exit;
        }

        // Validar que el nickname coincida con el usuario de la sesión
        if ($nickname !== $_SESSION['nombre_usuario']) {
            echo json_encode(["success" => false, "error" => "No tienes permiso para hacer reservas con este nickname"]);
            exit;
        }

        $fechas_array = explode(',', $fechas_string);
        
        // Validar formato de fechas
        $fechas_validas = [];
        foreach ($fechas_array as $fecha) {
            $fecha = trim($fecha);
            if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha) && strtotime($fecha) !== false) {
                $fechas_validas[] = $fecha;
            }
        }

        if (empty($fechas_validas)) {
            echo json_encode(["success" => false, "error" => "No se encontraron fechas válidas"]);
            exit;
        }

        // Verificar reservas existentes del usuario
        $user_reserved_dates = [];
        $user_reserved_puestos = [];
        $stmt_user_check = $conn->prepare("SELECT puesto, fecha FROM reservas WHERE nickname = ?");
        $stmt_user_check->bind_param("s", $nickname);
        $stmt_user_check->execute();
        $result_user = $stmt_user_check->get_result();
        while ($row = $result_user->fetch_assoc()) {
            $user_reserved_dates[] = $row['fecha'];
            $user_reserved_puestos[$row['fecha']] = $row['puesto'];
        }
        $stmt_user_check->close();

        // Verificar conflictos
        $conflicting_dates = array_intersect($fechas_validas, $user_reserved_dates);
        if (!empty($conflicting_dates)) {
            $conflicting_message = [];
            foreach ($conflicting_dates as $date) {
                $puesto_existente = $user_reserved_puestos[$date];
                $conflicting_message[] = "el puesto $puesto_existente para la fecha $date";
            }
            echo json_encode(["success" => false, "error" => "Ya tienes una reserva en " . implode(", ", $conflicting_message)]);
            exit;
        }

        // Verificar disponibilidad del puesto
        $occupied_dates = [];
        $stmt_check = $conn->prepare("SELECT fecha FROM reservas WHERE puesto = ?");
        $stmt_check->bind_param("i", $puesto);
        $stmt_check->execute();
        $result = $stmt_check->get_result();
        while ($row = $result->fetch_assoc()) {
            $occupied_dates[] = $row['fecha'];
        }
        $stmt_check->close();

        $available_dates = array_filter($fechas_validas, function($fecha) use ($occupied_dates) {
            return !in_array($fecha, $occupied_dates);
        });

        if (empty($available_dates)) {
            echo json_encode(["success" => false, "error" => "Todas las fechas seleccionadas ya están ocupadas"]);
            exit;
        }

        // Insertar reservas
        $stmt_insert = $conn->prepare("INSERT INTO reservas (puesto, nickname, fecha) VALUES (?, ?, ?)");
        $success = true;
        $errors = [];
        $inserted_dates = [];
        
        foreach ($available_dates as $fecha) {
            $stmt_insert->bind_param("iss", $puesto, $nickname, $fecha);
            if ($stmt_insert->execute()) {
                $inserted_dates[] = $fecha;
            } else {
                $success = false;
                $errors[] = "Error al insertar fecha $fecha: " . $conn->error;
            }
        }
        $stmt_insert->close();

        if ($success) {
            echo json_encode([
                "success" => true, 
                "message" => "Reserva creada para las fechas: " . implode(", ", $inserted_dates),
                "fechas_reservadas" => $inserted_dates
            ]);
        } else {
            echo json_encode(["success" => false, "error" => implode("; ", $errors)]);
        }

    } elseif ($action === 'get_occupied_all') {
        $stmt = $conn->prepare("SELECT DISTINCT puesto, nickname FROM reservas ORDER BY puesto");
        $stmt->execute();
        $result = $stmt->get_result();
        $occupied = [];
        while ($row = $result->fetch_assoc()) {
            $occupied[] = [
                'puesto' => $row['puesto'],
                'nickname' => $row['nickname'],
                'ocupado' => true
            ];
        }
        echo json_encode($occupied);
        $stmt->close();
        
    } elseif ($action === 'get_occupied') {
        $fecha = $_POST['fecha'] ?? $_GET['fecha'] ?? date('Y-m-d');
        
        // Validar formato de fecha
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha) || strtotime($fecha) === false) {
            echo json_encode(["success" => false, "error" => "Formato de fecha inválido"]);
            exit;
        }

        $stmt = $conn->prepare("SELECT puesto, nickname FROM reservas WHERE fecha = ? ORDER BY puesto");
        $stmt->bind_param("s", $fecha);
        $stmt->execute();
        $result = $stmt->get_result();
        $occupied = [];
        while ($row = $result->fetch_assoc()) {
            $occupied[] = ['puesto' => $row['puesto'], 'nickname' => $row['nickname']];
        }
        echo json_encode($occupied);
        $stmt->close();
        
    } elseif ($action === 'get_puesto_info') {
        $puesto = isset($_POST['puesto']) ? intval($_POST['puesto']) : 0;
        if ($puesto <= 0) {
            echo json_encode(["success" => false, "error" => "Puesto inválido"]);
            exit;
        }
        
        $stmt = $conn->prepare("SELECT nickname, fecha FROM reservas WHERE puesto = ? ORDER BY fecha");
        $stmt->bind_param("i", $puesto);
        $stmt->execute();
        $result = $stmt->get_result();
        $reservas = [];
        $fechas_ocupadas = [];
        while ($row = $result->fetch_assoc()) {
            $reservas[] = [
                'nickname' => $row['nickname'],
                'fecha' => $row['fecha']
            ];
            $fechas_ocupadas[] = $row['fecha'];
        }
        $stmt->close();
        
        echo json_encode([
            "success" => true,
            "data" => [
                "reservas" => $reservas,
                "fechas_ocupadas" => $fechas_ocupadas
            ]
        ]);
        
    } elseif ($action === 'delete') {
        $puesto = isset($_POST['puesto']) ? intval($_POST['puesto']) : 0;
        $nickname = isset($_POST['nickname']) ? trim($_POST['nickname']) : '';
        
        if ($puesto <= 0 || empty($nickname)) {
            echo json_encode(["success" => false, "error" => "Datos inválidos"]);
            exit;
        }
        
        // Verificar sesión
        if (!isset($_SESSION['nombre_usuario'])) {
            echo json_encode(["success" => false, "error" => "Sesión no iniciada"]);
            exit;
        }
        
        if ($nickname !== $_SESSION['nombre_usuario']) {
            echo json_encode(["success" => false, "error" => "No tienes permiso para eliminar estas reservas"]);
            exit;
        }
        
        $delete_stmt = $conn->prepare("DELETE FROM reservas WHERE puesto = ? AND nickname = ?");
        $delete_stmt->bind_param("is", $puesto, $nickname);
        if ($delete_stmt->execute()) {
            if ($delete_stmt->affected_rows > 0) {
                echo json_encode(["success" => true, "message" => "Tus reservas fueron eliminadas"]);
            } else {
                echo json_encode(["success" => false, "error" => "No se encontraron reservas para este usuario en el puesto"]);
            }
        } else {
            echo json_encode(["success" => false, "error" => $conn->error]);
        }
        $delete_stmt->close();
        
    } elseif ($action === 'delete_date') {
        $puesto = isset($_POST['puesto']) ? intval($_POST['puesto']) : 0;
        $nickname = isset($_POST['nickname']) ? trim($_POST['nickname']) : '';
        $fecha = isset($_POST['fecha']) ? trim($_POST['fecha']) : '';

        if ($puesto <= 0 || empty($nickname) || empty($fecha)) {
            echo json_encode(["success" => false, "error" => "Datos inválidos"]);
            exit;
        }
        
        // Validar formato de fecha
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha) || strtotime($fecha) === false) {
            echo json_encode(["success" => false, "error" => "Formato de fecha inválido"]);
            exit;
        }
        
        // Verificar sesión
        if (!isset($_SESSION['nombre_usuario'])) {
            echo json_encode(["success" => false, "error" => "Sesión no iniciada"]);
            exit;
        }
        
        if ($nickname !== $_SESSION['nombre_usuario']) {
            echo json_encode(["success" => false, "error" => "No tienes permiso para eliminar esta reserva"]);
            exit;
        }

        $delete_stmt = $conn->prepare("DELETE FROM reservas WHERE puesto = ? AND nickname = ? AND fecha = ?");
        $delete_stmt->bind_param("iss", $puesto, $nickname, $fecha);
        if ($delete_stmt->execute()) {
            if ($delete_stmt->affected_rows > 0) {
                echo json_encode(["success" => true, "message" => "Fecha eliminada exitosamente"]);
            } else {
                echo json_encode(["success" => false, "error" => "No se encontró la reserva para esta fecha"]);
            }
        } else {
            echo json_encode(["success" => false, "error" => $conn->error]);
        }
        $delete_stmt->close();
        
    } else {
        echo json_encode(["success" => false, "error" => "Acción no válida"]);
    }
} else {
    echo json_encode(["success" => false, "error" => "Método no permitido"]);
}

$conn->close();
?>