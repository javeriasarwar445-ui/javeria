# Smart Hostel Management System
## Step-by-Step Installation Guide

This guide will help you set up the Smart Hostel Management System on your local computer.

---

## What You Need Before Starting

1. **XAMPP** (Recommended for Windows) or **WAMP** or **MAMP**
   - Download from: https://www.apachefriends.org/
   - This includes PHP, MySQL, and Apache web server

2. **A Web Browser** (Chrome, Firefox, etc.)

3. **Text Editor** (Optional - for customization)

---

## Installation Steps

### Step 1: Install XAMPP

1. Download XAMPP from https://www.apachefriends.org/
2. Run the installer
3. Install to default location: `C:\xampp`
4. Complete the installation

### Step 2: Start XAMPP Services

1. Open XAMPP Control Panel
2. Click "Start" next to **Apache**
3. Click "Start" next to **MySQL**
4. Both should show green "Running" status

### Step 3: Copy Project Files

1. Locate your project folder
2. Copy the entire `hostel-management` folder
3. Paste it into: `C:\xampp\htdocs\`
4. Final path should be: `C:\xampp\htdocs\hostel-management\`

### Step 4: Create Database

1. Open your web browser
2. Go to: `http://localhost/phpmyadmin`
3. Click on "New" in the left sidebar
4. Database name: `hostel_management`
5. Click "Create"

### Step 5: Import Database Tables

**Method 1: Using SQL Tab**
1. Click on the `hostel_management` database (left sidebar)
2. Click on "SQL" tab at the top
3. Open the file: `hostel-management/database/schema.sql` with Notepad
4. Copy all the content
5. Paste it into the SQL query box in phpMyAdmin
6. Click "Go" button at the bottom
7. You should see "Query executed successfully" messages

**Method 2: Using Import**
1. Click on the `hostel_management` database
2. Click on "Import" tab
3. Click "Choose File"
4. Select `schema.sql` from `hostel-management/database/` folder
5. Click "Go" at the bottom
6. Wait for success message

### Step 6: Verify Database Setup

1. In phpMyAdmin, click on `hostel_management` database
2. You should see 5 tables:
   - admin
   - students
   - mess_attendance
   - mess_fees
   - meal_prices

### Step 7: Access the System

1. Open your web browser
2. Go to: `http://localhost/hostel-management/login.php`
3. You should see the login page

### Step 8: Login

Use these credentials:
```
Username: admin
Password: admin123
```

---

## Verify Everything is Working

After logging in, you should see the dashboard with:
- Total Students: 3
- Departments: 3
- Quick action cards

### Test Each Module

1. **Students**: Click "Students" in sidebar
   - You should see 3 sample students
   - Try adding a new student

2. **Attendance**: Click "Attendance"
   - Select today's date
   - Mark some checkboxes
   - Click "Save Attendance"

3. **Fees**: Click "Mess Fees"
   - Select current month
   - Click "Calculate Fees"
   - You should see calculated fees

4. **Reports**: Click "Reports"
   - Select current month
   - Click "Print Report"

---

## Troubleshooting

### Problem: "Database connection error"

**Solution:**
1. Make sure MySQL is running in XAMPP
2. Check database name is exactly: `hostel_management`
3. Verify you imported the schema.sql file

### Problem: "Page not found" or "404 Error"

**Solution:**
1. Make sure Apache is running in XAMPP
2. Check folder location: `C:\xampp\htdocs\hostel-management\`
3. Use correct URL: `http://localhost/hostel-management/login.php`

### Problem: Login not working

**Solution:**
1. Make sure you imported the database schema
2. Check the admin table has data:
   - Open phpMyAdmin
   - Click `hostel_management` database
   - Click `admin` table
   - Click "Browse"
   - You should see one admin user

### Problem: Blank white page

**Solution:**
1. Enable error display:
   - Open `C:\xampp\php\php.ini`
   - Find `display_errors = Off`
   - Change to `display_errors = On`
   - Restart Apache in XAMPP

### Problem: Changes not showing

**Solution:**
1. Clear browser cache (Ctrl + Shift + Delete)
2. Refresh page (F5 or Ctrl + R)
3. Try different browser

---

## Default Configuration

### Database Connection
Located in: `config/database.php`
```php
DB_HOST: localhost
DB_USER: root
DB_PASS: (empty)
DB_NAME: hostel_management
```

### Meal Prices
- Breakfast: Rs. 100
- Lunch: Rs. 150
- Dinner: Rs. 150

To change prices:
1. Open phpMyAdmin
2. Click `hostel_management` database
3. Click `meal_prices` table
4. Click "Edit" (pencil icon)
5. Change values
6. Click "Go"

---

## Next Steps After Installation

1. **Change Admin Password**
   - For security, change the default password
   - Use a strong password

2. **Add Your Students**
   - Go to Students module
   - Add all your hostel students

3. **Start Marking Attendance**
   - Go to Attendance module
   - Mark daily attendance

4. **Calculate Monthly Fees**
   - At month end, go to Fees module
   - Calculate fees for the month

5. **Generate Reports**
   - Use Reports module for printable records

---

## Getting Help

If you encounter any issues:

1. Check the troubleshooting section above
2. Verify all installation steps were completed
3. Check XAMPP error logs:
   - `C:\xampp\apache\logs\error.log`
   - `C:\xampp\mysql\data\mysql_error.log`

---

## System Requirements

**Minimum Requirements:**
- OS: Windows 7/8/10/11, Linux, or macOS
- RAM: 2 GB
- PHP: 7.4 or higher
- MySQL: 5.7 or higher
- Apache: 2.4 or higher

**Recommended:**
- RAM: 4 GB or more
- Modern browser (Chrome, Firefox)
- SSD for faster performance

---

## Backup Recommendation

**Regular Backups:**
1. Backup database weekly
2. Export from phpMyAdmin
3. Save the SQL file in safe location

**How to Backup:**
1. Open phpMyAdmin
2. Click `hostel_management` database
3. Click "Export" tab
4. Click "Go"
5. Save the file

---

**Installation Complete!**

You now have a fully functional Smart Hostel Management System.

For detailed usage instructions, please refer to README.md file.
