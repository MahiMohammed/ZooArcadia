<?php
$servername = "mysql:host=localhost";
$username = "root";
$password = "";
$dbname = "dbname=zoo_arcadia";

try {
    $pdo = new PDO("$servername;$dbname", $username, $password);
    echo"connection réussie"."<br>";
    foreach( $pdo->query('Select nom FROM services', PDO::FETCH_ASSOC) as $row )
    {
        echo $row['nom']. '<br>';
    }

} catch (PDOException $e) { echo"". $e->getMessage();}