<?php
// config.php Database configuration and connection setup
define('DBHOST', 'localhost:3307'); 
define('DBNAME', 'student_class_scheduler'); 
define('DBUSER', 'root'); 
define('DBPASS', ''); 

// Establish database connection
$con = mysqli_connect(DBHOST, DBUSER, DBPASS, DBNAME);
if (!$con) {
    die("Connection failed: " . mysqli_connect_error());
}
?>