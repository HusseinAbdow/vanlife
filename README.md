<div align="center">

# 🚐 VANLIFE

**Van Rental & Leasing Platform**

Rent vans, manage leases, and share your journey — all in one place.

[![PHP](https://img.shields.io/badge/PHP-8.0-777BB4?logo=php&logoColor=white)](https://www.php.net/)
[![MySQL](https://img.shields.io/badge/MySQL-MariaDB-4479A1?logo=mysql&logoColor=white)](https://mariadb.org/)
[![XAMPP](https://img.shields.io/badge/XAMPP-Local-FB7A24?logo=xampp&logoColor=white)](https://www.apachefriends.org/)

[Report Bug](../../issues) · [Releases](../../releases) · [Clone Repo](#️-installation--setup)

</div>

---

## 📖 About

**Vanlife** is a full-stack van rental web application built with **PHP & MySQL**. It allows customers to browse available vans, view detailed vehicle info, start and manage active leases, and leave reviews — while vendors and admins manage the fleet from dedicated dashboards.

---

## 🎬 Demo Video

### 🏠 Landing Page Walkthrough

https://github.com/user-attachments/assets/a8e0a9af-3c70-41b2-8aa0-86c69bf8cac3

> ▶️ Click the player above to watch the walkthrough — or [download the full-quality video](https://github.com/HusseinAbdow/vanlife/releases/download/v1.0/landingpage_vid.mp4).

---

## ✨ Features

| Role | Capabilities |
|---|---|
| 🧑 **Customer** | Browse & search vans, view vehicle details, start leases, track active rentals, request support |
| 🚐 **Vendor** | Manage own van listings, review incoming rental requests, track active leases, message customers |
| 🛡️ **Admin** | Full fleet oversight, user & vendor management, platform-wide dashboard |

---

## 📸 Screenshots

### 🏠 Landing Page

<img src="Read_images_%26_videos/landingPage.png" width="100%" alt="Landing Page"/>

### 🚙 Car Info

<img src="Read_images_%26_videos/carInfo.png" width="100%" alt="Car Info"/>

### 🚗 Car Info — Details

<img src="Read_images_%26_videos/carInfo2.png" width="100%" alt="Car Info 2"/>

### 🔑 Available Cars

<img src="Read_images_%26_videos/availableCars.png" width="100%" alt="Available Cars"/>

### 📋 Active Leases

<img src="Read_images_%26_videos/active_leases.png" width="100%" alt="Active Leases"/>

### ⭐ Customer Review

<img src="Read_images_%26_videos/customerReview%20.png" width="100%" alt="Customer Review"/>

---

## ⚙️ Installation & Setup

> Requires **[XAMPP](https://www.apachefriends.org/)** (Apache + MySQL) installed.

### 1️⃣ Clone the Project

Clone the repo into XAMPP's `htdocs` folder:

```bash
cd C:\xampp\htdocs
git clone https://github.com/HusseinAbdow/vanlife.git
```

### 2️⃣ Import the Database

1. Open **http://localhost/phpmyadmin**
2. Click **"Yeni" / "New"** in the left menu and create a database named **`vanlife_db`**
3. Select the new database, go to the **"İçe Aktar" / "Import"** tab
4. Choose **`database/vanlife_db.sql`** from the project folder and click **"Git" / "Go"**

### 3️⃣ Database Connection

The app connects with these credentials (configured in `configs/database.php`):

| Setting | Value |
|---|---|
| Host | `localhost` |
| Username | `root` |
| Password | *(empty)* |
| Database | `vanlife_db` |

```php
$host = 'localhost';
$dbname = 'vanlife_db';
$username = 'root';
$password = '';
```

> These are XAMPP's default credentials — no changes needed on a fresh install.

### 4️⃣ Run the Website

Start **Apache** and **MySQL** from the XAMPP control panel, then open:

```
http://localhost/vanlife/login.php
```

🎉 That's it — welcome to Vanlife!

---

## 🗂️ Project Structure

```
vanlife/
├── admin/               # Admin dashboard & fleet oversight
├── musteri/             # Customer area (browsing, leases, reviews)
├── satici/              # Vendor area (listings, requests, orders)
├── configs/
│   ├── database.php     # PDO connection settings
│   └── vanlife_db.sql   # Full database schema + seed data
├── assets/              # Images & static assets
├── uploads/             # User-uploaded media
├── Read_images_&_videos # README screenshots & demo video
├── login.php            # Login page
└── registration.php     # Registration page
```

---

## 🛠️ Tech Stack

- **Backend:** PHP 8 (PDO)
- **Database:** MySQL / MariaDB
- **Frontend:** HTML5, CSS3, JavaScript
- **Server:** Apache via XAMPP

---

<div align="center">

Made with ❤️ by [HusseinAbdow](https://github.com/HusseinAbdow) for van lovers

</div>
