<?php
session_start();

require_once '../class/db.php'; 
require_once '../class/UserHandler.php';
require_once '../class/adminHandler.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get the inputs from the form
    $usernameOrMatricule = $_POST['username_or_matricule']; 
    $password = $_POST['password'];

    
    $db = new Database('localhost', 'root', '', 'php');
    $conn = $db->getConnection();

    // Check if the input is for a user (matricule) or admin (username)
    if (is_numeric($usernameOrMatricule)) {
        
        $matricule = $usernameOrMatricule;
        $userId = Person::login($conn, $matricule, $password);

        if ($userId) {
            
            $_SESSION['id'] = $userId;
            $_SESSION['username_or_matricule'] = $matricule;
            $_SESSION['role'] = 'user';  // Set role as 'user'

            header("Location: ../pages/requestPage.php");
            exit();
        } else {
            
            $_SESSION['login_error'] = "Invalid credentials. Please try again.";
            header("Location: ../pages/login.php");
            exit();
        }
    } else {
        
        $username = $usernameOrMatricule;
        $adminId = Admin::loginA($conn, $username, $password);

        if ($adminId) {
            
            $_SESSION['admin_id'] = $adminId;
            $_SESSION['username'] = $username;
            $_SESSION['role'] = 'admin';  

            header("Location: ../pages/admin.php");
            exit();
        } else {
            
            $_SESSION['login_error'] = "Invalid credentials. Please try again.";
            header("Location: ../pages/login.php");
            exit();
        }
    }
}

?>
