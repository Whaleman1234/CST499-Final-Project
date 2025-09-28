<?php
// cancelclass.php Handles class cancellation for enrolled students
require_once 'requirelogon.php';
require_once 'config.php';
require_once 'sharedfunctions.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cancel_class_id'])) {
    $classID = (int) $_POST['cancel_class_id'];
    
    if (!cancelStudentEnrollment($con, $studentID, $classID)) {
        redirectTo("classes.php?error=not_enrolled");
    }
    
    redirectTo("classes.php?msg=cancelled");
} else {
    redirectTo("classes.php");
}
?>