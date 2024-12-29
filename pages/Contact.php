<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Page</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/flowbite/1.6.5/flowbite.min.js"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">

</head>

<body style="background: linear-gradient(35deg, #4B5563, #D1D5DB);">


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

if (!isset($_SESSION['role'])) {
    header("Location: ../pages/login.php");
    exit();
}

// Role-based checks
if ($_SESSION['role'] === 'user') {
    
    if (!isset($_SESSION['username_or_matricule'])) {
        die("Matricule is missing from the session.");
    }

    $matricule = $_SESSION['username_or_matricule'];
    $id_user = Person::getUserIdByMatricule($conn, $matricule);
    $nom = Person::getNomPrenomByMatricule($conn, $matricule);
    $documentRequests = Person::fetchDocumentRequests($conn, $id_user);
    $WelcomeMessage = "Hello " . htmlspecialchars($nom['prenom']) . " , Welcome to the Informatique department services portal!";
} elseif ($_SESSION['role'] === 'admin') {
    // For admins
    $WelcomeMessage = "Welcome Admin! You can manage document requests here.";
    $documentRequests = Person::fetchDocumentRequests($conn, null);
} else {
    die("Invalid role specified.");
}

?>

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
                    class="flex items-center justify-center w-full p-3 rounded-lg text-white hover:bg-gray-100 dark:hover:bg-gray-700 group-hover:justify-start peer active:bg-gray-200 dark:active:bg-gray-700">
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

            
            <li>
                <button onclick="window.location.href='docRequest.php'" 
                    class="flex items-center justify-center w-full p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 group-hover:justify-start">
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

           
            <li>
                <button onclick="window.location.href='ccontactRequest.php'" 
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

    <!-- Main Content -->
    <div class="flex-1 ml-16 mt-16 p-6">
        <h2 class="text-2xl font-bold text-gray-800 mb-4">Contactez les Professeurs</h2>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
            <!-- Professor Card 1 -->
            <div class="bg-gray-300 shadow-lg rounded-lg p-4 flex flex-col items-center">
                <h3 class="font-semibold text-xl text-gray-800">Prof. Youcef Yahiatene</h3>
                <p class="text-gray-700">Email: yahiatene.y@gmail.com</p>
                <p class="text-gray-900"> Chef Departement </p>
            </div>
            <div class="bg-gray-300 shadow-lg rounded-lg p-4 flex flex-col items-center">
                <h3 class="font-semibold text-xl text-gray-800">Prof. Mokrani Hocine</h3>
                <p class="text-gray-700">Email: mokrani.hocine@univ-boumerdes,com</p>
                <p class="text-gray-900"> Vice Chef Departement </p>
            </div>
            <div class="bg-gray-300 shadow-lg rounded-lg p-4 flex flex-col items-center">
                <h3 class="font-semibold text-xl text-gray-800">Prof. Gaceb Mohamed</h3>
                <p class="text-gray-700">Email: miec.info@gmail.com</p>
                <p class="text-gray-900"> Professor  </p>
            </div>
            <div class="bg-gray-300 shadow-lg rounded-lg p-4 flex flex-col items-center">
                <h3 class="font-semibold text-xl text-gray-800">Prof. Maouch Rida</h3>
                <p class="text-gray-700">Email: armaouche@yahoo.fr</p>
                <p class="text-gray-900"> Professor </p>
            </div>
            <div class="bg-gray-300 shadow-lg rounded-lg p-4 flex flex-col items-center">
                <h3 class="font-semibold text-xl text-gray-800">Prof. Menouar Boulif</h3>
                <p class="text-gray-700">Email: boumen7@gmail.com</p>
                <p class="text-gray-900"> Professor </p>
            </div>
            <div class="bg-gray-300 shadow-lg rounded-lg p-4 flex flex-col items-center">
                <h3 class="font-semibold text-xl text-gray-800">Prof. Bourennane Noudjoud</h3>
                <p class="text-gray-700">Email: bourennane,noudjoud01@gmail.com</p>
                <p class="text-gray-900"> Professor </p>
            </div>
            <div class="bg-gray-300 shadow-lg rounded-lg p-4 flex flex-col items-center">
                <h3 class="font-semibold text-xl text-gray-800">Prof. Ibtihel Baddari</h3>
                <p class="text-gray-700">Email: i.baddari@gmail.com</p>
                <p class="text-gray-900"> Professor </p>
            </div>
            <div class="bg-gray-300 shadow-lg rounded-lg p-4 flex flex-col items-center">
                <h3 class="font-semibold text-xl text-gray-800">Scolarité</h3>
                <p class="text-gray-700">Email: umbb_fs_di.scolarite@yahoo.fr</p>
                <p class="text-gray-900"> Scolarité</p>
            </div>
        </div>
    </div>
</div>


</body>
</html>
