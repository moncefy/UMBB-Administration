<?php
session_start();
require_once '../class/db.php';
require_once '../class/UserHandler.php';
$db = new Database('localhost', 'root', '', 'php');
$conn = $db->getConnection();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <title>Forgot Password</title>
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Font Awesome -->
    <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
    <style>
        body {
            background: linear-gradient(135deg, #e0e7ff, #f8fafc);
        }
    </style>
</head>

<body class="min-h-screen flex flex-col justify-center items-center">
    <!-- Top Bar -->
    <div class="fixed top-0 w-full bg-gray-800 text-white py-3 shadow-lg">
        <div class="container mx-auto flex justify-between items-center px-6">
            <h4 class="text-lg font-extrabold text-blue-400 tracking-wide">UMBB</h4>
            <a href="mailto:fs-univ-boumerdes.dz" class="text-sm font-medium hover:underline">
                Contact us: fs-univ-boumerdes.dz
            </a>
        </div>
    </div>

    <!-- Forgot Password Card -->
    <div
        class="mt-20 bg-white shadow-2xl rounded-2xl p-10 w-full max-w-sm transform transition duration-500 hover:scale-105 hover:shadow-3xl">
        <!-- Logo -->
        <div class="flex justify-center mb-6">
            <img src="saw.png" alt="Logo" style="max-width: 100px;">
        </div>
        <!-- Title -->
        <h2 class="text-center text-2xl font-semibold text-gray-800 mb-6">Forgot Your Password?</h2>

        <!-- Error/Success Messages -->
        <?php
        if (isset($_SESSION['error'])) {
            echo "<div class='bg-red-100 text-red-800 px-4 py-2 rounded-md mb-4'>" . $_SESSION['error'] . "</div>";
            unset($_SESSION['error']);
        }
        if (isset($_SESSION['success'])) {
            echo "<div class='bg-green-100 text-green-800 px-4 py-2 rounded-md mb-4'>" . $_SESSION['success'] . "</div>";
            unset($_SESSION['success']);
        }
        ?>
        
        <!-- Forgot Password Form -->
        <form method="POST" action="">
            <!-- Email Input -->
            <div class="mb-4 relative">
                <label for="email" class="block text-gray-600 font-medium mb-1">Enter your email address</label>
                <div class="relative">
                    <input type="email" name="email" id="email"
                        class="w-full pl-10 pr-4 py-2 border rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition"
                        placeholder="Enter your email" required>
                    <i class="fas fa-envelope absolute left-3 top-3.5 text-gray-400"></i>
                </div>
            </div>
            <!-- Submit Button -->
            <button type="submit" name="submit"
                class="w-full bg-blue-500 text-white font-medium py-3 rounded-lg shadow-lg hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-300 focus:ring-offset-2 transition">
                Submit
            </button>
        </form>

        <!-- Back to Login -->
        <div class="mt-4 text-center">
            <a href="login.php"
                class="w-full inline-block text-center bg-gray-200 text-gray-800 font-medium py-2 rounded-lg shadow-md hover:bg-gray-300 transition">
                Back to Login
            </a>
        </div>
    </div>
</body>

</html>
