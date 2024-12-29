
#   UMBB - Administration

  

###  A web-based platform to streamline administrative requests and enhance efficiency in an academic setting.

  

---

  

##   Project Overview

  

`UMBB - Administration` is a fully responsive and feature-rich system designed to manage administrative tasks, including:

  

-  **Submitting and tracking requests** for documents, Tools, and Room reservations.

-  **Contacting professors** directly through email.

-  **Admin Panel** for overseeing and managing all requests.


##  🛠 Installation

  

###  1. Clone the Repository

```bash

git  clone  https://github.com/moncefy/UMBB_ADMINISTRATION.git

```

  

###  2. Set Up the Database

- Open your preferred database client (e.g., phpMyAdmin).

- Import the `Database.sql` file into a new database.

  

###  3. Configure Database Connection

Update the `class/db.php` file with your database credentials:

  

```php

$host  =  "localhost";

$username  =  "your_username";

$password  =  "your_password";

$dbname  =  "your_database_name";

```

  

###  4. Run the Project

- Move the project folder to your server’s root directory (e.g., `htdocs` for XAMPP).

- Start your local server (Apache and MySQL).

- Visit the app in your browser:

  

```plaintext

http://localhost/UMBB_ADMINISTRATION

```

  

---

  

##   Features

  

###  User Features

- Submit and track requests for :

- 📄 **Documents**

- 🛠️ **Tools**

- 🏢 **Rooms**

- View the status of current requests.

- Contact professors directly via email.

  

### Admin Features

- Monitor and manage all incoming requests.

- Approve or reject requests for documents, Tools, and rooms.

  

---

  


## Tech Stack

* **Backend:** 

     <img src="https://img.shields.io/badge/php-black?style=for-the-badge&logo=php&logoColor=474A8A" alt="PHP"> 

     - The backend is implemented using **Object-Oriented Programming (OOP)** concepts by creating reusable classes and methods, which are dynamically called on the relevant pages for modularity and maintainability.

* **Database:** 

     <img src="https://img.shields.io/badge/MySQL-black?style=for-the-badge&logo=MySQL&logoColor=blue" alt="MySQL">

* **Frontend:** 

     <img src="https://img.shields.io/badge/html5-black?style=for-the-badge&logo=html5&logoColor=red" alt="HTML5">
     <img src="https://img.shields.io/badge/CSS-black?style=for-the-badge&logo=css3&logoColor=darkgreen" alt="CSS3">
     <img src="https://img.shields.io/badge/Tailwind_CSS-black?style=for-the-badge&logo=tailwind-css&logoColor=38B2AC" alt="Tailwind CSS">
     <img src="https://img.shields.io/badge/JavaScript-black?style=for-the-badge&logo=JavaScript&logoColor=yellow" alt="JavaScript"> 

     
##   Future Improvements

  

- 🔔 **Add notifications** The user will receive a status update notification of his request via email.

- 🎨 **Enhance UI/UX** for a more seamless experience.

