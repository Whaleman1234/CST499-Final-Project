<?php
// getclassinfo.php Returns class availability information as JSON
require_once 'requirelogon.php';
require_once 'config.php';
require_once 'sharedfunctions.php';

$cid = (int)$_GET['cid'];

header('Content-Type: application/json');
echo json_encode([
    'slotsLeft' => getSlotsLeft($con, $cid),
    'waitlistCount' => getWaitlistCount($con, $cid)
]);
?>