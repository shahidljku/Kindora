<?php
$conn = mysqli_connect("localhost", "root", "", "kindora");

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}
?>
