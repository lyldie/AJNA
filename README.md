# AJ & A Inventory & POS System

A web-based inventory management and Point-of-Sale (POS) system developed as an academic project using PHP and MySQL. It streamlines product tracking, customer ordering, checkout transactions, and daily sales reporting.

## Key Features
- **Point-of-Sale (POS) & Order Processing:** Handles cart checkout and customer order tracking with automatic real-time stock deduction.
- **Inventory Management:** Manages product listings, stock levels, and automated low-stock alerts via the admin dashboard.
- **Role-Based Access Control:** Supports separate authentication and permissions for **Admin** and **Cashier/Customer** roles.
- **Sales Reporting:** Displays transaction logs and daily sales summaries.

## Tech Stack
- **Backend:** PHP
- **Database:** MySQL (`inventory_system`)
- **Dependencies:** Composer
- **Local Server Environment:** Apache (XAMPP)

## Local Setup Instructions
1. Clone this repository into your `htdocs` directory (`C:\xampp\htdocs\AJNA`):
   ```bash
   git clone [https://github.com/lyldie/AJNA.git](https://github.com/lyldie/AJNA.git)
   ```
2. Run `composer install` in the project root to install required PHP packages.
3. Start **Apache** and **MySQL** via the XAMPP Control Panel.
4. Open **phpMyAdmin** (`http://localhost/phpmyadmin`), create a database named `inventory_system`, and import `inventory_system.sql`.
5. Verify your local database credentials in `config/dbcon.php` (`localhost`, `root`, blank password), then access the app via `http://localhost/AJNA`.

## Author
- **John Loyld Estardo** — BS in Information Systems Student
