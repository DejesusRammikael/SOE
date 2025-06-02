<?php
$servername = "localhost";
$username = "root";       
$password = "";           
$dbname = "ezorderdb"; 


$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>