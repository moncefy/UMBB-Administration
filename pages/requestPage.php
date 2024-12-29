<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Requests Page</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/flowbite/1.6.5/flowbite.min.js"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">

</head>

<body style="background: linear-gradient(35deg, #4B5563, #D1D5DB); bg-cover">


<?php
session_start();
require_once '../class/db.php';
require_once '../class/UserHandler.php';

$db = new Database('localhost', 'root', '', 'php');
$conn = $db->getConnection();

if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > 600) {
    // Session expired after 10 minutes
    session_unset();
    session_destroy();
    header("Location: login.php");
    exit();
}

$_SESSION['last_activity'] = time(); // Update last activity time

// Re-fetch user data
if (!isset($nom)) {
    $matricule = $_SESSION['username_or_matricule'] ?? null;
    $nom = $matricule ? Person::getNomPrenomByMatricule($conn, $matricule) : null;
}


// Check if the user is logged in
if (!isset($_SESSION['role'])) {
    header("Location: ../pages/login.php"); // Redirect to login if not logged in
    exit();
}

// Role-based checks
if ($_SESSION['role'] === 'user') {
    // For regular users
    if (!isset($_SESSION['username_or_matricule'])) {
        die("Matricule is missing from the session.");
    }

    $matricule = $_SESSION['username_or_matricule'];
    $id_user = Person::getUserIdByMatricule($conn, $matricule);
    $nom = Person::getNomPrenomByMatricule($conn, $matricule);
    $documentRequests = Person::fetchDocumentRequests($conn, $id_user);
    $WelcomeMessage = "Salut Mr." . htmlspecialchars($nom['prenom']) . ", bienvenue sur le portail des services du Département d'Informatique - UMBB ! ";
    $documentRequests = Person::fetchDocumentRequests($conn, null); // Fetch all document requests
} else {
    die("Invalid role specified.");
}

// The rest of your code to display $WelcomeMessage and $documentRequests
?>

<div class="main">
    <!-- Top Bar -->
    <div class="fixed top-0 w-full bg-gray-800 text-white py-3 shadow-lg">
        <div class="container mx-auto flex items-center px-6">
            <!-- Navigation Button -->
            <button class="text-white bg-gray-700 hover:bg-gray-600 focus:ring-4 focus:ring-gray-500 rounded-lg text-sm px-3 py-2 me-4"
                    type="button"
                    data-drawer-target="drawer-navigation"
                    data-drawer-show="drawer-navigation"
                    aria-controls="drawer-navigation">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16m-7 6h7" />
                </svg>
            </button>
            <!-- Logo and Welcome Message -->
            <h4 class="text-lg font-extrabold text-blue-400 tracking-wide">UMBB</h4>
            <div class="text-sm font-medium text-right ml-auto">
                <?php
                    echo htmlspecialchars($nom['nom']) . " " . htmlspecialchars($nom['prenom']) . 
                    " | Matricule: " . htmlspecialchars($matricule) . 
                    " | Specialité: " . htmlspecialchars($nom['specialite']);
                ?>
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
                    <button onclick="window.location.href='requestPage.php'" 
                    class="flex items-center justify-center w-full p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 group-hover:justify-start">
                    <i class="fas fa-home text-blue-500"></i>
                    <span class="ml-3 hidden group-hover:inline">Accueil</span>
                </button>
                </li>
                
                <li class="group flex items-center">
                    <button onclick="window.location.href='currentRequest.php'" 
                        class="flex items-center justify-center w-full p-3 rounded-lg text-white hover:bg-gray-100 dark:hover:bg-gray-700 group-hover:justify-start peer active:bg-gray-200 dark:active:bg-gray-700">
                        <i class="fas fa-history text-blue-500"></i>
                        <span class="ml-3 hidden group-hover:inline">Consulter vos demandes</span>
                    </button>
                </li>

                <!-- Demandes Section (Middle) -->
                <li>
                    <button onclick="window.location.href='docRequest.php'" 
                        class="flex items-center justify-center w-full p-3 rounded-lg text-white hover:bg-gray-100 dark:hover:bg-gray-700 group-hover:justify-start peer active:bg-gray-200 dark:active:bg-gray-700">
                        <i class="fas fa-file-alt text-green-500"></i>
                        <span class="ml-3 hidden group-hover:inline">Demander documents</span>
                    </button>
                </li>
                <li>
                    <button onclick="window.location.href='matRequest.php'" 
                        class="flex items-center justify-center w-full p-3 rounded-lg text-white hover:bg-gray-100 dark:hover:bg-gray-700 group-hover:justify-start peer active:bg-gray-200 dark:active:bg-gray-700">
                        <i class="fas fa-tools text-purple-500"></i>
                        <span class="ml-3 hidden group-hover:inline">Demander matériels</span>
                    </button>
                </li>
                <li>
                    <button onclick="window.location.href='salRequest.php'" 
                        class="flex items-center justify-center w-full p-3 rounded-lg text-white hover:bg-gray-100 dark:hover:bg-gray-700 group-hover:justify-start peer active:bg-gray-200 dark:active:bg-gray-700">
                        <i class="fas fa-door-open text-orange-500"></i>
                        <span class="ml-3 hidden group-hover:inline">Demander salles</span>
                    </button>
                </li>

                <!-- Contacts Section (Bottom) -->
                <li>
                    <button onclick="window.location.href='Contact.php'" 
                        class="flex items-center justify-center w-full p-3 rounded-lg text-white hover:bg-gray-100 dark:hover:bg-gray-700 group-hover:justify-start peer active:bg-gray-200 dark:active:bg-gray-700">
                        <i class="fas fa-envelope text-red-500"></i>
                        <span class="ml-3 hidden group-hover:inline">Contacter les professeurs</span>
                    </button>
                </li>
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
    <div id="WelcomeMessage" class="text-2xl font-medium text-gray-800 flex justify-center items-center h-64 ml-16" style:"width = 200px"></div>
</div>

<!-- Description Card -->
<div class="description-card">
    <h3 class="text-2xl font-bold text-center mb-4 text-gray-800">Description</h3>
    <p class="text-lg text-gray-700 font-medium text-center">
        Cette plateforme a été créée par quatre étudiants passionnés de l'Université de Boumerdes. Notre mission est de simplifier la vie des étudiants et des employés en transférant presque tous les services du département sur une plateforme en ligne, les rendant facilement accessibles et efficaces.
    </p>
</div>
    </div>
</div>


<script>
    const message = <?php echo json_encode($WelcomeMessage); ?>;  // Ensure message is properly encoded in PHP

    const element = document.getElementById("WelcomeMessage"); 
    let index = 0;

    function typeWriter() {
        if (index < message.length) {
            element.innerHTML += message.charAt(index);
            index++;  // Move to the next letter
            setTimeout(typeWriter, 50);
        }
    }
    
    // Call typeWriter when the page loads
    window.onload = function() {
        typeWriter();
    };
</script>

<style>
    .description-card {
    position: fixed;
    bottom: 300px; 
    left: 50%; 
    transform: translateX(-50%); 
    background-color: rgba(255, 255, 255, 0.2); 
    padding: 20px; 
    width: 80%; 
    max-width: 600px; /* Maximum width */
    border: 1px solid rgba(255, 255, 255, 0.1); 
    border-radius: 10px; 
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2); 
    text-align: center; 
    z-index: 1000; 
}

</style>
</body>
</html>
