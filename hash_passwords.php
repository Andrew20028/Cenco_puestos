<?php
// Mostrar errores para debug
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Incluir conexión
require_once 'Conexion.php';

// Seleccionar todos los usuarios
$sql = "SELECT id, Contraseña FROM usuarios";
$result = $conn->query($sql);

if ($result && $result->num_rows > 0) {
    $updated = 0;
    while ($row = $result->fetch_assoc()) {
        $id = $row['id'];
        $currentPass = $row['Contraseña'];

        // Verificar si ya está hasheada (bcrypt comienza con $2y$ o $2a$)
        if (preg_match('/^\$2[ayb]\$/', $currentPass)) {
            continue; // Ya está hasheada, la saltamos
        }

        // Hashear contraseña
        $hashed = password_hash($currentPass, PASSWORD_DEFAULT);

        // Actualizar en la base de datos
        $update = $conn->prepare("UPDATE usuarios SET Contraseña = ? WHERE id = ?");
        $update->bind_param("si", $hashed, $id);
        $update->execute();
        $updated++;
    }

    echo "✅ Contraseñas actualizadas: $updated";
} else {
    echo "No se encontraron usuarios.";
}

$conn->close();
?>
