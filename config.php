<?php
/* Attempt to connect to MySQL database */
$link = mysqli_connect('localhost', 'root', 'root', 'donna_rafinha');

// Check connection
if($link === false){
    die("ERROR: Não foi possível conectar no banco. " . mysqli_connect_error());
}
?>