<?php
// sharedfunctions.php Shared utility functions for database operations and class management

// Input sanitization for safe output display
function sanitizeInput($data) {
    return htmlspecialchars(trim(stripslashes($data)), ENT_QUOTES, 'UTF-8');
}

// Handles page redirects
function redirectTo($location) {
    header("Location: $location");
    exit;
}

// Database query helpers
function queryOne($con, $sql, $params = [], $types = "") {
    $stmt = mysqli_prepare($con, $sql);
    if ($params) mysqli_stmt_bind_param($stmt, $types, ...$params);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);
    return $row;
}

function queryCount($con, $sql, $params = [], $types = "") {
    $stmt = mysqli_prepare($con, $sql);
    if ($params) mysqli_stmt_bind_param($stmt, $types, ...$params);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_bind_result($stmt, $count);
    mysqli_stmt_fetch($stmt);
    mysqli_stmt_close($stmt);
    return (int)$count;
}

function executeQuery($con, $sql, $params = [], $types = "") {
    $stmt = mysqli_prepare($con, $sql);
    if ($params) mysqli_stmt_bind_param($stmt, $types, ...$params);
    $result = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    return $result;
}

// Class capacity and enrollment management
function getClassSlots($con, $classID) {
    return queryCount($con, "SELECT slots FROM tblclasses WHERE cid = ?", [$classID], "i");
}

function getEnrolledCount($con, $classID) {
    return queryCount($con, "SELECT COUNT(*) FROM tblenrollment WHERE classID = ? AND status = 'enrolled'", [$classID], "i");
}

function getWaitlistCount($con, $classID) {
    return queryCount($con, "SELECT COUNT(*) FROM tblenrollment WHERE classID = ? AND status = 'waitlist'", [$classID], "i");
}

function getStudentStatus($con, $studentID, $classID) {
    $row = queryOne($con, "SELECT status FROM tblenrollment WHERE studentID = ? AND classID = ?", [$studentID, $classID], "ii");
    return $row ? $row['status'] : null;
}

function getSlotsLeft($con, $classID) {
    return max(0, getClassSlots($con, $classID) - getEnrolledCount($con, $classID));
}

// Maintains correct waitlist position numbering
function fixWaitlistPositions($con, $classID) {
    $stmt = mysqli_prepare($con, "SELECT eid FROM tblenrollment WHERE classID = ? AND status = 'waitlist' ORDER BY created_at ASC");
    mysqli_stmt_bind_param($stmt, "i", $classID);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    $position = 1;
    while ($row = mysqli_fetch_assoc($result)) {
        executeQuery($con, "UPDATE tblenrollment SET waitlist_position = ? WHERE eid = ?", [$position, $row['eid']], "ii");
        $position++;
    }
    mysqli_stmt_close($stmt);
}

// Promotes next waitlisted student when slot becomes available
function promoteNextWaitlistedStudent($con, $classID) {
    $row = queryOne($con, "SELECT studentID FROM tblenrollment WHERE classID = ? AND status = 'waitlist' ORDER BY created_at ASC LIMIT 1", [$classID], "i");
    if ($row) {
        executeQuery($con, "UPDATE tblenrollment SET status = 'enrolled' WHERE studentID = ? AND classID = ?", [$row['studentID'], $classID], "ii");
        
        // Create notification for waitlist promotion
        $classInfo = queryOne($con, "SELECT className, dayOfWeek, semester FROM tblclasses WHERE cid = ?", [$classID], "i");
        if ($classInfo) {
            $message = "Great news! You've been enrolled in " . $classInfo['className'] . " (" . $classInfo['dayOfWeek'] . ", " . $classInfo['semester'] . ") from the waitlist.";
            executeQuery($con, "INSERT INTO tblnotifications (studentID, classID, message) VALUES (?, ?, ?)", 
                [$row['studentID'], $classID, $message], "iis");
        }
        
        return true;
    }
    return false;
}

// Removes student from class and handles waitlist promotion
function cancelStudentEnrollment($con, $studentID, $classID) {
    $currentStatus = getStudentStatus($con, $studentID, $classID);
    if (!$currentStatus) return false;
    
    executeQuery($con, "DELETE FROM tblenrollment WHERE studentID = ? AND classID = ?", [$studentID, $classID], "ii");
    
    if ($currentStatus === 'enrolled') {
        promoteNextWaitlistedStudent($con, $classID);
    }
    fixWaitlistPositions($con, $classID);
    return true;
}

// Enrolls student in class or adds to waitlist if full
function enrollStudent($con, $studentID, $classID) {
    $slotsLeft = getSlotsLeft($con, $classID);
    $status = $slotsLeft > 0 ? 'enrolled' : 'waitlist';
    
    if ($status === 'waitlist') {
        $maxPos = queryCount($con, "SELECT MAX(waitlist_position) FROM tblenrollment WHERE classID = ? AND status = 'waitlist'", [$classID], "i");
        $position = $maxPos + 1;
        return executeQuery($con, "INSERT INTO tblenrollment (studentID, classID, status, waitlist_position) VALUES (?, ?, ?, ?)", [$studentID, $classID, $status, $position], "iisi");
    } else {
        return executeQuery($con, "INSERT INTO tblenrollment (studentID, classID, status, waitlist_position) VALUES (?, ?, ?, NULL)", [$studentID, $classID, $status], "iis");
    }
}

// Validates proper email format
function validateEmail($email) {
    return filter_var(trim($email), FILTER_VALIDATE_EMAIL);
}

// Checks if email address is already registered
function emailExists($con, $email) {
    return queryCount($con, "SELECT COUNT(*) FROM tblStudent WHERE email = ?", [$email], "s") > 0;
}

// Validates password requirements
function validatePassword($password) {
    return strlen($password) >= 8 && 
           preg_match('/[A-Z]/', $password) && 
           preg_match('/[a-z]/', $password) && 
           preg_match('/\d/', $password) && 
           preg_match('/[^a-zA-Z\d]/', $password);
}

// Retrieves student's enrollments with optional semester filtering
function getStudentEnrollments($con, $studentID, $semester = null) {
    if ($semester) {
        $sql = "
            SELECT e.classID, c.className, c.dayOfWeek, c.semester, e.status, e.created_at,
                   (SELECT COUNT(*) FROM tblenrollment e2 
                    WHERE e2.classID = e.classID AND e2.status = 'waitlist' 
                    AND e2.created_at < e.created_at) + 1 AS waitlist_position
            FROM tblenrollment e
            JOIN tblclasses c ON e.classID = c.cid
            WHERE e.studentID = ? AND c.semester = ?
            ORDER BY c.semester, e.created_at ASC
        ";
        $stmt = mysqli_prepare($con, $sql);
        mysqli_stmt_bind_param($stmt, "is", $studentID, $semester);
    } else {
        $sql = "
            SELECT e.classID, c.className, c.dayOfWeek, c.semester, e.status, e.created_at,
                   (SELECT COUNT(*) FROM tblenrollment e2 
                    WHERE e2.classID = e.classID AND e2.status = 'waitlist' 
                    AND e2.created_at < e.created_at) + 1 AS waitlist_position
            FROM tblenrollment e
            JOIN tblclasses c ON e.classID = c.cid
            WHERE e.studentID = ?
            ORDER BY c.semester, e.created_at ASC
        ";
        $stmt = mysqli_prepare($con, $sql);
        mysqli_stmt_bind_param($stmt, "i", $studentID);
    }
    
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $enrollments = mysqli_fetch_all($result, MYSQLI_ASSOC);
    mysqli_stmt_close($stmt);
    return $enrollments;
}

// Checks if student is already enrolled in a specific class for semester
function isStudentEnrolledInClassForSemester($con, $studentID, $className, $semester) {
    return queryOne($con, "
        SELECT e.classID FROM tblenrollment e
        JOIN tblclasses c ON e.classID = c.cid
        WHERE e.studentID = ? AND c.className = ? AND c.semester = ?
    ", [$studentID, $className, $semester], "iss") !== null;
}

// Notification management functions
function getUnreadNotifications($con, $studentID) {
    $stmt = mysqli_prepare($con, "
        SELECT n.id, n.message, n.created_at, c.className
        FROM tblnotifications n 
        JOIN tblclasses c ON n.classID = c.cid 
        WHERE n.studentID = ? AND n.is_read = FALSE 
        ORDER BY n.created_at DESC
    ");
    mysqli_stmt_bind_param($stmt, "i", $studentID);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $notifications = mysqli_fetch_all($result, MYSQLI_ASSOC);
    mysqli_stmt_close($stmt);
    return $notifications;
}

function getUnreadNotificationCount($con, $studentID) {
    return queryCount($con, "SELECT COUNT(*) FROM tblnotifications WHERE studentID = ? AND is_read = FALSE", [$studentID], "i");
}

function markNotificationAsRead($con, $notificationId, $studentID) {
    return executeQuery($con, "UPDATE tblnotifications SET is_read = TRUE WHERE id = ? AND studentID = ?", 
        [$notificationId, $studentID], "ii");
}
?>