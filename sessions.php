<?php
/*
Plugin Name: Sessions de Treball
Description: Plugin que llista sessions i mostra el total d'hores, i permet administrar-les.
Version: 1.0
Author: Marc Peral
Author URI: https://github.com/mperalsapa
*/
add_action('admin_menu', 'menu_sessions');
 
function menu_sessions(){
    add_menu_page( 'Administracio de sessions', 'Sessions', 'manage_options', 'plugin_sessions', 'init_sessions' );
}

function init_sessions(){
    # Importem la informacio de connexio de 
    # la base de dades que fa servir la instal·lacio de wordpress
    require_once(ABSPATH . 'wp-config.php');
    $connection = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD);
    mysqli_select_db($connection, DB_NAME);
    
    # Comprovem si l'usuari vol esborrar la base de dades
    if (isset($_POST['esborrar_bd'])){
        $query = "DROP TABLE wp_sessions";
        mysqli_query($connection, $query);
    }

    # Comprovem si existeix la taula que farem servir
    # per la llista de tasques
    $query = "SHOW TABLES LIKE 'wp_sessions'";
    $resultat = mysqli_fetch_all(mysqli_query($connection,$query));
    if ($resultat == array()) 
    {
        # Comprovem si l'usuari vol crear la base de dades
        if (isset($_POST['crear_bd'])){
            # Si no existex la taula, la crearem
            $query = "CREATE TABLE `wp_p1_webs`.`wp_sessions` ( `id_sessio` INT NOT NULL AUTO_INCREMENT , `inici_sessio` DATETIME NOT NULL , `fi_sessio` DATETIME NULL , PRIMARY KEY (`id_sessio`)) ENGINE = InnoDB";
            mysqli_query($connection,$query);
            echo "<h1>Creant base de dades</h1>";
            echo "<h2>Refrescant pagina en 2 segons...</h2>";
            echo("<meta http-equiv='refresh' content='2'>");
        } else {
            echo "<h1>Administracio de sessions</h1>";
            echo "<form method='POST' action=''>";
            echo "<input type='submit' name='crear_bd' value='CREAR BASE DE DADES' class='button'>";
            echo "</form>";
        }
    } else {
        # Primer de tot, comprovem si aquesta carrega es 
        # deguda a una modificacio de sessio
        if (isset($_POST['iniciar'])){
            $query = "INSERT INTO `wp_sessions` (`inici_sessio`, `fi_sessio`) VALUES (NOW(), NULL)";
            mysqli_query($connection, $query);
        }
        if (isset($_POST['acabar'])){
            # Primer agafem la sesio que no te data de fi
            $query = "SELECT * FROM wp_sessions WHERE fi_sessio IS NULL";
            $resultat = mysqli_fetch_all(mysqli_query($connection, $query));
            $id_sessio = $resultat[0][0];

            $query = "UPDATE wp_sessions SET fi_sessio = NOW() WHERE wp_sessions.id_sessio = $id_sessio";
            mysqli_query($connection, $query);
        }

        # Començem a printar el HTML
        echo "<h1>Administrcio de sessions</h1>";
        echo "<form method='POST' action=''>";

        # Comprovem si tenim una sessio activa
        $query = "SELECT * FROM wp_sessions WHERE fi_sessio IS NULL";
        $resultat = mysqli_fetch_all(mysqli_query($connection,$query));        
        
        # Si no tenim sesions mostrem el botó d'iniciar sesio
        if ($resultat == array()) {
            echo "<input type='submit' name='iniciar' value='INICIAR SESSIO' class='button'>";
        } else {
            echo "<input type='submit' name='acabar' value='ACABAR SESSIO' class='button'>";
        }
        echo "</form>";

        # Agafem la informacio de les sessions
        $query = "SELECT id_sessio, inici_sessio, fi_sessio, TIMESTAMPDIFF(MINUTE, inici_sessio, fi_sessio) AS 'Minuts' FROM wp_sessions";
        $sessions = mysqli_fetch_all(mysqli_query($connection,$query));
        $totalMinuts = 0;
        # Mostrem les sessions
        echo "<h1>sessions</h1>";
        echo "<table style='table-layout: fixed;'>";
        echo "<tr><td>Inici de sessio</td><td>Fi de sessio</td><td>Duracio de sessio (minuts)</td></tr>";
        foreach ($sessions as $sessio) {
            echo "<tr>";
            echo "<td style='width:150px; word-break:break-all;'>$sessio[1]</td>";
            echo "<td style='width:150px; word-break:break-all;'>$sessio[2]</td>";
            echo "<td style='width:150px; word-break:break-all;'>$sessio[3]</td>";
            echo "</tr>"; 
            $totalMinuts += $sessio[3];
        }
        echo "<tr><td>Temps Total</td><td></td><td>$totalMinuts</td></tr>";
        echo "</table>";
        mysqli_close($connection);
        
        # Boto per esborrar la base de dades
        echo "<br><br><br><br>";
        echo "<form method='POST' action=''>";
        echo "<input type='submit' name='esborrar_bd' value='ESBORRAR BASE DE DADES' class='button'>";
        echo "</form>";

    }
}
?>
