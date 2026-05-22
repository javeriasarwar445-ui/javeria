# Smart Hostel Management System

A comprehensive web-based hostel management system built with PHP and MySQL. This system streamlines hostel administration by managing student records, tracking mess attendance, calculating monthly fees, and generating printable reports.

## Features

### 1. Admin Authentication
- Secure login system with session management
- Protected dashboard accessible only to authorized users
- Clean and modern login interface

### 2. Student Management (CRUD)
- Add new students with complete information
- Edit existing student records
- Delete student records
- Search students by name, ID, or room number
- Filter students by department
- Automatic department filtering (new departments automatically added to filter)

### 3. Mess Attendance System
- Mark daily attendance for three meals (Breakfast, Lunch, Dinner)
- Quick select options (Select All Breakfast/Lunch/Dinner)
- Date-based attendance tracking
- Visual checkbox interface for easy marking
- Automatic saving with duplicate prevention

### 4. Mess Fee Management
- Automatic monthly fee calculation based on attendance
- Configurable meal prices (Breakfast, Lunch, Dinner)
- Payment status tracking (Paid/Unpaid)
- Visual indicators for payment status
- Monthly fee summaries with total collected and pending amounts

### 5. Report Generation
- Monthly fee summary reports
- Detailed attendance reports
- Printable reports with professional formatting
- Export-ready format for accounting

### 6. Modern UI/UX
- Responsive design for all devices
- Clean and intuitive interface
- Gradient color schemes
- Interactive dashboard with statistics
- Smooth transitions and hover effects

## Technology Stack

- **Frontend:** HTML5, CSS3, JavaScript
- **Backend:** PHP 7.4+
- **Database:** MySQL 5.7+
- **Architecture:** MVC-inspired structure

## Installation & Setup

### Prerequisites
- PHP 7.4 or higher
- MySQL 5.7 or higher
- Apache/Nginx web server
- phpMyAdmin (optional, for database management)

### Step 1: Clone or Download
Download the project files to your web server directory:
```bash
# For XAMPP
C:/xampp/htdocs/hostel-management/

# For WAMP
C:/wamp64/www/hostel-management/

# For Linux/Apache
/var/www/html/hostel-management/
```

### Step 2: Create Database
1. Open phpMyAdmin (http://localhost/phpmyadmin)
2. Click "New" to create a new database
3. Name it `hostel_management`
4. Click "Create"

### Step 3: Import Database Schema
1. Select the `hostel_management` database
2. Click on "SQL" tab
3. Open the file `database/schema.sql`
4. Copy all SQL content and paste it into the SQL query box
5. Click "Go" to execute

Alternatively, use the Import feature:
1. Click "Import" tab
2. Choose file `database/schema.sql`
3. Click "Go"

### Step 4: Configure Database Connection
Open `config/database.php` and update the following if needed:
```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'hostel_management');
```

### Step 5: Start Web Server
- **XAMPP:** Start Apache from XAMPP Control Panel
- **WAMP:** Start all services from WAMP Manager
- **Linux:** `sudo service apache2 start`

### Step 6: Access the System
Open your web browser and navigate to:
```
http://localhost/hostel-management/login.php
```

### Default Login Credentials
```
Username: admin
Password: admin123
```

## Project Structure

```
hostel-management/
│
├── assets/
│   └── css/
│       └── style.css          # Main stylesheet
│
├── config/
│   ├── database.php          # Database connection
│   └── session.php           # Session management
│
├── database/
│   └── schema.sql            # Database schema with sample data
│
├── includes/
│   ├── header.php            # Common header
│   └── sidebar.php           # Navigation sidebar
│
├── index.php                 # Dashboard
├── login.php                 # Login page
├── logout.php                # Logout handler
├── students.php              # Student management
├── attendance.php            # Attendance marking
├── fees.php                  # Fee management
├── reports.php               # Report generation
└── README.md                 # This file
```

## Database Schema

### Tables

1. **admin** - Admin user accounts
2. **students** - Student information
3. **mess_attendance** - Daily attendance records
4. **mess_fees** - Monthly fee calculations
5. **meal_prices** - Configurable meal pricing

## Usage Guide

### Adding Students
1. Navigate to "Students" from the sidebar
2. Click "+ Add New Student"
3. Fill in all required information
4. Click "Save Student"

### Marking Attendance
1. Navigate to "Attendance"
2. Select the date
3. Check boxes for meals attended by each student
4. Click "Save Attendance"

### Calculating Fees
1. Navigate to "Mess Fees"
2. Select the month
3. Click "Calculate Fees"
4. Mark students as "Paid" once they pay

### Generating Reports
1. Navigate to "Reports"
2. Select month and report type
3. Click "Load Report"
4. Click "Print Report" to print

## Meal Pricing

Default prices (can be modified in database):
- Breakfast: Rs. 100
- Lunch: Rs. 150
- Dinner: Rs. 150

Total per day: Rs. 400

## Security Features

- Password hashing using PHP's `password_hash()`
- Session-based authentication
- SQL injection prevention with prepared statements
- XSS protection with `htmlspecialchars()`
- Protected pages require login

## Browser Compatibility

- Chrome (Recommended)
- Firefox
- Safari
- Edge
- Opera

## Troubleshooting

### Issue: Cannot connect to database
**Solution:** Check database credentials in `config/database.php`

### Issue: Login not working
**Solution:** Ensure database is imported correctly and admin table has data

### Issue: Attendance not saving
**Solution:** Check if students exist in database first

### Issue: Fees showing 0.00
**Solution:** Mark attendance first, then calculate fees

## Future Enhancements

- Student dashboard/portal
- SMS/Email notifications
- Advanced reporting with charts
- Backup and restore functionality
- Multiple hostel support
- Room allocation management
- Complaint management system

## Support

For issues or questions, please contact the system administrator.

## License

This project is developed for educational purposes.

---

**Smart Hostel Management System** - Efficient Hostel Administration
