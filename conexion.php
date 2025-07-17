     <?php
     $host = 'localhost';
     $db = 'mapa_pasillos';
     $user = 'root';
     $pass = ''; // dejar vacío si usas XAMPP sin contraseña

     $conn = new mysqli($host, $user, $pass, $db);

     if ($conn->connect_error) {
         header('Content-Type: application/json');
         echo json_encode(["success" => false, "error" => "Conexión fallida: " . $conn->connect_error]);
         exit;
     }

     $conn->set_charset("utf8");
     ?>