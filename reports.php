<?php
require_once 'config/database.php';
require_once 'config/session.php';

requireLogin();

$db = new Database();
$conn = $db->getConnection();

$selectedMonth = $_GET['month'] ?? date('Y-m');
$reportType = $_GET['type'] ?? 'summary';

$prices = $conn->query("SELECT * FROM meal_prices LIMIT 1")->fetch_assoc();

if ($reportType === 'summary') {
    $query = "SELECT
        s.student_id,
        s.name,
        s.department,
        s.room_number,
        s.phone,
        mf.breakfast_count,
        mf.lunch_count,
        mf.dinner_count,
        mf.total_fee,
        mf.payment_status,
        mf.payment_date
        FROM students s
        LEFT JOIN mess_fees mf ON s.student_id = mf.student_id AND mf.month = ?
        ORDER BY s.department, s.name";

    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $selectedMonth);
    $stmt->execute();
    $reportData = $stmt->get_result();
    $stmt->close();
} elseif ($reportType === 'attendance') {
    $firstDay = $selectedMonth . '-01';
    $lastDay = date('Y-m-t', strtotime($firstDay));

    $students = $conn->query("SELECT student_id, name, department, room_number FROM students ORDER BY name")->fetch_all(MYSQLI_ASSOC);

    $attendanceData = [];
    foreach ($students as $student) {
        $stmt = $conn->prepare("SELECT date, breakfast, lunch, dinner FROM mess_attendance WHERE student_id = ? AND date BETWEEN ? AND ? ORDER BY date");
        $stmt->bind_param("iss", $student['student_id'], $firstDay, $lastDay);
        $stmt->execute();
        $result = $stmt->get_result();
        $attendance = [];
        $totalMealsServiced = 0;
        while ($row = $result->fetch_assoc()) {
            $attendance[$row['date']] = $row;
        }
        $stmt->close();
        $attendanceData[$student['student_id']] = [
            'student' => $student,
            'attendance' => $attendance
        ];
    }
}

$totalCollected = $conn->query("SELECT SUM(total_fee) as total FROM mess_fees WHERE month = '$selectedMonth' AND payment_status = 'paid'")->fetch_assoc()['total'] ?? 0;
$totalPending = $conn->query("SELECT SUM(total_fee) as total FROM mess_fees WHERE month = '$selectedMonth' AND payment_status = 'unpaid'")->fetch_assoc()['total'] ?? 0;
$totalStudents = $conn->query("SELECT COUNT(*) as count FROM students")->fetch_assoc()['count'];

$db->close();
?>
<?php include 'includes/header.php'; ?>
<style>
@media print {
    .no-print {
        display: none !important;
    }
    .print-container {
        box-shadow: none !important;
        border: none !important;
        margin: 0 !important;
        padding: 0 !important;
        width: 100% !important;
    }
    body {
        background-color: white !important;
    }
}
.a4-page {
    width: 210mm;
    min-height: 297mm;
    padding: 20mm;
    margin: auto;
    background: white;
    box-shadow: 0 0 10px rgba(0,0,0,0.1);
}
</style>
<?php include 'includes/sidebar.php'; ?>

<!-- Main Content Area -->
<div class="flex-1 flex flex-col h-full">

<!-- Header Action Bar -->
<header class="no-print sticky top-0 z-10 flex items-center justify-between px-8 py-4 bg-white/80 dark:bg-slate-900/80 backdrop-blur-md border-b border-slate-200 dark:border-slate-800">
<div>
<h2 class="text-lg font-bold text-slate-900 dark:text-slate-100">Print Preview</h2>
<p class="text-sm text-slate-500">Review the report layout before printing</p>
</div>

<div class="flex gap-4 items-center">
    <div class="flex gap-2">
        <input type="month" id="reportMonth" class="bg-white border-slate-300 rounded text-sm px-3 py-2" value="<?php echo $selectedMonth; ?>">
        <select id="reportType" class="bg-white border-slate-300 rounded text-sm px-3 py-2">
            <option value="summary" <?php echo $reportType === 'summary' ? 'selected' : ''; ?>>Fee Summary</option>
            <option value="attendance" <?php echo $reportType === 'attendance' ? 'selected' : ''; ?>>Attendance Details</option>
        </select>
        <button class="px-4 py-2 bg-slate-100 hover:bg-slate-200 border border-slate-300 rounded-lg text-sm font-medium transition-colors" onclick="loadReport()">Load Report</button>
    </div>
    <div class="w-px h-8 bg-slate-200 dark:bg-slate-700 mx-2"></div>
    <button class="flex items-center gap-2 px-6 py-2 bg-[#E67E22] text-white rounded-lg text-sm font-bold shadow-lg hover:brightness-110 transition-all" onclick="window.print()">
    <span class="material-symbols-outlined text-[20px]">print</span>
        Print Report
    </button>
</div>
</header>
<!-- A4 Document Container -->
<div class="flex-1 p-12 overflow-y-auto flex justify-center bg-slate-100 dark:bg-slate-950 min-h-screen">
<div class="a4-page print-container text-black">
<!-- Report Header -->
<div class="flex justify-between items-start border-b-2 border-black pb-6 mb-8">
<div class="flex items-center gap-4">
<div class="w-16 h-16 bg-primary rounded flex items-center justify-center text-white font-bold text-3xl">
H
</div>
<div>
<h1 class="text-2xl font-bold uppercase tracking-tight">Smart Hostel</h1>
<p class="text-sm font-medium text-slate-700 italic">Excellence in Student Living</p>
<p class="text-xs text-slate-500 mt-1">Management System Generated Report</p>
</div>
</div>
<div class="text-right">
<h2 class="text-xl font-bold text-slate-800 uppercase">
    <?php echo $reportType === 'summary' ? 'Monthly Fee Report' : 'Monthly Attendance Report'; ?>
</h2>
<p class="text-sm font-semibold py-1 px-2 bg-slate-100 inline-block rounded mt-1"><?php echo date('F Y', strtotime($selectedMonth . '-01')); ?></p>
<p class="text-xs text-slate-500 mt-2">Generated: <?php echo date('M d, Y'); ?></p>
<p class="text-xs text-slate-500">By: <?php echo htmlspecialchars(getAdminUsername()); ?></p>
</div>
</div>

<?php if ($reportType === 'summary'): ?>
<!-- Executive Summary Section -->
<div class="mb-8">
<h3 class="text-sm font-bold uppercase tracking-wider border-b border-slate-300 pb-2 mb-4">Summary Statistics</h3>
<div class="grid grid-cols-4 gap-4">
<div class="p-4 border border-slate-200 rounded text-center">
<p class="text-[10px] text-slate-500 uppercase font-bold">Total Students</p>
<p class="text-2xl font-bold"><?php echo $totalStudents; ?></p>
</div>
<div class="p-4 border border-slate-200 rounded text-center items-center flex flex-col justify-center">
<p class="text-[10px] text-slate-500 uppercase font-bold">Meal Prices (B/L/D)</p>
<p class="text-sm font-bold mt-1">₹<?php echo $prices['breakfast_price']; ?> / ₹<?php echo $prices['lunch_price']; ?> / ₹<?php echo $prices['dinner_price']; ?></p>
</div>
<div class="p-4 border border-slate-200 rounded text-center">
<p class="text-[10px] text-slate-500 uppercase font-bold">Total Collected</p>
<p class="text-2xl font-bold">₹<?php echo number_format($totalCollected, 2); ?></p>
</div>
<div class="p-4 border border-slate-200 rounded text-center">
<p class="text-[10px] text-slate-500 uppercase font-bold">Total Pending</p>
<p class="text-2xl font-bold text-red-600">₹<?php echo number_format($totalPending, 2); ?></p>
</div>
</div>
</div>

<!-- Detailed Table Section -->
<div class="mb-8">
<h3 class="text-sm font-bold uppercase tracking-wider border-b border-slate-300 pb-2 mb-4">Student Billing Detail</h3>
<table class="w-full text-left text-sm border-collapse">
<thead>
<tr class="bg-slate-50 border-y border-slate-300">
<th class="py-3 px-2 font-bold text-[11px] uppercase">ID</th>
<th class="py-3 px-2 font-bold text-[11px] uppercase">Name</th>
<th class="py-3 px-2 font-bold text-[11px] uppercase">Room</th>
<th class="py-3 px-2 font-bold text-[11px] uppercase text-center">Meals (B/L/D)</th>
<th class="py-3 px-2 font-bold text-[11px] uppercase text-right">Total Bill</th>
<th class="py-3 px-2 font-bold text-[11px] uppercase text-center">Status</th>
</tr>
</thead>
<tbody class="divide-y divide-slate-100">
<?php
$grandTotal = 0;
while ($record = $reportData->fetch_assoc()):
    $grandTotal += $record['total_fee'] ?? 0;
?>
<tr>
<td class="py-3 px-2 text-xs"><?php echo htmlspecialchars($record['student_id']); ?></td>
<td class="py-3 px-2 font-medium text-xs"><?php echo htmlspecialchars($record['name']); ?></td>
<td class="py-3 px-2 text-xs"><?php echo htmlspecialchars($record['room_number']); ?></td>
<td class="py-3 px-2 text-center text-xs">
    <?php echo $record['breakfast_count'] ?? 0; ?>/<?php echo $record['lunch_count'] ?? 0; ?>/<?php echo $record['dinner_count'] ?? 0; ?>
</td>
<td class="py-3 px-2 text-right font-semibold text-xs">₹<?php echo number_format($record['total_fee'] ?? 0, 2); ?></td>
<td class="py-3 px-2 text-center text-xs">
    <?php if (empty($record['payment_status'])): ?>
        -
    <?php elseif ($record['payment_status'] === 'paid'): ?>
        Paid
    <?php else: ?>
        <span class="text-red-500">Unpaid</span>
    <?php endif; ?>
</td>
</tr>
<?php endwhile; ?>
</tbody>
<tfoot>
<tr class="border-t-2 border-slate-300 font-bold bg-slate-50">
<td class="py-3 px-2 text-right uppercase text-xs" colspan="4">Total Generated Invoice</td>
<td class="py-3 px-2 text-right text-sm">₹<?php echo number_format($grandTotal, 2); ?></td>
<td></td>
</tr>
</tfoot>
</table>
</div>

<?php elseif ($reportType === 'attendance'): ?>

<!-- Executive Summary Section -->
<div class="mb-8">
<h3 class="text-sm font-bold uppercase tracking-wider border-b border-slate-300 pb-2 mb-4">Summary Statistics</h3>
<div class="grid grid-cols-2 gap-4">
<div class="p-4 border border-slate-200 rounded text-center">
<p class="text-[10px] text-slate-500 uppercase font-bold">Total Students</p>
<p class="text-2xl font-bold"><?php echo $totalStudents; ?></p>
</div>
<div class="p-4 border border-slate-200 rounded text-center items-center flex flex-col justify-center">
<p class="text-[10px] text-slate-500 uppercase font-bold">Reporting Month</p>
<p class="text-xl font-bold mt-1"><?php echo date('M Y', strtotime($selectedMonth . '-01')); ?></p>
</div>
</div>
</div>

<!-- Detailed Table Section -->
<div class="mb-8">
<h3 class="text-sm font-bold uppercase tracking-wider border-b border-slate-300 pb-2 mb-4">Student Attendance Detail</h3>
<table class="w-full text-left text-sm border-collapse">
<thead>
<tr class="bg-slate-50 border-y border-slate-300">
<th class="py-3 px-2 font-bold text-[11px] uppercase">Name</th>
<th class="py-3 px-2 font-bold text-[11px] uppercase">Dept</th>
<th class="py-3 px-2 font-bold text-[11px] uppercase">Room</th>
<th class="py-3 px-2 font-bold text-[11px] uppercase text-center">Brkfast Days</th>
<th class="py-3 px-2 font-bold text-[11px] uppercase text-center">Lunch Days</th>
<th class="py-3 px-2 font-bold text-[11px] uppercase text-center">Dinner Days</th>
<th class="py-3 px-2 font-bold text-[11px] uppercase text-center">Total Days Present</th>
</tr>
</thead>
<tbody class="divide-y divide-slate-100">
<?php foreach ($attendanceData as $data): ?>
<?php
$student = $data['student'];
$attendance = $data['attendance'];
$breakfastDays = 0;
$lunchDays = 0;
$dinnerDays = 0;

foreach ($attendance as $record) {
    if ($record['breakfast']) $breakfastDays++;
    if ($record['lunch']) $lunchDays++;
    if ($record['dinner']) $dinnerDays++;
}
?>
<tr>
<td class="py-3 px-2 font-medium text-xs"><?php echo htmlspecialchars($student['name']); ?></td>
<td class="py-3 px-2 text-xs"><?php echo htmlspecialchars($student['department']); ?></td>
<td class="py-3 px-2 text-xs"><?php echo htmlspecialchars($student['room_number']); ?></td>
<td class="py-3 px-2 text-center text-xs"><?php echo $breakfastDays; ?></td>
<td class="py-3 px-2 text-center text-xs"><?php echo $lunchDays; ?></td>
<td class="py-3 px-2 text-center text-xs"><?php echo $dinnerDays; ?></td>
<td class="py-3 px-2 text-center font-bold text-xs"><?php echo count($attendance); ?></td>
</tr>
<?php endforeach; ?>
</tbody>
</table>
</div>

<?php endif; ?>

<!-- Verification Footer -->
<div class="mt-auto pt-12">
<div class="flex justify-between items-end">
<div class="w-48 border-t border-black pt-2 flex flex-col break-inside-avoid">
<p class="text-xs font-bold text-center uppercase">System Generated</p>
<p class="text-[10px] text-slate-500 text-center mt-1">Automatically Verified</p>
</div>
<div class="text-center break-inside-avoid">
<p class="text-[10px] text-slate-400">Smart Hostel Management System © <?php echo date('Y'); ?></p>
</div>
<div class="w-48 border-t border-black pt-2 break-inside-avoid">
<p class="text-xs font-bold text-center uppercase">Warden Approval</p>
<p class="text-[10px] text-slate-500 text-center mt-1">Signature &amp; Date</p>
</div>
</div>
</div>
</div>
</div>

<script>
    function loadReport() {
        const month = document.getElementById('reportMonth').value;
        const type = document.getElementById('reportType').value;
        window.location.href = 'reports.php?month=' + month + '&type=' + type;
    }
</script>

</div>
</main>
</div>
</body>
</html>
