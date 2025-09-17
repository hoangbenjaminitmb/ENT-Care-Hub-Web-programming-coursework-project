<?php
    $servername = "servername";
    $username = "username";
    $password = "password";
    $dbname = "databasename";

    $conn = new mysqli($servername, $username, $password, $dbname);

    if ($conn->connect_error) {
        die("Error: " . $conn->connect_error);
    }
?>
