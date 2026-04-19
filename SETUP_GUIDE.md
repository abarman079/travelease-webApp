# TravelEase Setup Guide

## Requirements
- XAMPP installed
- Apache started
- MySQL started

## Project Setup
1. Copy the `travelease` folder into `C:\xampp\htdocs\`
2. Open phpMyAdmin
3. Create a database named `travelease_db`
4. Import the SQL file from `05_Database/travelease_db.sql`
5. Check `config/db.php` for database credentials
6. Open `http://localhost/travelease/`

## Default Local Database Settings
- host: localhost
- dbname: travelease_db
- username: root
- password: empty

## Notes
- If the project folder name changes, update `BASE_URL` in `config/config.php`
- Start Apache and MySQL before opening the project