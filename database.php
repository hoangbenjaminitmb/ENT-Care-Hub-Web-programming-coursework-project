<?php
    $servername = "placeholder";
    $username = "placeholder";
    $password = "placeholder";
    $dbname = "placeholder";

    $conn = new mysqli($servername, $username, $password, $dbname);

    if ($conn->connect_error) {
        die("Error: " . $conn->connect_error);
    }
?>
