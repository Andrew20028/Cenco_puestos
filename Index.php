<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();

// Incluir la función LDAP
require_once 'ldap_auth.php';

$username = isset($_SESSION['cached_username']) ? $_SESSION['cached_username'] : '';
$password = isset($_SESSION['cached_password']) ? $_SESSION['cached_password'] : '';
$remember = isset($_SESSION['cached_remember']) ? $_SESSION['cached_remember'] : '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuario = htmlspecialchars($_POST['usuario']);
    $contrasena = htmlspecialchars($_POST['password']);
    $remember = isset($_POST['remember']);

    // Guardar en caché (sesión)
    $_SESSION['cached_username'] = $usuario;
    $_SESSION['cached_password'] = $contrasena;
    $_SESSION['cached_remember'] = $remember;

    // Autenticar con LDAP
    $ldap_result = autenticarConAdGrupo($usuario, $contrasena);

    if ($ldap_result !== false && $ldap_result !== true) {
    // Autenticación exitosa, obtener detalles del usuario
    $user_entry = $ldap_result;

    // Determinar rol basado en memberOf
    $rol_id = 2; // Por defecto: usuario
    $rol_nombre = 'Usuario';
    if (isset($user_entry['memberof'])) {
        foreach ($user_entry['memberof'] as $group) {
            // Cambia 'CN=Admins,OU=Groups,DC=cencosud,DC=corp' por el grupo real de AD
            if (stripos($group, 'CN=Admins,OU=Groups,DC=cencosud,DC=corp') !== false) {
                $rol_id = 1; // Admin
                $rol_nombre = 'Admin';
                break;
            }
        }
    }

    // Guardar datos del usuario en la sesión
    $_SESSION['usuario_id'] = $user_entry['samaccountname'][0];
    $_SESSION['rol_id'] = $rol_id;
    $_SESSION['rol_nombre'] = $rol_nombre;
    // Guardar el nombre completo del usuario
    $_SESSION['nombre_usuario'] = isset($user_entry['displayname'][0]) ? $user_entry['displayname'][0] : $user_entry['samaccountname'][0];

    // Redirigir según rol
    switch ($rol_id) {
        case 1: // Admin
            header('Location: admin.php');
            exit;
        case 2: // Usuario
            header('Location: puestos.php');
            exit;
        default:
            $error = "Rol no reconocido.";
    }
} else {
    $error = "Usuario o contraseña incorrectos.";
}
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Puestos oficina - Iniciar Sesión</title> 
    <link rel="stylesheet" href="css/index.css">
</head>
<body>
    <div class="login-container">
        <h2>Puestos oficina</h2>
        <h3>Sistema de Gestión de puestos</h3>
        <div class="form-container">
            <form method="POST" action="">
                <h4>Iniciar Sesión</h4>
                <?php if ($error): ?>
                    <p style="color: red; background: #ffe6e6; padding: 10px; border-radius: 5px; font-size: 14px; margin: 10px 0;">
                        <?php echo htmlspecialchars($error); ?>
                    </p>
                <?php endif; ?>
                <div class="input-group">
                    <label>Usuario</label>
                    <input type="text" name="usuario" value="<?php echo htmlspecialchars($username); ?>" placeholder="Ingrese su usuario" required>
                </div>
                <div class="input-group">
                    <label>Contraseña</label>
                    <input type="password" name="password" placeholder="Ingrese su contraseña" required>
                    <a href="recuperar_contrasena.php" class="forgot-password">¿Olvidó contraseña?</a>
                </div>
                <div class="remember">
                    <input type="checkbox" name="remember" <?php echo $remember ? 'checked' : ''; ?>>
                    <label>Recordar mi sesión</label>
                </div>
                <button type="submit">Ingresar Sistema</button>
            </form>
        </div>
        <p>No tiene cuenta? <a href="con_admin.php">Contacte al administrador</a></p>
        <p>© 2025 Puestos oficina. Todos los derechos reservados a CENCOSUD.</p>
    </div>
</body>
</html>

<?php
// Limpiar caché manualmente
if (isset($_GET['clear_cache'])) {
    unset($_SESSION['cached_username']);
    unset($_SESSION['cached_remember']);
    header('Location: index.php');
    exit;
}

// Logout
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: index.php');
    exit;
}
?>