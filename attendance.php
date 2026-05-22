<?php
require_once 'config/database.php';
require_once 'config/session.php';

requireLogin();

$db = new Database();
$conn = $db->getConnection();

$message = '';
$messageType = '';
$selectedDate = $_GET['date'] ?? date('Y-m-d');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $date = $_POST['date'] ?? date('Y-m-d');
    $attendanceData = $_POST['attendance'] ?? [];

    foreach ($attendanceData as $student_id => $meals) {
        $breakfast = isset($meals['breakfast']) ? 1 : 0;
        $lunch = isset($meals['lunch']) ? 1 : 0;
        $dinner = isset($meals['dinner']) ? 1 : 0;

        $stmt = $conn->prepare("INSERT INTO mess_attendance (student_id, date, breakfast, lunch, dinner) VALUES (?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE breakfast=?, lunch=?, dinner=?");
        $stmt->bind_param("isiiiiii", $student_id, $date, $breakfast, $lunch, $dinner, $breakfast, $lunch, $dinner);
        $stmt->execute();
        $stmt->close();
    }

    $message = 'Attendance saved successfully for ' . date('F d, Y', strtotime($date));
    $messageType = 'success';
    $selectedDate = $date;
}

$students = $conn->query("SELECT student_id, name, department, room_number FROM students ORDER BY name")->fetch_all(MYSQLI_ASSOC);

$attendanceRecords = [];
$stmt = $conn->prepare("SELECT student_id, breakfast, lunch, dinner FROM mess_attendance WHERE date = ?");
$stmt->bind_param("s", $selectedDate);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $attendanceRecords[$row['student_id']] = $row;
}
$stmt->close();

$db->close();
?>
<?php include 'includes/header.php'; ?>
<?php include 'includes/sidebar.php'; ?>

<!-- Main Content Area -->
<div class="flex-1 flex flex-col overflow-hidden">
<!-- Header -->
<header class="h-16 border-b border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 flex items-center justify-between px-8 z-10 w-full mb-6 mt-4">
<h2 class="text-lg font-bold">Daily Attendance</h2>
<div class="flex items-center gap-4">
    <button onclick="document.getElementById('attendanceForm').submit();" class="bg-primary hover:bg-primary/90 text-white px-5 py-2 rounded-lg text-sm font-bold flex items-center gap-2 transition-all shadow-sm">
        <span class="material-symbols-outlined text-base">save</span>
        Save Attendance
    </button>
</div>
</header>
<section class="px-8 bg-white dark:bg-slate-900/50 space-y-4 mb-4">
    <?php if ($message): ?>
        <div class="mb-4 <?php echo $messageType === 'success' ? 'bg-emerald-50 border-emerald-200 text-emerald-800' : 'bg-red-50 border-red-200 text-red-800'; ?> border px-4 py-3 rounded relative">
            <?php echo htmlspecialchars($message); ?>
        </div>
    <?php endif; ?>
    <div class="flex flex-wrap items-end justify-between gap-6 pb-4 border-b border-slate-200">
        <div class="flex flex-wrap gap-6 items-center w-full">
            <div class="space-y-1.5 w-full">
               <label class="text-xs font-bold text-slate-500 uppercase tracking-wider">Attendance Date</label>
               <input type="date" name="displayDate" id="attendanceDate" class="w-full pl-4 pr-4 py-2.5 rounded-lg border border-primary/10 bg-background-light/50 focus:ring-2 focus:ring-primary/50 focus:border-primary outline-none text-sm transition-all" value="<?php echo $selectedDate; ?>" max="<?php echo date('Y-m-d'); ?>" required>
            </div>
            <div class="flex gap-2 w-full mt-2">
                <button type="button" class="px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg text-sm transition-colors border" onclick="selectAll('breakfast')">All Breakfast</button>
                <button type="button" class="px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg text-sm transition-colors border" onclick="selectAll('lunch')">All Lunch</button>
                <button type="button" class="px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg text-sm transition-colors border" onclick="selectAll('dinner')">All Dinner</button>
                <button type="button" class="px-3 py-1.5 bg-red-50 hover:bg-red-100 text-red-600 rounded-lg text-sm transition-colors border border-red-200" onclick="clearAll()">Clear All</button>
            </div>
        </div>
    </div>
</section>

<!-- Students List -->
<section class="flex-1 overflow-auto px-8 pb-8 bg-background-light dark:bg-background-dark w-full">
<form method="POST" id="attendanceForm">
<input type="hidden" name="date" id="formDate" value="<?php echo $selectedDate; ?>">
<div class="grid grid-cols-1 gap-4">
<?php foreach ($students as $student): ?>
<?php
$attendance = $attendanceRecords[$student['student_id']] ?? null;
$breakfastChecked = $attendance && $attendance['breakfast'] ? 'checked' : '';
$lunchChecked = $attendance && $attendance['lunch'] ? 'checked' : '';
$dinnerChecked = $attendance && $attendance['dinner'] ? 'checked' : '';
?>
<div class="bg-white dark:bg-slate-800 p-4 rounded-xl border border-slate-200 dark:border-slate-700 flex items-center gap-6 shadow-sm hover:shadow-md transition-shadow">
<div class="flex items-center gap-4 w-72">
<div class="size-12 rounded-full bg-slate-100 dark:bg-slate-700 flex items-center justify-center overflow-hidden border border-slate-200 dark:border-slate-600 font-bold text-gray-500">
<?php echo strtoupper(substr($student['name'], 0, 2)); ?>
</div>
<div>
<h3 class="font-bold text-sm text-slate-800"><?php echo htmlspecialchars($student['name']); ?></h3>
<p class="text-xs text-slate-500"><?php echo htmlspecialchars($student['department']); ?> • Room <?php echo htmlspecialchars($student['room_number']); ?></p>
</div>
</div>

<div class="flex-1 flex justify-around">
<div class="flex flex-col items-center gap-2">
<span class="text-[10px] font-bold text-slate-400 uppercase">Breakfast</span>
<label class="relative inline-flex items-center cursor-pointer">
<input type="checkbox" name="attendance[<?php echo $student['student_id']; ?>][breakfast]" class="sr-only peer breakfast-check" <?php echo $breakfastChecked; ?>>
<div class="w-14 h-7 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-7 peer-checked:after:border-white after:content-[''] after:absolute after:top-0.5 after:left-[4px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-6 after:w-6 after:transition-all peer-checked:bg-emerald-500"></div>
</label>
</div>

<div class="flex flex-col items-center gap-2">
<span class="text-[10px] font-bold text-slate-400 uppercase">Lunch</span>
<label class="relative inline-flex items-center cursor-pointer">
<input type="checkbox" name="attendance[<?php echo $student['student_id']; ?>][lunch]" class="sr-only peer lunch-check" <?php echo $lunchChecked; ?>>
<div class="w-14 h-7 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-7 peer-checked:after:border-white after:content-[''] after:absolute after:top-0.5 after:left-[4px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-6 after:w-6 after:transition-all peer-checked:bg-emerald-500"></div>
</label>
</div>

<div class="flex flex-col items-center gap-2">
<span class="text-[10px] font-bold text-slate-400 uppercase">Dinner</span>
<label class="relative inline-flex items-center cursor-pointer">
<input type="checkbox" name="attendance[<?php echo $student['student_id']; ?>][dinner]" class="sr-only peer dinner-check" <?php echo $dinnerChecked; ?>>
<div class="w-14 h-7 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-7 peer-checked:after:border-white after:content-[''] after:absolute after:top-0.5 after:left-[4px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-6 after:w-6 after:transition-all peer-checked:bg-emerald-500"></div>
</label>
</div>
</div>
</div>
<?php endforeach; ?>
</div>
</form>
</section>

<script>
    function loadAttendance() {
        const date = document.getElementById('attendanceDate').value;
        window.location.href = 'attendance.php?date=' + date;
    }

    function selectAll(meal) {
        const checkboxes = document.querySelectorAll('.' + meal + '-check');
        checkboxes.forEach(cb => cb.checked = true);
    }

    function clearAll() {
        const checkboxes = document.querySelectorAll('input[type="checkbox"]');
        checkboxes.forEach(cb => cb.checked = false);
    }

    document.getElementById('attendanceDate').addEventListener('change', function() {
        if (confirm('Do you want to load attendance for this date? Unsaved changes will be lost.')) {
            loadAttendance();
        } else {
            this.value = document.getElementById('formDate').value;
        }
    });
</script>

</main>
</div>
</body>
</html>
