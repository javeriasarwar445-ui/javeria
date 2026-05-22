<?php
require_once 'config/database.php';
require_once 'config/session.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = ($_POST['username'] ?? '');
    $password = ($_POST['password'] ?? '');

    if (!empty($username) && !empty($password)) {
        $db = new Database();
        $conn = $db->getConnection();

        $stmt = $conn->prepare("SELECT id, username, password FROM admin WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();       
        $result = $stmt->get_result();
        if ($result->num_rows === 1) {
            $admin = $result->fetch_assoc();

            if (password_verify($password, $admin['password'])) {
                setAdminSession($admin['id'], $admin['username']);
                header('Location: index.php');
                exit();
            } else {
                $error = 'Invalid username or password';
            }
        } else {
            $error = 'Invalid username or password';
        }

        $stmt->close();
        $db->close();
    } else {
        $error = 'Please fill in all fields';
    }
}

if (isLoggedIn()) {
    header('Location: index.php');
    exit();
}
?>
<!DOCTYPE html>
<html class="light" lang="en">
<head>
<meta charset="utf-8"/>
<meta content="width=device-width, initial-scale=1.0" name="viewport"/>
<script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&amp;display=swap" rel="stylesheet"/>
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght@100..700,0..1&amp;display=swap" rel="stylesheet"/>
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap" rel="stylesheet"/>
<script id="tailwind-config">
tailwind.config = {
  darkMode: "class",
  theme: {
    extend: {
      colors: {
        "primary": "#2b3d4f",
        "background-light": "#f6f7f7",
        "background-dark": "#16191c",
      },
      fontFamily: {
        "display": ["Inter"]
      },
      borderRadius: {"DEFAULT": "0.25rem", "lg": "0.5rem", "xl": "0.75rem", "full": "9999px"},
    },
  },
}
</script>
<title>Hostel Management System - Login</title>
</head>
<body class="bg-background-light dark:bg-background-dark font-display min-h-screen flex items-center justify-center p-4">
<div class="w-full max-w-[400px] bg-white dark:bg-slate-900 shadow-xl rounded-lg overflow-hidden border border-primary/10">
<div class="p-8">
<!-- Logo & Header -->
<div class="flex flex-col items-center mb-6">
<img src="assets/img/logo-dark.png" alt="Hostel Management System" class="h-28 w-auto mb-2 object-contain">
<p class="text-slate-500 dark:text-slate-400 text-sm mt-1">Please enter your details to sign in</p>
</div>

<?php if ($error): ?>
    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
        <span class="block sm:inline"><?php echo htmlspecialchars($error); ?></span>
    </div>
<?php endif; ?>

<!-- Login Form -->
<form action="" class="space-y-5" method="POST">
<!-- Username Field -->
<div>
<label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5" for="username">Username</label>
<div class="relative">
<div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
<span class="material-symbols-outlined text-[20px]">person</span>
</div>
<input class="block w-full pl-10 pr-3 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-900 dark:text-slate-100 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all text-sm" id="username" name="username" placeholder="Enter your username" type="text" required/>
</div>
</div>
<!-- Password Field -->
<div>
<label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5" for="password">Password</label>
<div class="relative">
<div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
<span class="material-symbols-outlined text-[20px]">lock</span>
</div>
<input class="block w-full pl-10 pr-10 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-900 dark:text-slate-100 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all text-sm" id="password" name="password" placeholder="••••••••" type="password" required/>
<button class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-primary transition-colors" type="button" onclick="const p = document.getElementById('password'); p.type = p.type === 'password' ? 'text' : 'password';">
<span class="material-symbols-outlined text-[20px]">visibility</span>
</button>
</div>
</div>
<!-- Remember Me & Forgot Password -->
<div class="flex items-center justify-between py-1">
<div class="flex items-center">
<input class="h-4 w-4 rounded border-slate-300 text-primary focus:ring-primary transition-all" id="remember-me" name="remember-me" type="checkbox"/>
<label class="ml-2 block text-sm text-slate-600 dark:text-slate-400" for="remember-me">
                            Remember me
                        </label>
</div>
<div class="text-sm">
<a class="font-medium text-primary hover:text-primary/80 transition-colors" href="#">Forgot password?</a>
</div>
</div>
<!-- Submit Button -->
<div>
<button class="w-full flex justify-center py-3 px-4 border border-transparent rounded-lg shadow-sm text-sm font-semibold text-white bg-primary hover:bg-primary/90 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary transition-all" type="submit">
                        Sign in
                    </button>
</div>
</form>
<!-- Footer Info -->
<div class="mt-8 pt-6 border-t border-slate-100 dark:border-slate-800 text-center">
<p class="text-xs text-slate-400 dark:text-slate-500">
                    © <?php echo date('Y'); ?> Smart Hostel Management System
                </p>
<!-- <div class="mt-2 flex justify-center space-x-4">
<a class="text-xs text-slate-400 hover:text-primary" href="#">Support</a>
<a class="text-xs text-slate-400 hover:text-primary" href="#">Security Policy</a>
</div> -->
</div>
</div>
</div>
<!-- Decorative Elements (Background) -->
<div class="fixed top-0 left-0 w-full h-full -z-10 opacity-30 pointer-events-none">
<div class="absolute top-[-10%] left-[-10%] w-[40%] h-[40%] bg-primary/10 rounded-full blur-3xl"></div>
<div class="absolute bottom-[-10%] right-[-10%] w-[40%] h-[40%] bg-primary/10 rounded-full blur-3xl"></div>
</div>
</body></html>
