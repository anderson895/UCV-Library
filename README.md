# UCV Library Management System

A beginner-friendly library management system built with **vanilla PHP** and **MySQL**, designed to run on **XAMPP**.

## Features

- User registration and login
- Admin and regular user roles
- Browse and search books
- Borrow and return books (7-day loan period)
- Auto-overdue tracking
- Admin dashboard: manage books, users, and borrow records

## Setup (3 Steps)

### 1. Start XAMPP

Open the XAMPP Control Panel and start **Apache** and **MySQL**.

### 2. Import the Database

1. Open phpMyAdmin: <http://localhost/phpmyadmin>
2. Click the **Import** tab
3. Choose `database.sql` (in this folder)
4. Click **Go**

### 3. Run Setup

Open in your browser:

<http://localhost/Blessy%20Taccad%20Priete/setup.php>

This creates the default admin account and seeds sample books.

## Default Accounts

| Role    | Username  | Password    |
|---------|-----------|-------------|
| Admin   | `admin`   | `admin123`  |
| Student | `student` | `student123`|

## Folder Structure

```
Blessy Taccad Priete/
├── assets/
│   ├── logo.jpg          # UCV logo
│   └── style.css         # Single stylesheet (whole app)
├── config/
│   └── database.php      # DB connection settings
├── includes/
│   ├── auth.php          # Session + role helpers
│   ├── header.php        # Top navigation
│   └── footer.php        # Page footer
├── auth/
│   ├── login.php         # Log in
│   ├── register.php      # Sign up
│   └── logout.php        # Log out
├── user/
│   ├── dashboard.php     # User home
│   ├── books.php         # Browse / borrow books
│   └── my_books.php      # My borrowed books / return
├── admin/
│   ├── dashboard.php     # Admin home
│   ├── books.php         # Manage books (CRUD)
│   ├── users.php         # Manage users
│   └── borrows.php       # All borrow records
├── database.sql          # Database schema
├── setup.php             # One-time setup helper
├── index.php             # Landing page
└── README.md             # This file
```

## Open the App

<http://localhost/Blessy%20Taccad%20Priete/>
