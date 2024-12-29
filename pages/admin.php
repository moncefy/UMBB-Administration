<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home - Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/flowbite/1.6.5/flowbite.min.js"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">

</head>

<body style="background: linear-gradient(35deg, #4B5563, #D1D5DB);">

<?php
session_start();
require_once '../class/db.php';
require_once '../class/AdminHandler.php'; 
require_once '../class/UserHandler.php'; 

$db = new Database('localhost', 'root', '', 'php');
$conn = $db->getConnection();

if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > 600) {
    
    session_unset();
    session_destroy();
    header("Location: login.php");
    exit();
}

$_SESSION['last_activity'] = time(); 

// Check if the user is logged in
if (!isset($_SESSION['role'])) {
    header("Location: ../pages/login.php"); 
    exit();
}

if ($_SESSION['role'] === 'admin') {
    $adminUsername = $_SESSION['username'] ?? null; // Use 'username' for admins

    if ($adminUsername) {
        $adminData = Admin::getNomPrenomByUsername($conn, $adminUsername); 

        if ($adminData) {
            $adminNom = htmlspecialchars($adminData['nom']);
            $adminPrenom = htmlspecialchars($adminData['prenom']);
            $WelcomeMessage = "Salut Mr " . $adminPrenom . " " . $adminNom . ", bienvenue sur le coté Admin du portail des services du Département d'Informatique - UMBB !";
        } else {
            $WelcomeMessage = "Bienvenue sur le portail des services du Département d'Informatique - UMBB !";
        }
    } else {
        header("Location: ../pages/login.php");
        exit();
    }
}

    
    if ($adminData) {
        
        $adminNom = $adminData['nom'];
        $adminPrenom = $adminData['prenom'];
    } else {
        
        $WelcomeMessage = "Admin not found.";
        $adminNom = "Unknown";
        $adminPrenom = "Unknown";
    }
?>

<div class="main">
    <!-- Top Bar -->
    <div class="fixed top-0 w-full bg-gray-800 text-white py-3 shadow-lg">
        <div class="container mx-auto flex items-center px-6">
            
            <button class="text-white bg-gray-700 hover:bg-gray-600 focus:ring-4 focus:ring-gray-500 rounded-lg text-sm px-3 py-2 me-4"
                    type="button"
                    data-drawer-target="drawer-navigation"
                    data-drawer-show="drawer-navigation"
                    aria-controls="drawer-navigation">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16m-7 6h7" />
                </svg>
            </button>
           
            <h4 class="text-lg font-extrabold text-blue-400 tracking-wide">UMBB</h4>
            <div class="text-sm font-medium text-right ml-auto">
                <?php
                    echo htmlspecialchars($adminPrenom) . " " . htmlspecialchars($adminNom) . " | Admin";
                ?>
            </div>
        </div>
    </div>
</div>

    <div class="flex h-screen">
        <!-- Collapsible Sidebar -->
        <div id="sidebar" 
            class="group fixed top-[calc(4rem-4px)] left-0 z-50 bg-white dark:bg-gray-800 shadow-lg h-[calc(100%-(4rem-3px))] transition-width duration-300 overflow-hidden hover:w-64 w-16">
            
            <!-- Sidebar Content -->
            <ul class="space-y-6 font-medium mt-6">
                <li>
                    <button onclick="window.location.href='admin.php'" 
                    class="flex items-center justify-center w-full p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 group-hover:justify-start">
                    <i class="fas fa-home text-blue-500"></i>
                    <span class="ml-3 hidden group-hover:inline">Accueil</span>
                </button>
                </li>
                
                <li class="group flex items-center">
    <button onclick="window.location.href='admin.php'" 
        class="flex items-center justify-center w-full p-3 rounded-lg text-white  opacity-60 cursor-not-allowed pointer-events-none shadow-none">
        <i class="fas fa-history text-blue-200"></i>
        <span class="ml-3 hidden group-hover:inline text-gray-400">Consulter vos demandes</span>
    </button>
</li>


                
                <li>
                    <button onclick="window.location.href='adminDocument.php'" 
                        class="flex items-center justify-center w-full p-3 rounded-lg text-white hover:bg-gray-100 dark:hover:bg-gray-700 group-hover:justify-start peer active:bg-gray-200 dark:active:bg-gray-700">
                        <i class="fas fa-file-alt text-green-500"></i>
                        <span class="ml-3 hidden group-hover:inline">Gérer Documents</span>
                    </button>
                </li>
                <li>
                    <button onclick="window.location.href='adminMateriel.php'" 
                        class="flex items-center justify-center w-full p-3 rounded-lg text-white hover:bg-gray-100 dark:hover:bg-gray-700 group-hover:justify-start peer active:bg-gray-200 dark:active:bg-gray-700">
                        <i class="fas fa-tools text-purple-500"></i>
                        <span class="ml-3 hidden group-hover:inline">Gérer Matériels</span>
                    </button>
                </li>
                <li>
                    <button onclick="window.location.href='adminSalle.php'" 
                        class="flex items-center justify-center w-full p-3 rounded-lg text-white hover:bg-gray-100 dark:hover:bg-gray-700 group-hover:justify-start peer active:bg-gray-200 dark:active:bg-gray-700">
                        <i class="fas fa-door-open text-orange-500"></i>
                        <span class="ml-3 hidden group-hover:inline">Gérer Salles</span>
                    </button>
                </li>

               <!-- <li>

        <button onclick="window.location.href='ccontactRequest.php'" 
        class="m-0.5 w-full p-3 rounded-lg text-white  opacity-60 cursor-not-allowed pointer-events-none shadow-none">
        <i class="fas fa-envelope text-red-500"></i>
        <span class="hidden group-hover:inline text-gray-400">Gérer Contacts</span>
    </button>
                </li> -->
            </ul>

            <!-- Logout Button (Bottom) -->
            <form action="../class/logout.php" method="POST" class="absolute bottom-4 w-full px-4">
                <button class="flex items-center justify-center w-full p-3 rounded-lg text-white hover:bg-gray-100 dark:hover:bg-gray-700 group-hover:justify-start peer active:bg-gray-200 dark:active:bg-gray-700">
                    <i class="fas fa-sign-out-alt text-red-500"></i>
                    <span class="ml-3 hidden group-hover:inline">Logout</span>
                </button>
            </form>
        </div>

<!-- Main Content Area -->
<div class="flex-1 p-6 pl-20 pt-16">
    <div id="WelcomeMessage" class="text-2xl font-medium text-gray-800 flex justify-center items-center h-64 ml-16"></div>
</div>

<script>
    const message = <?php echo json_encode($WelcomeMessage); ?>;  

    const element = document.getElementById("WelcomeMessage");
    let index = 0;

    function typeWriter() {
        if (index < message.length) {
            element.innerHTML += message.charAt(index);
            index++;  
            setTimeout(typeWriter, 50);  // typing speed //
        }
    }

    // typing effect on page load
    window.onload = function() {
        typeWriter();
    };
</script>


</body>
</html>
