# NHPC Empanelled Hospitals

## Tech Stack

- PHP
- MySQL
- Bootstrap 5
- Font Awesome Icons
- Animate.css

## Prerequisites

- XAMPP/WAMP/MAMP server
- PHP 7.0 or higher
- MySQL 5.6 or higher

## Installation

1. Clone the repository to your web server directory:
   ```bash
   git clone [repository-url]
   ```

2. Import the database schema:
   - Open phpMyAdmin
   - Create a new database
   - Import `table qurery.sql`

3. Configure database connection:
   - Open `db.php`
   - Update database credentials if needed

4. Access the application:
   ```
   http://localhost/path_to_project
   ```

## Project Structure

```
├── add_hospital.php    # Add new hospital form
├── db.php             # Database connection
├── delete_hospital.php # Hospital deletion handler
├── index.php          # Main hospital listing
├── save_hospital.php  # Hospital data save handler
└── table qurery.sql   # Database schema
```

## Features in Detail

### Hospital Listing
- Responsive table layout
- Real-time search functionality
- Smooth animations on load
- Modern vintage styling with gradients

### Add Hospital Form
- Comprehensive input validation
- Date range validation
- Bilingual field support
- Modern form styling with vintage aesthetics





