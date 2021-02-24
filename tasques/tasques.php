<?php
/*
Plugin Name: Llista de Tasques
Description: Plugin que llista tasques, i permet administrar-les.
Version: 1.0
Author: Marc Peral
Author URI: https://github.com/mperalsapa
*/
add_action('admin_menu', 'test_plugin_setup_menu');
 
function test_plugin_setup_menu(){
    add_menu_page( 'Administracio de tasques', 'Tasques', 'manage_options', 'plugin_tasques', 'init_tasques' );
}
 
function init_tasques(){
	# Importem la informacio de connexio de 
    # la base de dades que fa servir la instal·lacio de wordpress
    require_once(ABSPATH . 'wp-config.php');
    $connection = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD);
    mysqli_select_db($connection, DB_NAME);
    
    # Comprovem si existeix la taula que farem servir
    # per la llista de tasques
    $query = "SHOW TABLES LIKE 'wp_tasques'";
    $resultat = mysqli_fetch_all(mysqli_query($connection,$query));
    
    # Comparem el resultat de la consulta de taules
    # i si es buida, vol dir que no existeix
    if ($resultat == array()) 
    {
    	# Si no existex la taula, la crearem
    	$query = "CREATE TABLE `wp_p1_webs`.`wp_tasques` ( `id_tasca` INT NOT NULL AUTO_INCREMENT , `nom_tasca` VARCHAR(250) NOT NULL , PRIMARY KEY (`id_tasca`)) ENGINE = InnoDB;";
    	mysqli_query($connection,$query);
    	echo "<h1>Creant base de dades</h1>";
    	echo "<h2>Refrescant pagina en 2 segons...</h2>";
    	echo("<meta http-equiv='refresh' content='2'>");
    } else {
        # Primer de tot, comprovem si aquesta carrega es 
	    # deguda a una modificacio de la llista
	    if (isset($_POST['afegir'])){
		    $query = "INSERT INTO `wp_tasques` (`nom_tasca`) VALUES ('".$_POST['nom_tasca']."')";
		    mysqli_query($connection, $query);
        }
        if (isset($_POST['esborrar'])){
        	$query = "DELETE FROM `wp_tasques` WHERE `wp_tasques`.`id_tasca` = ".$_POST['esborrar'];
        	mysqli_query($connection, $query);
        }

    	# Agafem la informacio de les tasques
    	$query = "SELECT * FROM wp_tasques";
    	$tasques = mysqli_fetch_all(mysqli_query($connection,$query));

    	# Generacio del HTML
        echo "<h1>Afegeix tasques</h1>";
    	echo "<form method='POST' action=''>";
        echo "<input type='text' id='nom_tasca' name='nom_tasca' required value=''>";
        echo "<input type='submit' name='afegir' value='AFEGIR' class='button'>";
        echo "</form>";
        echo "<h1>Tasques per fer</h1>";
        echo "<table style='table-layout: fixed;'>";
        foreach ($tasques as $tasca) {
        	echo "<tr>";
        	echo "<td style='width:352px; word-break:break-all;'>$tasca[1]</td>";
        	echo "<td >";
            echo "
            <form action='' method='post'>
            <button type='submit' name='esborrar' value='".$tasca[0]."' class='button'>✔</button>
            </form>";
        	echo "</td>";
        	echo "</tr>";    	
        }
        echo "</table>";
    }
}
?>