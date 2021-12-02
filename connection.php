<?php
$servername = "localhost:3306";
$database = "com231";
$username = "root";
$password = "root"; 
// Create connection
$conn = mysqli_connect($servername, $username, $password, $database);

return $conn;
?>