# StudyPal

A collaborative study platform that enables students to form study groups, share educational materials, ask questions, and engage in peer-to-peer learning.

## Features

### User Management
- **User Registration & Authentication**: Secure signup and login system
- **Profile Management**: Users can create and edit their profiles
- **Password Recovery**: Forgot password functionality with OTP verification
- **User Profiles**: View other users' profiles

### Study Groups
- **Create Groups**: Users can create study groups with names and descriptions
- **Join/Leave Groups**: Flexible group membership management
- **Group Discovery**: Browse and search for available study groups
- **Group Pages**: Dedicated pages for each group with multiple sections

### Learning Resources
- **Material Upload**: Share documents, files, and web links with group members
- **Live Search**: Real-time search for materials within groups
- **Material Management**: Delete unwanted materials (by uploader or group creator)

### Q&A System
- **Post Questions**: Ask questions within study groups
- **Answer Questions**: Provide answers to help fellow group members
- **Question Search**: Real-time search for questions
- **Discussion Threads**: View full question threads with all answers
- **Content Management**: Delete questions and answers

### Administration
- **Admin Dashboard**: Centralized moderation panel
- **Report System**: Users can report inappropriate content
- **Content Moderation**: Admins can review and act on reports
- **User Management**: Ban users and delete flagged content

### Additional Features
- **Live Search**: Real-time search for groups, materials, questions, and members
- **Search Analytics**: Log and track search queries
- **Responsive Interface**: Modern, user-friendly design with Inter font family

## Technologies Used

- **Backend**: PHP 7.4+
- **Database**: MySQL
- **Server**: Apache (XAMPP)
- **Frontend**: HTML5, CSS3, JavaScript
- **Fonts**: Google Fonts (Inter)

## Prerequisites

- XAMPP (or LAMP/MAMP/WAMP) with PHP 7.4+ and MySQL
- Web browser (Chrome, Firefox, Safari, Edge)
- Git (optional, for version control)

## Installation

### 1. Clone or Download the Project

```bash
git clone https://github.com/sifatth/StudyPal.git
```

Or download and extract the ZIP file to your XAMPP `htdocs` directory:
```
c:\xampp\htdocs\StudyPal
```

### 2. Start XAMPP Services

- Open XAMPP Control Panel
- Start **Apache** and **MySQL** services

### 3. Create the Database

1. Open phpMyAdmin: `http://localhost/phpmyadmin`
2. Create a new database named: `studypal`
3. Import the database schema (or create tables manually if a SQL file is provided)

### 4. Database Configuration

The database connection is configured in `db_connect.php`:

```php
$servername = "localhost";
$username = "root";
$password = "";
$database = "studypal";
```

Modify these credentials if your MySQL setup differs.

### 5. Required Database Tables

Ensure your database has the following tables:
- `userprofile` - User information
- `studygroup` - Study group details
- `groupmembership` - User-group relationships
- `material` - Uploaded study materials
- `question` - Posted questions
- `answer` - Answers to questions
- `report` - User reports
- `target` - Report targets (materials, questions, answers)
- `searchlog` - Search query logs

### 6. Create Uploads Directory

Ensure the `uploads/` directory exists and is writable:

```bash
mkdir uploads
chmod 755 uploads
```

## Usage

### Access the Application

1. Open your web browser
2. Navigate to: `http://localhost/StudyPal/`
3. You'll be redirected to the login page

### First-Time Setup

1. **Sign Up**: Click on the signup link and create a new account
2. **Verify Email**: Complete OTP verification (if enabled)
3. **Login**: Use your credentials to log in
4. **Create/Join Groups**: Start by creating your own study group or joining existing ones
5. **Share Resources**: Upload materials and post questions in your groups

### Admin Access

Admin users have additional privileges:
- Access the admin dashboard at: `http://localhost/StudyPal/admin_dashboard.php`
- Review and moderate reported content
- Manage users and enforce community guidelines

## Project Structure

```
StudyPal/
├── admin_action_process.php     # Admin action handler
├── admin_dashboard.php          # Admin moderation panel
├── create_group_process.php     # Group creation handler
├── db_connect.php               # Database connection
├── delete_*.php                 # Content deletion handlers
├── edit_profile.php             # Profile editing page
├── error_*.html                 # Error pages
├── forgot_password.html         # Password recovery form
├── group_page.php               # Individual group page
├── header.php                   # Common header component
├── homepage.php                 # Main dashboard
├── join_group.php               # Join group handler
├── leave_group.php              # Leave group handler
├── live_search_*.php            # Real-time search endpoints
├── log_search_query.php         # Search logging
├── login.html                   # Login page
├── login_process.php            # Login handler
├── logout.php                   # Logout handler
├── password_reset_success.html  # Reset confirmation page
├── post_*.php                   # Content posting handlers
├── profile.php                  # User profile page
├── report_process.php           # Report submission handler
├── reset_password_*.php         # Password reset handlers
├── signup.html                  # Registration page
├── signup_process.php           # Registration handler
├── update_*.php                 # Update handlers
├── upload_material.php          # Material upload handler
├── verify_otp.php               # OTP verification page
├── verify_otp_process.php       # OTP verification handler
├── view_*.php                   # Content viewing pages
├── uploads/                     # Uploaded files directory
└── README.md                    # This file
```

## Security Considerations

- ⚠️ **For Development Only**: This application is configured for local development
- Update database credentials in production environments
- Implement proper password hashing (use `password_hash()` and `password_verify()`)
- Validate and sanitize all user inputs
- Use prepared statements to prevent SQL injection (already implemented)
- Configure proper file upload restrictions
- Implement HTTPS in production
- Set appropriate file permissions for the uploads directory

## Contributing

1. Fork the repository
2. Create a feature branch: `git checkout -b feature-name`
3. Commit your changes: `git commit -m 'Add some feature'`
4. Push to the branch: `git push origin feature-name`
5. Submit a pull request

## Support

For issues, questions, or suggestions:
- Open an issue on GitHub
- Contact: [Your contact information]

## License

This project is open source and available under the [MIT License](LICENSE).

## Acknowledgments

- Built with PHP and MySQL
- UI design inspired by modern web applications
- Uses Google Fonts (Inter font family)

---

**StudyPal** - Empowering collaborative learning through technology.
