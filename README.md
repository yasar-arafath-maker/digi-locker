📁 DigiLocker Web App (PHP + MySQL)
A secure web-based digital locker system that allows users to upload, view, and manage documents such as Aadhar, Voter ID, and Education Certificates. Each document gets a unique QR code to access it securely.

📌 Features
✅ User registration and login system

📤 Upload documents (JPG, PNG, PDF)

📎 Supported types: Aadhar, Voter ID, Education

📄 View documents securely

🗑️ Delete documents

📱 Auto-generate QR code for each document

🔐 Access control (users can only access their own documents)

🛠️ Tech Stack
Frontend: HTML, CSS, SweetAlert

Backend: PHP (XAMPP), MySQL

QR Code Generator: phpqrcode

⚙️ Setup Instructions
✅ Install XAMPP

Download and install from: https://www.apachefriends.org/

📂 Project Structure
Place your project in:

makefile
Copy
Edit
C:\xampp\htdocs\digilocker
📥 Database Setup

Start Apache and MySQL via XAMPP Control Panel.

Open http://localhost/phpmyadmin

Run the SQL below to create the database and tables:

sql
Copy
Edit
CREATE DATABASE digilockerdb;

USE digilockerdb;

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100),
    email VARCHAR(100) UNIQUE,
    password VARCHAR(255),
    role VARCHAR(20) DEFAULT 'user'
);

CREATE TABLE documents (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    doc_type VARCHAR(50),
    file_path VARCHAR(255),
    uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);
🛠️ Configure includes/db_connect.php

php
Copy
Edit
<?php
$conn = new mysqli("localhost", "root", "", "digilockerdb");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
📦 Install QR Code Library

Place the phpqrcode library inside:

bash
Copy
Edit
/digilocker/phpqrcode/
Make sure phpqrcode/qrlib.php exists.

🧩 Enable PHP GD Library

In php.ini, uncomment:

ini
Copy
Edit
extension=gd
Restart Apache in XAMPP.

🔐 Access the App

Go to: http://localhost/digilocker/register.php

After registration, log in and start uploading!

🔗 Project Pages
Page	URL
📝 Register	http://localhost/digilocker/register.php
🔐 Login	http://localhost/digilocker/login.php
📂 Dashboard	http://localhost/digilocker/dashboard.php
📤 Upload	http://localhost/digilocker/upload_document.php

📸 Screenshots
You can add screenshots of:

Dashboard

Upload form

QR code popup

View Document

✅ To-Do (Optional Enhancements)
🔑 Password reset via email

🌐 Public access link for documents (optional)

📱 Mobile responsive design

📊 Admin dashboard for monitoring users

📧 Author
Name: Don Yasar

Project Type: DBMS Mini Project

Language: PHP

Database: MySQL
