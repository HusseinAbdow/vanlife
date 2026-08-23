<div align="center">

# 🚐 VANLIFE

**Van Rental & Leasing Platform**

Rent vans, manage leases, and share your journey — all in one place.

![PHP](https://img.shields.io/badge/PHP-8.0-777BB4?logo=php&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-MariaDB-4479A1?logo=mysql&logoColor=white)
![XAMPP](https://img.shields.io/badge/XAMPP-Local-FB7A24?logo=xampp&logoColor=white)

</div>

---

## 📖 About

**Vanlife** is a full-stack van rental web application built with **PHP & MySQL**. It allows customers to browse available vans, view detailed vehicle info, start and manage active leases, and leave reviews — while vendors and admins manage the fleet from dedicated dashboards.

---

## 🎬 Demo Video

### 🏠 Landing Page Walkthrough

[![Landing Page Video](Read_images_%26_videos/landingPage.png)](Read_images_%26_videos/landingpage_vid.mp4)

> ▶️ **Click the image above to watch the demo video** *(GitHub doesn't autoplay local `.mp4` files — for best results, drag & drop `landingpage_vid.mp4` into any GitHub issue and paste the generated `user-attachments` URL here)*

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

Clone or copy the project into XAMPP's `htdocs` folder:

```bash
cd C:\xampp\htdocs
git clone <your-repo-url> vanlife
```

### 2️⃣ Import the Database

1. Open **http://localhost/phpmyadmin**
2. Click **"Yeni" / "New"** in the left menu and create a database named **`vanlife_db`**
3. Select the new database, go to the **"İçe Aktar" / "Import"** tab
4. Choose **`database/vanlife_db.sql`** and click **"Git" / "Go"**

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

### 4️⃣ Run the Website

Start **Apache** and **MySQL** from the XAMPP control panel, then open:

```
http://localhost/vanlife/login.php
```

🎉 That's it — welcome to Vanlife!

---

<div align="center">

Made with ❤️ for van lovers

</div>
