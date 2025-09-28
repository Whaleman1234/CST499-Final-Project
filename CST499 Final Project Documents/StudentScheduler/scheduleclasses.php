<?php
// scheduleclasses.php Handles class enrollment and day swapping for multiple courses
require_once 'config.php';
require_once 'requirelogon.php';
require_once 'sharedfunctions.php';

$error = '';
$message = $_GET['msg'] ?? '';

// Handle multi-class enrollment
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['enroll_selected'])) {
    $selectedClasses = json_decode($_POST['selected_classes_json'], true);
    
    if (!$selectedClasses || count($selectedClasses) === 0) {
        $error = "No classes selected to enroll.";
    } else {
        $selectedSemester = $_POST['semester'] ?? '';
        
        mysqli_begin_transaction($con);
        try {
            foreach ($selectedClasses as $classID) {
                $classID = (int)$classID;
                
                // Get class info for this classID
                $classInfo = queryOne($con, "SELECT className FROM tblclasses WHERE cid = ?", [$classID], "i");
                $className = $classInfo['className'];
                
                // Remove existing enrollment for same class name in the same semester
                $existingEnrollment = queryOne($con, "
                    SELECT e.classID FROM tblenrollment e
                    JOIN tblclasses c ON e.classID = c.cid
                    WHERE e.studentID = ? AND c.className = ? AND c.semester = ?
                ", [$studentID, $className, $selectedSemester], "iss");
                
                if ($existingEnrollment) {
                    cancelStudentEnrollment($con, $studentID, $existingEnrollment['classID']);
                }
                
                enrollStudent($con, $studentID, $classID);
            }
            
            mysqli_commit($con);
            redirectTo("scheduleclasses.php?msg=Enrollment processed successfully");
        } catch (Exception $e) {
            mysqli_rollback($con);
            $error = "Error during enrollment: " . $e->getMessage();
        }
    }
}



// Get semester selection
$semesters = ['Fall', 'Winter', 'Spring'];
$selectedSemester = $_GET['semester'] ?? '';

// Get current enrollments for this semester
$currentEnrollments = [];
if ($selectedSemester) {
    $enrollments = getStudentEnrollments($con, $studentID, $selectedSemester);
    foreach ($enrollments as $enrollment) {
        $currentEnrollments[$enrollment['className']] = $enrollment;
    }
}

// Get grouped classes for semester
$groupedClasses = [];
if ($selectedSemester) {
    $stmt = mysqli_prepare($con, "SELECT cid, className, dayOfWeek, slots FROM tblclasses WHERE semester = ? ORDER BY className, dayOfWeek");
    mysqli_stmt_bind_param($stmt, "s", $selectedSemester);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    while ($class = mysqli_fetch_assoc($result)) {
        $groupedClasses[$class['className']][] = $class;
    }
    mysqli_stmt_close($stmt);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<title>Schedule Classes</title>
<link rel="stylesheet" href="style.css" />
<style>
    .enrolled { color: green; }
    .waitlist { color: orange; }
    .swap-notice { color: #ff8c00; font-style: italic; font-size: 0.9em; }
</style>
</head>
<body>

<h1>Schedule Classes</h1>

<nav>
    <a href="welcome.php" class="btn">Dashboard</a>
    <a href="profile.php" class="btn">Profile</a>
    <a href="logout.php" class="btn">Logout</a>
    <a href="classes.php" class="btn">My Classes</a>
    <br></br>
</nav>

<!-- Semester Selection Dropdown -->
<form method="GET">
    <label for="semester">Select Semester:</label>
    <select name="semester" id="semester" required onchange="this.form.submit()">
        <option value="">-- Select --</option>
        <?php foreach ($semesters as $sem): ?>
            <option value="<?= $sem ?>" <?= strtolower($sem) === strtolower($selectedSemester) ? 'selected' : '' ?>>
                <?= $sem ?>
            </option>
        <?php endforeach; ?>
    </select>
</form>

<?php if ($error): ?>
    <p style="color:red"><?= sanitizeInput($error) ?></p>
<?php endif; ?>

<?php if ($message): ?>
    <p style="color:green"><?= sanitizeInput($message) ?></p>
<?php endif; ?>

<?php if ($selectedSemester): ?>

<input type="text" id="searchBox" placeholder="Search classes..." onkeyup="filterClasses()" />
<br><br>

<!-- Enrollment Table -->
<form id="enrollForm" method="POST" onsubmit="return submitEnrollment()">
    <input type="hidden" name="selected_classes_json" id="selected_classes_json" value="" />
    <input type="hidden" name="semester" value="<?= sanitizeInput($selectedSemester) ?>" />
    <input type="hidden" name="enroll_selected" value="1" />

    <table id="classesTable" class="schedule-table">
        <thead>
            <tr>
                <th>Class Name</th>
                <th>Day of Week</th>
                <th>Slots Left</th>
                <th>Waitlist Count</th>
                <th>Current Status</th>
                <th>Add/Swap</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($groupedClasses as $className => $classVariants): ?>
                <?php
                $userEnrollment = $currentEnrollments[$className] ?? null;
                $userStatus = $userEnrollment ? $userEnrollment['status'] : '-';
                $selectedDay = $userEnrollment ? $userEnrollment['dayOfWeek'] : '';
                
                // Get stats for first variant to populate initial display
                $firstVariant = $classVariants[0];
                $slotsLeft = getSlotsLeft($con, $firstVariant['cid']);
                $waitlistCount = getWaitlistCount($con, $firstVariant['cid']);
                
                // Check if there are alternative days available for swapping
                $hasSwapOptions = $userEnrollment && count($classVariants) > 1;
                ?>
                <tr class='classRow'>
                    <td><?= sanitizeInput($className) ?></td>
                    
                    <td>
                        <select onchange='updateDaySelection(this)' data-classname="<?= sanitizeInput($className) ?>">
                            <option value=''>-- Select Day --</option>
                            <?php foreach ($classVariants as $variant): ?>
                                <?php if ($selectedDay !== $variant['dayOfWeek']): ?>
                                    <option value='<?= $variant['cid'] ?>' data-day='<?= sanitizeInput($variant['dayOfWeek']) ?>'>
                                        <?= sanitizeInput($variant['dayOfWeek']) ?>
                                    </option>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </select>
                        <?php if ($userEnrollment): ?>
                            <div class="swap-notice">Currently enrolled: <?= sanitizeInput($selectedDay) ?></div>
                        <?php endif; ?>
                    </td>
                    
                    <!-- Show Slots/Waitlist Count -->
                    <td class='slotsLeft'><?= $slotsLeft ?></td>
                    <td class='waitlistCount'><?= $waitlistCount ?></td>
                    <td class='<?= $userStatus === 'enrolled' ? 'enrolled' : ($userStatus === 'waitlist' ? 'waitlist' : '') ?>'>
                        <?= ucfirst($userStatus) ?>
                        <?php if ($userStatus === 'waitlist' && isset($userEnrollment['waitlist_position'])): ?>
                            (Position: <?= $userEnrollment['waitlist_position'] ?>)
                        <?php endif; ?>
                    </td>
                    
                    <td>
                        <button type='button' onclick='addClassDropdown("<?= sanitizeInput($className) ?>")' 
                                id="btn-<?= sanitizeInput($className) ?>"
                                data-has-enrollment="<?= $userEnrollment ? 'true' : 'false' ?>"
                                data-has-swap-options="<?= $hasSwapOptions ? 'true' : 'false' ?>">
                            <?= $userEnrollment ? 'Swap Day' : 'Add' ?>
                        </button>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <!-- Confirm Enrollment -->
    <h3>Your Selection</h3>
    <ul id="selectionList"></ul>
    <button type="submit">Process Selected</button>
</form>

<script>    
/* Dynamic Class Selection Display */
let selectedClasses = {};

async function updateDaySelection(selectElement) {
    const selectedCid = selectElement.value;
    if (!selectedCid) return;

    const res = await fetch('getclassinfo.php?cid=' + selectedCid);
    if (!res.ok) return;
    const data = await res.json();

    const row = selectElement.closest('tr');
    row.querySelector('.slotsLeft').textContent = data.slotsLeft;
    row.querySelector('.waitlistCount').textContent = data.waitlistCount;
}

function addClassDropdown(className) {
    const select = document.querySelector("select[data-classname='" + className + "']");
    const selectedCid = select.value;
    const selectedDay = select.options[select.selectedIndex]?.text || '';
    const button = document.getElementById('btn-' + className);
    const hasEnrollment = button.getAttribute('data-has-enrollment') === 'true';

    if (!selectedCid) {
        if (hasEnrollment) {
            alert("Please select a day to swap to.");
        } else {
            alert("Please select a day first.");
        }
        return;
    }

    if (selectedClasses[selectedCid]) {
        alert("Class already selected.");
        return;
    }

    const actionType = hasEnrollment ? 'swap' : 'add';
    selectedClasses[selectedCid] = {
        className: className, 
        dayOfWeek: selectedDay, 
        actionType: actionType
    };
    renderSelection();
}

function removeClassFromSelection(cid) {
    delete selectedClasses[cid];
    renderSelection();
}

/* Show Selected */
function renderSelection() {
    const list = document.getElementById('selectionList');
    list.innerHTML = '';

    for (const cid in selectedClasses) {
        const item = selectedClasses[cid];
        const li = document.createElement('li');
        const actionText = item.actionType === 'swap' ? ' (Swap)' : '';
        li.textContent = item.className + " (" + item.dayOfWeek + ")" + actionText;
        const removeBtn = document.createElement('button');
        removeBtn.textContent = 'Remove';
        removeBtn.type = 'button';
        removeBtn.onclick = () => removeClassFromSelection(cid);
        li.appendChild(removeBtn);
        list.appendChild(li);
    }

    document.getElementById('selected_classes_json').value = JSON.stringify(Object.keys(selectedClasses));
}

function submitEnrollment() {
    if (Object.keys(selectedClasses).length === 0) {
        alert("No classes selected to enroll.");
        return false;
    }
    
    // Check if any swaps are being made
    const hasSwaps = Object.values(selectedClasses).some(item => item.actionType === 'swap');
    
    if (hasSwaps) {
        const swapCount = Object.values(selectedClasses).filter(item => item.actionType === 'swap').length;
        const addCount = Object.values(selectedClasses).filter(item => item.actionType === 'add').length;
        
        let message = "You are about to:\n";
        if (swapCount > 0) message += "- Swap " + swapCount + " class(es) to different days\n";
        if (addCount > 0) message += "- Add " + addCount + " new class(es)\n";
        message += "\nContinue?";
        
        return confirm(message);
    }
    
    return true;
}

/* Class Search */
function filterClasses() {
    const filter = document.getElementById('searchBox').value.toLowerCase();
    const rows = document.querySelectorAll('#classesTable tbody tr.classRow');

    rows.forEach(row => {
        const className = row.cells[0].textContent.toLowerCase();
        row.style.display = className.includes(filter) ? '' : 'none';
    });
}
</script>

<?php endif; ?>
</body>
</html>