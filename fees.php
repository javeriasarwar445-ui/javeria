<?php
require_once 'config/database.php';
require_once 'config/session.php';

requireLogin();

$db = new Database();
$conn = $db->getConnection();

$message = '';
$messageType = '';
$selectedMonth = $_GET['month'] ?? date('Y-m');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'calculate') {
        $month = $_POST['month'] ?? date('Y-m');

        $prices = $conn->query("SELECT * FROM meal_prices LIMIT 1")->fetch_assoc();
        $breakfastPrice = $prices['breakfast_price'];
        $lunchPrice = $prices['lunch_price'];
        $dinnerPrice = $prices['dinner_price'];

        $students = $conn->query("SELECT student_id FROM students")->fetch_all(MYSQLI_ASSOC);

        foreach ($students as $student) {
            $student_id = $student['student_id'];

            $stmt = $conn->prepare("SELECT
                SUM(breakfast) as breakfast_count,
                SUM(lunch) as lunch_count,
                SUM(dinner) as dinner_count
                FROM mess_attendance
                WHERE student_id = ? AND DATE_FORMAT(date, '%Y-%m') = ?");
            $stmt->bind_param("is", $student_id, $month);
            $stmt->execute();
            $counts = $stmt->get_result()->fetch_assoc();
            $stmt->close();

            $breakfastCount = $counts['breakfast_count'] ?? 0;
            $lunchCount = $counts['lunch_count'] ?? 0;
            $dinnerCount = $counts['dinner_count'] ?? 0;

            $totalFee = ($breakfastCount * $breakfastPrice) + ($lunchCount * $lunchPrice) + ($dinnerCount * $dinnerPrice);

            $stmt = $conn->prepare("INSERT INTO mess_fees (student_id, month, breakfast_count, lunch_count, dinner_count, total_fee, payment_status)
                VALUES (?, ?, ?, ?, ?, ?, 'unpaid')
                ON DUPLICATE KEY UPDATE
                breakfast_count = ?, lunch_count = ?, dinner_count = ?, total_fee = ?");
            $stmt->bind_param("isiiidiiid", $student_id, $month, $breakfastCount, $lunchCount, $dinnerCount, $totalFee, $breakfastCount, $lunchCount, $dinnerCount, $totalFee);
            $stmt->execute();
            $stmt->close();
        }

        $message = 'Fees calculated successfully for ' . date('F Y', strtotime($month . '-01'));
        $messageType = 'success';
        $selectedMonth = $month;
    } elseif ($action === 'update_payment') {
        $fee_id = $_POST['fee_id'] ?? '';
        $payment_status = $_POST['payment_status'] ?? 'unpaid';
        $payment_date = $payment_status === 'paid' ? date('Y-m-d') : null;

        $stmt = $conn->prepare("UPDATE mess_fees SET payment_status = ?, payment_date = ? WHERE fee_id = ?");
        $stmt->bind_param("ssi", $payment_status, $payment_date, $fee_id);

        if ($stmt->execute()) {
            $message = 'Payment status updated successfully!';
            $messageType = 'success';
        }
        $stmt->close();
    }
}

$prices = $conn->query("SELECT * FROM meal_prices LIMIT 1")->fetch_assoc();

$query = "SELECT
    s.student_id,
    s.name,
    s.department,
    s.room_number,
    mf.fee_id,
    mf.breakfast_count,
    mf.lunch_count,
    mf.dinner_count,
    mf.total_fee,
    mf.payment_status,
    mf.payment_date
    FROM students s
    LEFT JOIN mess_fees mf ON s.student_id = mf.student_id AND mf.month = ?
    ORDER BY s.name";

$stmt = $conn->prepare($query);
$stmt->bind_param("s", $selectedMonth);
$stmt->execute();
$feeRecords = $stmt->get_result();
$stmt->close();

$totalCollected = $conn->query("SELECT SUM(total_fee) as total FROM mess_fees WHERE month = '$selectedMonth' AND payment_status = 'paid'")->fetch_assoc()['total'] ?? 0;
$totalPending = $conn->query("SELECT SUM(total_fee) as total FROM mess_fees WHERE month = '$selectedMonth' AND payment_status = 'unpaid'")->fetch_assoc()['total'] ?? 0;

$db->close();
?>
<?php include 'includes/header.php'; ?>
<?php include 'includes/sidebar.php'; ?>

<!-- Main Content Area -->
<div class="flex-1 flex flex-col overflow-hidden bg-background-light dark:bg-background-dark min-h-screen">

<div class="p-8 space-y-8">
<?php if ($message): ?>
    <div class="mb-4 <?php echo $messageType === 'success' ? 'bg-emerald-50 border-emerald-200 text-emerald-800' : 'bg-red-50 border-red-200 text-red-800'; ?> border px-4 py-3 rounded relative">
        <?php echo htmlspecialchars($message); ?>
    </div>
<?php endif; ?>

<!-- Month Selector -->
<div class="flex flex-col md:flex-row items-start md:items-end justify-between gap-4">
<div class="w-full max-w-xs">
<label class="block text-sm font-medium text-slate-600 dark:text-slate-400 mb-2">Select Month</label>
<div class="flex gap-2 w-full">
    <input type="month" id="selectedMonth" class="flex-1 bg-white dark:bg-slate-800 border-slate-200 dark:border-slate-700 rounded-lg py-2.5 px-4 text-sm focus:ring-primary focus:border-primary border" value="<?php echo $selectedMonth; ?>" max="<?php echo date('Y-m'); ?>">
    <button type="button" class="bg-slate-100 hover:bg-slate-200 text-slate-700 px-4 py-2 rounded-lg text-sm font-medium transition-colors border" onclick="loadMonth()">Load</button>
</div>
</div>
<div class="flex gap-2">
    <form method="POST" class="m-0">
        <input type="hidden" name="action" value="calculate">
        <input type="hidden" name="month" value="<?php echo $selectedMonth; ?>">
        <button type="submit" class="flex items-center gap-2 px-4 py-2.5 bg-primary text-white border-transparent rounded-lg text-sm font-medium hover:bg-primary/90 transition-all shadow-sm">
        <span class="material-symbols-outlined text-lg">sync</span>
            Calculate <?php echo date('M Y', strtotime($selectedMonth . '-01')); ?> Fees
        </button>
    </form>
</div>
</div>

<!-- Summary Cards -->
<div class="grid grid-cols-1 md:grid-cols-4 gap-6">
<div class="bg-white dark:bg-slate-900 p-6 rounded-xl shadow-sm border border-slate-100 dark:border-slate-800 flex items-center gap-4">
<div class="p-3 rounded-full bg-emerald-50 dark:bg-emerald-900/20 text-emerald-600 dark:text-emerald-400">
<span class="material-symbols-outlined text-3xl">payments</span>
</div>
<div>
<p class="text-sm font-medium text-slate-500 dark:text-slate-400">Total Collected</p>
<p class="text-2xl font-bold">₹<?php echo number_format($totalCollected, 2); ?></p>
</div>
</div>

<div class="bg-white dark:bg-slate-900 p-6 rounded-xl shadow-sm border border-slate-100 dark:border-slate-800 flex items-center gap-4">
<div class="p-3 rounded-full bg-rose-50 dark:bg-rose-900/20 text-rose-600 dark:text-rose-400">
<span class="material-symbols-outlined text-3xl">pending_actions</span>
</div>
<div>
<p class="text-sm font-medium text-slate-500 dark:text-slate-400">Pending Payments</p>
<p class="text-2xl font-bold">₹<?php echo number_format($totalPending, 2); ?></p>
</div>
</div>

<div class="bg-white dark:bg-slate-900 p-6 rounded-xl shadow-sm border border-slate-100 dark:border-slate-800 flex items-center gap-4">
<div class="p-3 rounded-full bg-blue-50 dark:bg-blue-900/20 text-blue-600 dark:text-blue-400">
<span class="material-symbols-outlined text-3xl">free_breakfast</span>
</div>
<div>
<p class="text-sm font-medium text-slate-500 dark:text-slate-400">Breakfast Price</p>
<p class="text-2xl font-bold">₹<?php echo number_format($prices['breakfast_price'], 2); ?></p>
</div>
</div>

<div class="bg-white dark:bg-slate-900 p-6 rounded-xl shadow-sm border border-slate-100 dark:border-slate-800 flex items-center gap-4">
<div class="p-3 rounded-full bg-amber-50 dark:bg-amber-900/20 text-amber-600 dark:text-amber-400">
<span class="material-symbols-outlined text-3xl">restaurant</span>
</div>
<div>
<p class="text-sm font-medium text-slate-500 dark:text-slate-400">Lunch/Dinner Price</p>
<p class="text-2xl font-bold">₹<?php echo number_format($prices['lunch_price'], 2); ?></p>
</div>
</div>
</div>

<!-- Students Table -->
<div class="bg-white dark:bg-slate-900 rounded-xl shadow-sm border border-slate-100 dark:border-slate-800 overflow-hidden">
<div class="px-6 py-4 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
<h3 class="font-bold text-lg">Detailed Student Fee Records</h3>
</div>
<div class="overflow-x-auto">
<table class="w-full text-left border-collapse">
<thead>
<tr class="bg-slate-50 dark:bg-slate-800/50">
<th class="px-6 py-4 text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Student Name</th>
<th class="px-6 py-4 text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Meal Counts (B/L/D)</th>
<th class="px-6 py-4 text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Total Fee</th>
<th class="px-6 py-4 text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Status</th>
<th class="px-6 py-4 text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider text-right">Actions</th>
</tr>
</thead>
<tbody class="divide-y divide-slate-100 dark:divide-slate-800">

<?php while ($record = $feeRecords->fetch_assoc()): ?>
<tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors">
<td class="px-6 py-4">
<div class="flex items-center gap-3">
<div class="size-8 rounded-full bg-primary/10 flex items-center justify-center text-primary font-bold text-xs">
<?php echo strtoupper(substr($record['name'], 0, 2)); ?>
</div>
<div>
    <span class="font-medium text-slate-900 dark:text-slate-100 block"><?php echo htmlspecialchars($record['name']); ?></span>
    <span class="text-xs text-slate-500"><?php echo htmlspecialchars($record['department']); ?> • Room <?php echo htmlspecialchars($record['room_number']); ?></span>
</div>
</div>
</td>
<td class="px-6 py-4">
<div class="flex gap-1">
<span class="px-2 py-0.5 bg-slate-100 dark:bg-slate-800 rounded text-xs"><?php echo $record['breakfast_count'] ?? 0; ?>B</span>
<span class="px-2 py-0.5 bg-slate-100 dark:bg-slate-800 rounded text-xs"><?php echo $record['lunch_count'] ?? 0; ?>L</span>
<span class="px-2 py-0.5 bg-slate-100 dark:bg-slate-800 rounded text-xs"><?php echo $record['dinner_count'] ?? 0; ?>D</span>
</div>
</td>
<td class="px-6 py-4 font-semibold text-slate-900 dark:text-slate-100">₹<?php echo number_format($record['total_fee'] ?? 0, 2); ?></td>
<td class="px-6 py-4">
    <?php if ($record['fee_id']): ?>
        <?php if ($record['payment_status'] === 'paid'): ?>
            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-emerald-100 dark:bg-emerald-900/30 text-emerald-800 dark:text-emerald-400 border border-emerald-200">
                <span class="w-1.5 h-1.5 rounded-full bg-emerald-600"></span>Paid
            </span>
        <?php else: ?>
            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-rose-100 dark:bg-rose-900/30 text-rose-800 dark:text-rose-400 border border-rose-200">
                <span class="w-1.5 h-1.5 rounded-full bg-rose-600"></span>Unpaid
            </span>
        <?php endif; ?>
    <?php else: ?>
        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-slate-100 dark:bg-slate-800 text-slate-500 border border-slate-200">
            <span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span>Not Calculated
        </span>
    <?php endif; ?>
</td>
<td class="px-6 py-4 text-right">
    <?php if ($record['fee_id']): ?>
        <?php if ($record['payment_status'] === 'unpaid'): ?>
            <form method="POST" class="m-0 inline">
                <input type="hidden" name="action" value="update_payment">
                <input type="hidden" name="fee_id" value="<?php echo $record['fee_id']; ?>">
                <input type="hidden" name="payment_status" value="paid">
                <button type="submit" class="bg-primary hover:bg-primary/90 text-white px-3 py-1.5 rounded-lg text-xs font-bold transition-all shadow-sm">
                    Mark as Paid
                </button>
            </form>
        <?php else: ?>
            <form method="POST" class="m-0 inline">
                <input type="hidden" name="action" value="update_payment">
                <input type="hidden" name="fee_id" value="<?php echo $record['fee_id']; ?>">
                <input type="hidden" name="payment_status" value="unpaid">
                <button type="submit" class="bg-white border border-slate-300 hover:bg-slate-50 text-slate-700 px-3 py-1.5 rounded-lg text-xs font-bold transition-all shadow-sm">
                    Mark Unpaid
                </button>
            </form>
        <?php endif; ?>
    <?php else: ?>
        <span class="text-slate-400 text-sm">-</span>
    <?php endif; ?>
</td>
</tr>
<?php endwhile; ?>

</tbody>
</table>
</div>
</div>
</div>

<script>
    function loadMonth() {
        const month = document.getElementById('selectedMonth').value;
        if (month) {
            window.location.href = 'fees.php?month=' + month;
        }
    }
</script>

</div>
</main>
</div>
</body>
</html>
