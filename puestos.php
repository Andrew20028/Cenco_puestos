<?php
session_start();

// Verificar si el usuario ha iniciado sesión
if (!isset($_SESSION['usuario_id'])) {
    header('Location: index.php');
    exit;
}

// Verificar si el usuario tiene el rol de "Usuario" (rol_id = 2)
if ($_SESSION['rol_id'] != 2) {
    header('Location: index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reservar Puesto</title>
    <link rel="stylesheet" href="/css/puestos.css">

    <!-- Flatpickr CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
</head>
<body>
<div class="logo">
    <!-- Sección izquierda: Logo y título -->
    <div class="logo-section">
        <img src="img/Logo.png" alt="Logo">
        <h1>SELECCIÓN DE PUESTOS</h1>
    </div>
    
    <!-- Sección central: Bienvenida -->
    <div class="welcome-section">
        <p>Bienvenido, <?php echo htmlspecialchars($_SESSION['nombre_usuario']); ?>!</p>
    </div>
    
    <!-- Sección derecha: Cerrar sesión -->
    <div class="logout-section">
        <a href="index.php?logout=true">Cerrar sesión</a>
    </div>
</div>

<div class="modal" id="modal">
    <h2>Reservar Puesto <span id="puesto-display"></span></h2>
    <form id="formulario" method="POST" action="registrar.php">
        <label>Nickname:</label>
        <input type="text" id="nickname" value="<?php echo htmlspecialchars($_SESSION['nombre_usuario']); ?>" readonly>
        <input type="hidden" id="puesto-id" name="puesto">

        <label>Fechas a ocupar:</label>
        <div id="calendario"></div> <!-- Calendario directamente visible -->
        <input type="hidden" id="fechas" name="fechas">

        <div class="modal-buttons">
            <button type="submit">Guardar</button>
            <button type="button" onclick="cerrarModal()">Cancelar</button>
        </div>
    </form>
</div>

<!-- Flatpickr JS -->
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<!-- Idioma español -->
<script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/es.js"></script>

<script>
    flatpickr("#calendario", {
        mode: "multiple",
        dateFormat: "Y-m-d",
        minDate: "today",
        locale: "es",
        inline: true, // Mostrar el calendario directamente
        onChange: function(selectedDates, dateStr, instance) {
            document.getElementById("fechas").value = dateStr;
        }
    });

    function cerrarModal() {
        document.getElementById("modal").style.display = "none";
    }
</script>

    
    <div class="modal-overlay" id="overlay"></div>
    <div class="table-container">
      <table>
        <tr>
          <td class="empty">
          <td class="empty">
          <td><button class="numero">86</button></td>
          <td><button class="numero">85</button></td>
          <td><button class="numero">84</button></td>
          <td class="principal">
            <img src="https://t3.ftcdn.net/jpg/02/08/38/52/360_F_208385214_9fdxiqpl87cv11H4KtABl5wjJt70NG5M.jpg" alt="Pasillo Principal" class="pasillo-img">
          </td>
          <td><button class="numero">83</button></td>
          <td><button class="numero">82</button></td>
          <td><button class="numero">81</button></td>
          <td><button class="numero">80</button></td>
          <td><button class="numero">79</button></td>
        </tr>

        <tr>
            <td class="empty"></td>
            <td class="empty"></td>
            <td class="empty"></td>
            <td class="empty"></td>
            <td class="empty"></td>
            <td class="principal">P</td>
            <td class="pasillo"> </td>
            <td class="pasillo"> </td>
            <td class="pasillo"> </td>
            <td class="pasillo"> </td>
            <td class="pasillo"> </td>
        </tr>

        <tr>
          <td class="empty"></td>
            <td class="empty"></td>
            <td class="empty"></td>
            <td class="empty"></td>
            <td class="empty"></td>
            <td class="principal">A</td>
            <td><button class="numero">74</button></td>
            <td><button class="numero">75</button></td>
            <td><button class="numero">76</button></td>
            <td><button class="numero">77</button></td>
            <td><button class="numero">78</button></td>
        </tr> 

        <tr> 
          <td class="empty"></td>
          <td class="empty"></td>
          <td class="empty"></td>
          <td class="empty"></td>
          <td class="empty"></td>
          <td class="principal">S</td>
          <td><button class="numero">73</button></td>
          <td><button class="numero">72</button></td>
          <td><button class="numero">71</button></td>
          <td><button class="numero">70</button></td>
          <td><button class="numero">69</button></td>
        </tr>

        <tr>
          <td class="empty"></td>
          <td class="empty"></td>
          <td class="empty"></td>
          <td class="empty"></td>
          <td class="empty"></td>
          <td class="principal">I</td>
          <td class="pasillo"> </td>
          <td class="pasillo"> </td>
          <td class="pasillo"> </td>
          <td class="pasillo"> </td>
          <td class="pasillo"> </td>
        </tr>

        <tr>
          <td class="empty"></td>
          <td class="empty"></td>
          <td class="empty"></td>
          <td class="empty"></td>
          <td class="empty"></td>
          <td class="principal">L</td>
          <td><button class="numero">64</button></td>
          <td><button class="numero">65</button></td>
          <td><button class="numero">66</button></td>
          <td><button class="numero">67</button></td>
          <td><button class="numero">68</button></td>
        </tr>

        <tr>
          <td class="empty"></td>
          <td class="empty"></td>
          <td class="empty"></td>
          <td class="empty"></td>
          <td class="empty"></td>
          <td class="principal">L</td>
          <td><button class="numero">63</button></td>
          <td><button class="numero">62</button></td>
          <td><button class="numero">61</button></td>
          <td><button class="numero">60</button></td>
          <td><button class="numero">59</button></td>
        </tr>


        <tr>
          <td class="empty"></td>
          <td class="empty"></td>
          <td class="empty"></td>
          <td class="empty"></td>
          <td class="empty"></td>
          <td class="principal">O</td>
          <td class="pasillo"> </td>
          <td class="pasillo"> </td>
          <td class="pasillo"> </td>
          <td class="pasillo"> </td>
          <td class="pasillo"> </td>
        </tr>

        <tr>
          <td class="empty"></td>
          <td class="empty"></td>
          <td class="empty"></td>
          <td class="empty"></td>
          <td class="empty"></td>
          <td class="principal"></td>
          <td><button class="numero">54</button></td>
          <td><button class="numero">55</button></td>
          <td><button class="numero">56</button></td>
          <td><button class="numero">57</button></td>
          <td><button class="numero">58</button></td>
        </tr>

        <tr>
          <td class="empty"></td>
          <td class="empty"></td>
          <td class="empty"></td>
          <td class="empty"></td>
          <td class="empty"></td>
          <td class="principal">P</td>
          <td><button class="numero">53</button></td>
          <td><button class="numero">52</button></td>
          <td><button class="numero">51</button></td>
          <td><button class="numero">50</button></td>
          <td><button class="numero">49</button></td>
        </tr>

        <tr>
          <td><button class="numero">23</button></td>
          <td><button class="numero">24</button></td>
          <td><button class="numero">25</button></td>
          <td><button class="numero">26</button></td>
          <td><button class="numero">27</button></td>
          <td class="principal">R</td>
          <td class="pasillo"> </td>
          <td class="pasillo"> </td>
          <td class="pasillo"> </td>
          <td class="pasillo"> </td>
          <td class="pasillo"> </td>
        </tr>

        <tr>
          <td class="pasillo"> </td>
          <td class="pasillo"> </td>
          <td class="pasillo"> </td>
          <td class="pasillo"> </td>
          <td class="pasillo"> </td>
          <td class="principal">I</td>
          <td><button class="numero">44</button></td>
          <td><button class="numero">45</button></td>
          <td><button class="numero">46</button></td>
          <td><button class="numero">47</button></td>
          <td><button class="numero">48</button></td>
        </tr>

        <tr>
          <td><button class="numero">22</button></td>
          <td><button class="numero">21</button></td>
          <td><button class="numero">20</button></td>
          <td><button class="numero">19</button></td>
          <td><button class="numero">18</button></td>
          <td class="principal">N</td>
          <td><button class="numero">43</button></td>
          <td><button class="numero">42</button></td>
          <td><button class="numero">41</button></td>
          <td><button class="numero">40</button></td>
          <td><button class="numero">39</button></td>
        </tr>

        <tr>
          <td><button class="numero">13</button></td>
          <td><button class="numero">14</button></td>
          <td><button class="numero">15</button></td>
          <td><button class="numero">16</button></td>
          <td><button class="numero">17</button></td>
          <td class="principal">C</td>
          <td class="pasillo"> </td>
          <td class="pasillo"> </td>
          <td class="pasillo"> </td>
          <td class="pasillo"> </td>
          <td class="pasillo"> </td>
        </tr>

        <tr>
          <td class="pasillo"> </td>
          <td class="pasillo"> </td>
          <td class="pasillo"> </td>
          <td class="pasillo"> </td>
          <td class="pasillo"> </td>
          <td class="principal">I</td>
          <td><button class="numero">34</button></td>
          <td><button class="numero">35</button></td>
          <td><button class="numero">36</button></td>
          <td><button class="numero">37</button></td>
          <td><button class="numero">38</button></td>
        </tr>
        
        <tr>
          <td><button class="numero">12</button></td>
          <td><button class="numero">11</button></td>
          <td><button class="numero">10</button></td>
          <td><button class="numero">9</button></td>
          <td><button class="numero">8</button></td>
          <td class="principal">P</td>
          <td><button class="numero">33</button></td>
          <td><button class="numero">32</button></td>
          <td><button class="numero">31</button></td>
          <td class="empty"></td>
          <td class="empty"></td>
        </tr>

        <tr>
          <td><button class="numero">3</button></td>
          <td><button class="numero">4</button></td>
          <td><button class="numero">5</button></td>
          <td><button class="numero">6</button></td>
          <td><button class="numero">7</button></td>
          <td class="principal">A</td>
          <td class="pasillo"> </td>
          <td class="pasillo"> </td>
          <td class="pasillo"> </td>
          <td class="empty"></td>
          <td class="empty"></td>
        </tr>

        <tr>
          <td class="pasillo"> </td>
          <td class="pasillo"> </td>
          <td class="pasillo"> </td>
          <td class="pasillo"> </td>
          <td class="pasillo"> </td>
          <td class="principal">L</td>
          <td><button class="numero">30</button></td>
          <td class="pasillo"> </td>
          <td class="pasillo"> </td>
          <td class="empty"></td>
          <td class="empty"></td>
        </tr>
        
        <tr>
          <td><button class="numero">2</button></td>
          <td class="pasillo"> </td>
          <td><button class="numero">1</button></td>
          <td class="pasillo"> </td>
          <td class="pasillo"> </td> 
          <td class="principal">
            <img src="https://www.cofan.es/images/content/500x331/no-hay-salida.jpg" alt="Pasillo Principal" class="pasillo-img">
          </td>
          <td><button class="numero">29</button></td>
          <td class="pasillo"> </td>
          <td><button class="numero">28</button></td>
          <td class="empty"></td>
          <td class="empty"></td>
          </tr>
      </table>
    </div>
    <script src="js/script.js"></script>
  </body>
</html>