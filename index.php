<?php
require_once 'config/database.php';
require_once 'config/session.php';

requireLogin();

$db = new Database();
$conn = $db->getConnection();

$totalStudents = $conn->query("SELECT COUNT(*) as count FROM students")->fetch_assoc()['count'];
$totalDepartments = $conn->query("SELECT COUNT(DISTINCT department) as count FROM students")->fetch_assoc()['count'];

$currentMonth = date('Y-m');
$unpaidCount = $conn->query("SELECT COUNT(*) as count FROM mess_fees WHERE month = '$currentMonth' AND payment_status = 'unpaid'")->fetch_assoc()['count'];
$paidCount = $conn->query("SELECT COUNT(*) as count FROM mess_fees WHERE month = '$currentMonth' AND payment_status = 'paid'")->fetch_assoc()['count'];

$db->close();
?>
<?php include 'includes/header.php'; ?>
<?php include 'includes/sidebar.php'; ?>
<!-- Dashboard Content -->
<div class="p-8 space-y-8 flex-1">
<div class="flex flex-col gap-1">
<h2 class="text-2xl font-bold text-slate-900 dark:text-slate-100">Overview Analytics</h2>
<p class="text-slate-500">Welcome back, here's what's happening with your hostel today.</p>
</div>
<!-- Metric Cards -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
<!-- Total Students -->
<div class="bg-white dark:bg-slate-900 p-6 rounded-xl shadow-sm border border-slate-200 dark:border-slate-800 group hover:shadow-md transition-all">
<div class="flex items-start justify-between">
<div class="h-12 w-12 rounded-lg bg-primary/10 flex items-center justify-center text-primary">
<span class="material-symbols-outlined text-2xl">group</span>
</div>
</div>
<div class="mt-4">
<h3 class="text-slate-500 dark:text-slate-400 text-sm font-medium">Total Students</h3>
<p class="text-3xl font-bold mt-1 text-slate-900 dark:text-slate-100"><?php echo $totalStudents; ?></p>
</div>
</div>
<!-- Today's Attendance -->
<div class="bg-white dark:bg-slate-900 p-6 rounded-xl shadow-sm border border-slate-200 dark:border-slate-800 group hover:shadow-md transition-all">
<div class="flex items-start justify-between">
<div class="h-12 w-12 rounded-lg bg-indigo-100 dark:bg-indigo-900/30 flex items-center justify-center text-indigo-600">
<span class="material-symbols-outlined text-2xl">domain</span>
</div>
</div>
<div class="mt-4">
<h3 class="text-slate-500 dark:text-slate-400 text-sm font-medium">Departments</h3>
<p class="text-3xl font-bold mt-1 text-slate-900 dark:text-slate-100"><?php echo $totalDepartments; ?></p>
</div>
</div>
<!-- Pending Payments -->
<div class="bg-white dark:bg-slate-900 p-6 rounded-xl shadow-sm border border-slate-200 dark:border-slate-800 group hover:shadow-md transition-all">
<div class="flex items-start justify-between">
<div class="h-12 w-12 rounded-lg bg-amber-100 dark:bg-amber-900/30 flex items-center justify-center text-amber-600">
<span class="material-symbols-outlined text-2xl">pending_actions</span>
</div>
</div>
<div class="mt-4">
<h3 class="text-slate-500 dark:text-slate-400 text-sm font-medium">Unpaid This Month</h3>
<p class="text-3xl font-bold mt-1 text-slate-900 dark:text-slate-100"><?php echo $unpaidCount; ?></p>
</div>
</div>
<!-- Monthly Revenue -->
<div class="bg-white dark:bg-slate-900 p-6 rounded-xl shadow-sm border border-slate-200 dark:border-slate-800 group hover:shadow-md transition-all">
<div class="flex items-start justify-between">
<div class="h-12 w-12 rounded-lg bg-emerald-100 dark:bg-emerald-900/30 flex items-center justify-center text-emerald-600">
<span class="material-symbols-outlined text-2xl">payments</span>
</div>
</div>
<div class="mt-4">
<h3 class="text-slate-500 dark:text-slate-400 text-sm font-medium">Paid This Month</h3>
<p class="text-3xl font-bold mt-1 text-slate-900 dark:text-slate-100"><?php echo $paidCount; ?></p>
</div>
</div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
<div class="bg-white dark:bg-slate-900 p-6 rounded-xl shadow-sm border border-slate-200 dark:border-slate-800">
<h4 class="font-bold text-lg mb-4">Quick Links</h4>
<div class="space-y-3 flex flex-col items-start w-full">
<a href="students.php" class="w-full flex items-center gap-3 p-3 bg-slate-50 border border-slate-200 rounded text-sm hover:bg-slate-100">
<span class="material-symbols-outlined text-primary">group</span>
<span>Manage Students</span>
</a>
<a href="attendance.php" class="w-full flex items-center gap-3 p-3 bg-slate-50 border border-slate-200 rounded text-sm hover:bg-slate-100">
<span class="material-symbols-outlined text-primary">restaurant</span>
<span>Mark Attendance</span>
</a>
</div>
</div>
<div class="bg-white dark:bg-slate-900 p-6 rounded-xl shadow-sm border border-slate-200 dark:border-slate-800">
<h4 class="font-bold text-lg mb-4">More Actions</h4>
<div class="space-y-3 flex flex-col items-start w-full">
<a href="fees.php" class="w-full flex items-center gap-3 p-3 bg-slate-50 border border-slate-200 rounded text-sm hover:bg-slate-100">
<span class="material-symbols-outlined text-primary">account_balance_wallet</span>
<span>Manage Fees</span>
</a>
<a href="reports.php" class="w-full flex items-center gap-3 p-3 bg-slate-50 border border-slate-200 rounded text-sm hover:bg-slate-100">
<span class="material-symbols-outlined text-primary">analytics</span>
<span>Generate Reports</span>
</a>
</div>
</div>
</div>
</div>
</main>
</div>
</body>
</html>
