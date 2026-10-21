<?php
$conn = mysqli_connect("localhost", "root", "", "event_db");
if (!$conn) {
    die("Database connection failed: " . mysqli_connect_error());
}