<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - University Portal</title>
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Font Awesome -->
    <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" integrity="sha512-Evv84Mr4kqVGRNSgIGL/F/aIDqQb7xQ2vcrdIwxfjThSH8CSR7PBEakCr51Ck+w+/U6swU2Im1vVX0SVk9ABhg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <style>
        body {
            background: linear-gradient(35deg, #4B5563, #D1D5DB);




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

    <!-- Login Card -->
    <div
        class="mt-20 bg-white shadow-2xl rounded-2xl p-10 w-full max-w-sm transform transition duration-500 hover:scale-105 hover:shadow-3xl">
       
        <div class="flex justify-center mb-6">
            <img src="saw.png" alt="Logo" style="max-width: 100px;">
        </div>
        
        <h2 class="text-center text-2xl font-semibold text-gray-800 mb-6">Student Requests Portal</h2>

        <!-- Login Error -->
        <?php
        session_start();
        if (isset($_SESSION['login_error'])) {
            echo "<div class='bg-red-100 text-red-800 px-4 py-2 rounded-md mb-4'>" . $_SESSION['login_error'] . "</div>";
            unset($_SESSION['login_error']);
        }
        ?>

        <!-- Login Form -->
        <form action="../class/loginHandler.php" method="POST">
            
            <div class="mb-4 relative">
                <label for="username_or_matricule" name="username_or_matricule" class="block text-gray-600 font-medium mb-1">Matricule</label>
                <div class="relative">
                    <input type="text" id="username_or_matricule" name="username_or_matricule"
                        class="w-full pl-10 pr-4 py-2 border rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition"
                        placeholder="Enter your matricule" required>
                    <i class="fas fa-user absolute left-3 top-3.5 text-gray-400"></i>
                </div>
            </div>
            
            <div class="mb-4 relative">
                <label for="password" class="block text-gray-600 font-medium mb-1">Mot de passe</label>
                <div class="relative">
                    <input type="password" id="password" name="password"
                        class="w-full pl-10 pr-4 py-2 border rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition"
                        placeholder="Enter your password" required>
                    <i class="fas fa-lock absolute left-3 top-3.5 text-gray-400"></i>
                </div>
            </div>
           
            <button type="submit"
                class="w-full bg-blue-500 text-white font-medium py-3 rounded-lg shadow-lg hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-300 focus:ring-offset-2 transition">
                Login
            </button>
        </form>

        <!-- Forgot Password -->
        <div class="mt-4 text-center">
            <a href="forgotPassword.php" class="text-blue-500 hover:underline font-medium">
                Mot de passe oublié?
            </a>
        </div>
    </div>
</body>



</html>
