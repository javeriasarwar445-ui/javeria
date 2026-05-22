<?php
require_once 'config/database.php';
require_once 'config/session.php';

requireLogin();

$db = new Database();
$conn = $db->getConnection();

$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'add') {
        $name = $_POST['name'] ?? '';
        $parent_name = $_POST['parent_name'] ?? '';
        $department = $_POST['department'] ?? '';
        $room_number = $_POST['room_number'] ?? '';
        $house_address = $_POST['house_address'] ?? '';
        $house_number = $_POST['house_number'] ?? '';
        $phone = $_POST['phone'] ?? '';
        $admission_date = $_POST['admission_date'] ?? '';

        $stmt = $conn->prepare("INSERT INTO students (name, parent_name, department, room_number, house_address, house_number, phone, admission_date) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssssss", $name, $parent_name, $department, $room_number, $house_address, $house_number, $phone, $admission_date);

        if ($stmt->execute()) {
            $message = 'Student added successfully!';
            $messageType = 'success';
        } else {
            $message = 'Error adding student.';
            $messageType = 'error';
        }
        $stmt->close();
    } elseif ($action === 'edit') {
        $student_id = $_POST['student_id'] ?? '';
        $name = $_POST['name'] ?? '';
        $parent_name = $_POST['parent_name'] ?? '';
        $department = $_POST['department'] ?? '';
        $room_number = $_POST['room_number'] ?? '';
        $house_address = $_POST['house_address'] ?? '';
        $house_number = $_POST['house_number'] ?? '';
        $phone = $_POST['phone'] ?? '';
        $admission_date = $_POST['admission_date'] ?? '';

        $stmt = $conn->prepare("UPDATE students SET name=?, parent_name=?, department=?, room_number=?, house_address=?, house_number=?, phone=?, admission_date=? WHERE student_id=?");
        $stmt->bind_param("ssssssssi", $name, $parent_name, $department, $room_number, $house_address, $house_number, $phone, $admission_date, $student_id);

        if ($stmt->execute()) {
            $message = 'Student updated successfully!';
            $messageType = 'success';
        } else {
            $message = 'Error updating student.';
            $messageType = 'error';
        }
        $stmt->close();
    } elseif ($action === 'delete') {
        $student_id = $_POST['student_id'] ?? '';

        $stmt = $conn->prepare("DELETE FROM students WHERE student_id=?");
        $stmt->bind_param("i", $student_id);

        if ($stmt->execute()) {
            $message = 'Student deleted successfully!';
            $messageType = 'success';
        } else {
            $message = 'Error deleting student.';
            $messageType = 'error';
        }
        $stmt->close();
    }
}

$search = $_GET['search'] ?? '';
$department_filter = $_GET['department'] ?? '';

$query = "SELECT * FROM students WHERE 1=1";
$params = [];
$types = '';

if ($search) {
    $query .= " AND (name LIKE ? OR student_id LIKE ? OR room_number LIKE ?)";
    $searchParam = "%$search%";
    $params[] = $searchParam;
    $params[] = $searchParam;
    $params[] = $searchParam;
    $types .= 'sss';
}

if ($department_filter) {
    if ($department_filter !== 'All Departments' && $department_filter !== '') {
        $query .= " AND department = ?";
        $params[] = $department_filter;
        $types .= 's';
    }
}

$query .= " ORDER BY name";

$stmt = $conn->prepare($query);
if ($params) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$students = $stmt->get_result();
$stmt->close();

$departments = $conn->query("SELECT DISTINCT department FROM students ORDER BY department")->fetch_all(MYSQLI_ASSOC);

$db->close();
?>
<?php include 'includes/header.php'; ?>
<?php include 'includes/sidebar.php'; ?>

<!-- Main Content Area -->
<div class="p-8 flex-1">
<!-- Header Section -->
<div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-8">
<div>
<h2 class="text-3xl font-black text-slate-900 dark:text-slate-100 tracking-tight">Student Management</h2>
<p class="text-slate-500 dark:text-slate-400 mt-1">Manage, filter and track student records across all departments.</p>
</div>
<button onclick="openAddModal()" class="bg-[#16A085] hover:bg-[#138d75] text-white px-6 py-2.5 rounded-lg font-bold text-sm shadow-lg shadow-[#16A085]/20 flex items-center gap-2 transition-all active:scale-95">
<span class="material-symbols-outlined text-lg">person_add</span>
    Add Student
</button>
</div>

<?php if ($message): ?>
<div class="mb-4 <?php echo $messageType === 'success' ? 'bg-emerald-50 border-emerald-200 text-emerald-800' : 'bg-red-50 border-red-200 text-red-800'; ?> border px-4 py-3 rounded relative">
    <?php echo htmlspecialchars($message); ?>
</div>
<?php endif; ?>

<!-- Filters & Search -->
<div class="bg-white dark:bg-background-dark p-4 rounded-xl shadow-sm border border-primary/5 mb-6">
<div class="flex flex-wrap gap-4">
<div class="flex-1 min-w-[300px] relative">
<span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-slate-400">search</span>
<input id="searchInput" class="w-full pl-10 pr-4 py-2.5 rounded-lg border border-primary/10 bg-background-light/50 focus:ring-2 focus:ring-accent/50 focus:border-accent outline-none text-sm transition-all" value="<?php echo htmlspecialchars($search); ?>" placeholder="Search students by name, ID, or room number..." type="text"/>
</div>
<div class="flex gap-3">
<div class="relative min-w-[180px]">
<select id="departmentFilter" class="w-full appearance-none pl-10 pr-10 py-2.5 rounded-lg border border-primary/10 bg-background-light/50 focus:ring-2 focus:ring-accent/50 focus:border-accent outline-none text-sm transition-all">
<option value="">All Departments</option>
<?php foreach ($departments as $dept): ?>
    <option value="<?php echo htmlspecialchars($dept['department']); ?>" <?php echo $department_filter === $dept['department'] ? 'selected' : ''; ?>>
        <?php echo htmlspecialchars($dept['department']); ?>
    </option>
<?php endforeach; ?>
</select>
<span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-slate-400">domain</span>
<span class="material-symbols-outlined absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none">expand_more</span>
</div>
<button onclick="applyFilters()" class="px-4 py-2.5 bg-primary/5 hover:bg-primary/10 text-primary rounded-lg font-medium text-sm flex items-center gap-2 transition-colors">
<span class="material-symbols-outlined">search</span> Search
</button>
<button onclick="clearFilters()" class="px-4 py-2.5 bg-primary/5 hover:bg-primary/10 text-primary rounded-lg font-medium text-sm flex items-center gap-2 transition-colors">
Clear
</button>
</div>
</div>
</div>
<!-- Data Table -->
<div class="bg-white dark:bg-background-dark rounded-xl shadow-sm border border-primary/5 overflow-hidden">
<div class="overflow-x-auto">
<table class="w-full text-left border-collapse">
<thead class="bg-primary/5 dark:bg-primary/20">
<tr>
<th class="px-6 py-4 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider border-b border-primary/10">ID</th>
<th class="px-6 py-4 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider border-b border-primary/10">Name</th>
<th class="px-6 py-4 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider border-b border-primary/10">Parent Name</th>
<th class="px-6 py-4 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider border-b border-primary/10">Department</th>
<th class="px-6 py-4 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider border-b border-primary/10">Room</th>
<th class="px-6 py-4 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider border-b border-primary/10">Phone</th>
<th class="px-6 py-4 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider border-b border-primary/10">Admit Date</th>
<th class="px-6 py-4 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider border-b border-primary/10 text-right">Actions</th>
</tr>
</thead>
<tbody class="divide-y divide-primary/5">
<?php while ($student = $students->fetch_assoc()): ?>
<tr class="table-row-striped hover:bg-primary/5 transition-colors group">
<td class="px-6 py-4 whitespace-nowrap text-sm text-slate-600 dark:text-slate-400"><?php echo htmlspecialchars($student['student_id']); ?></td>
<td class="px-6 py-4 whitespace-nowrap">
<div class="flex items-center gap-3">
<div class="w-8 h-8 rounded-full bg-primary/10 flex items-center justify-center text-primary font-bold text-xs"><?php echo strtoupper(substr($student['name'], 0, 2)); ?></div>
<span class="text-sm font-semibold text-slate-900 dark:text-slate-100"><?php echo htmlspecialchars($student['name']); ?></span>
</div>
</td>
<td class="px-6 py-4 whitespace-nowrap text-sm text-slate-600 dark:text-slate-400"><?php echo htmlspecialchars($student['parent_name']); ?></td>
<td class="px-6 py-4 whitespace-nowrap">
<span class="px-3 py-1 bg-primary/5 text-primary rounded-full text-xs font-medium border border-primary/10"><?php echo htmlspecialchars($student['department']); ?></span>
</td>
<td class="px-6 py-4 whitespace-nowrap text-sm font-mono text-slate-600 dark:text-slate-400"><?php echo htmlspecialchars($student['room_number']); ?></td>
<td class="px-6 py-4 whitespace-nowrap text-sm text-slate-600 dark:text-slate-400"><?php echo htmlspecialchars($student['phone']); ?></td>
<td class="px-6 py-4 whitespace-nowrap text-sm text-slate-600 dark:text-slate-400"><?php echo htmlspecialchars($student['admission_date']); ?></td>
<td class="px-6 py-4 whitespace-nowrap text-right">
<div class="flex justify-end gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
<button onclick='editStudent(<?php echo json_encode($student); ?>)' class="p-2 text-slate-500 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition-all" title="Edit Student">
<span class="material-symbols-outlined text-[20px]">edit</span>
</button>
<button onclick="deleteStudent(<?php echo $student['student_id']; ?>, '<?php echo htmlspecialchars(addslashes($student['name'])); ?>')" class="p-2 text-slate-500 hover:text-red-600 hover:bg-red-50 rounded-lg transition-all" title="Delete Student">
<span class="material-symbols-outlined text-[20px]">delete</span>
</button>
</div>
</td>
</tr>
<?php endwhile; ?>
</tbody>
</table>
</div>
</div>
</div>

<div id="studentModal" class="fixed inset-0 bg-black/50 hidden items-center justify-center z-50">
    <div class="bg-white rounded-xl shadow-lg w-full max-w-lg mx-4">
        <div class="flex items-center justify-between p-6 border-b border-gray-100">
            <h3 class="text-xl font-semibold" id="modalTitle">Add New Student</h3>
            <button onclick="closeModal()" class="text-gray-400 hover:text-gray-500 material-symbols-outlined">close</button>
        </div>
        <div class="p-6">
            <form id="studentForm" method="POST" class="space-y-4">
                <input type="hidden" name="action" id="formAction" value="add">
                <input type="hidden" name="student_id" id="studentId">

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Student Name *</label>
                        <input type="text" name="name" id="studentName" class="w-full border-gray-300 rounded-lg shadow-sm focus:border-primary focus:ring-primary text-sm p-2 border" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Parent Name *</label>
                        <input type="text" name="parent_name" id="parentName" class="w-full border-gray-300 rounded-lg shadow-sm focus:border-primary focus:ring-primary text-sm p-2 border" required>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Department *</label>
                        <input type="text" name="department" id="department" class="w-full border-gray-300 rounded-lg shadow-sm focus:border-primary focus:ring-primary text-sm p-2 border" required list="departmentList">
                        <datalist id="departmentList">
                            <?php foreach ($departments as $dept): ?>
                                <option value="<?php echo htmlspecialchars($dept['department']); ?>">
                            <?php endforeach; ?>
                        </datalist>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Room Number *</label>
                        <input type="text" name="room_number" id="roomNumber" class="w-full border-gray-300 rounded-lg shadow-sm focus:border-primary focus:ring-primary text-sm p-2 border" required>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">House Address *</label>
                    <input type="text" name="house_address" id="houseAddress" class="w-full border-gray-300 rounded-lg shadow-sm focus:border-primary focus:ring-primary text-sm p-2 border" required>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">House Number</label>
                        <input type="text" name="house_number" id="houseNumber" class="w-full border-gray-300 rounded-lg shadow-sm focus:border-primary focus:ring-primary text-sm p-2 border">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Phone *</label>
                        <input type="text" name="phone" id="phone" class="w-full border-gray-300 rounded-lg shadow-sm focus:border-primary focus:ring-primary text-sm p-2 border" required>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Admission Date *</label>
                    <input type="date" name="admission_date" id="admissionDate" class="w-full border-gray-300 rounded-lg shadow-sm focus:border-primary focus:ring-primary text-sm p-2 border" required>
                </div>

                <div class="flex justify-end gap-3 mt-6">
                    <button type="button" onclick="closeModal()" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg shadow-sm hover:bg-gray-50">Cancel</button>
                    <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-primary rounded-lg shadow-sm hover:bg-primary/90">Save Student</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function openAddModal() {
        document.getElementById('modalTitle').textContent = 'Add New Student';
        document.getElementById('formAction').value = 'add';
        document.getElementById('studentForm').reset();
        document.getElementById('studentModal').classList.remove('hidden');
        document.getElementById('studentModal').classList.add('flex');
    }

    function editStudent(student) {
        document.getElementById('modalTitle').textContent = 'Edit Student';
        document.getElementById('formAction').value = 'edit';
        document.getElementById('studentId').value = student.student_id;
        document.getElementById('studentName').value = student.name;
        document.getElementById('parentName').value = student.parent_name;
        document.getElementById('department').value = student.department;
        document.getElementById('roomNumber').value = student.room_number;
        document.getElementById('houseAddress').value = student.house_address;
        document.getElementById('houseNumber').value = student.house_number;
        document.getElementById('phone').value = student.phone;
        document.getElementById('admissionDate').value = student.admission_date;
        document.getElementById('studentModal').classList.remove('hidden');
        document.getElementById('studentModal').classList.add('flex');
    }

    function deleteStudent(id, name) {
        if (confirm('Are you sure you want to delete ' + name + '? This will also delete all attendance and fee records.')) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.innerHTML = `
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="student_id" value="${id}">
            `;
            document.body.appendChild(form);
            form.submit();
        }
    }

    function closeModal() {
        document.getElementById('studentModal').classList.add('hidden');
        document.getElementById('studentModal').classList.remove('flex');
    }

    function applyFilters() {
        const search = document.getElementById('searchInput').value;
        const department = document.getElementById('departmentFilter').value;
        let url = 'students.php?';
        if (search) url += 'search=' + encodeURIComponent(search) + '&';
        if (department) url += 'department=' + encodeURIComponent(department);
        window.location.href = url;
    }

    function clearFilters() {
        window.location.href = 'students.php';
    }

    document.getElementById('searchInput').addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            applyFilters();
        }
    });

    window.onclick = function(event) {
        const modal = document.getElementById('studentModal');
        if (event.target === modal) {
            closeModal();
        }
    }
</script>

</main>
</div>
</body>
</html>
