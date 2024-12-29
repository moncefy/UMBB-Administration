<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rooms Page</title>
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
    $WelcomeMessage = "Hello " . htmlspecialchars($nom['prenom']) . " , Welcome to the Informatique department services portal!";
} elseif ($_SESSION['role'] === 'admin') {
    // For admins
    $WelcomeMessage = "Welcome Admin! You can manage document requests here.";
    $documentRequests = Person::fetchDocumentRequests($conn, null); // Fetch all document requests
} else {
    die("Invalid role specified.");
}

// The rest of your code to display $WelcomeMessage and $documentRequests
?>


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

            <!-- Navigation Section - Accueil -->
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
                    class="flex items-center justify-center w-full p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 group-hover:justify-start">
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

   <!-- Room Request Card -->
<div class="mt-32 bg-gray-300 shadow-2xl rounded-2xl p-6 w-full max-w-sm transform transition duration-500 hover:scale-105 hover:shadow-3xl mx-auto" style="height: 700px">
    <p class="text-center text-gray-500 text-sm mb-6">University Room Request Portal</p>
    <h3 class="text-center text-2xl font-semibold text-gray-800 mb-8">Request Your Room</h3>

    <!-- Room Request Form -->
    <form id="roomRequestForm" method="POST" action="../class/roomHandler.php" onsubmit="return showPopup(event)">
        <!-- Room Selection -->
        <div class="mb-6">
            <label for="bloc-room" class="block text-gray-600 font-medium mb-2">Select Bloc and Room</label>
            <select id="bloc-room" name="bloc-room"
                class="w-full pl-4 pr-4 py-3 border rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition" required>
                <option value="">-- Select Bloc and Room --</option>
                <optgroup label="Bloc 4">
                    <option value="4.101">Room 4.101</option>
                    <option value="4.102">Room 4.102</option>
                    <option value="4.103">Room 4.103</option>
                    <option value="4.104">Room 4.104</option>
                </optgroup>
                <optgroup label="Bloc 5">
                    <option value="5.101">Room 5.101</option>
                    <option value="5.102">Room 5.102</option>
                    <option value="5.103">Room 5.103</option>
                    <option value="5.104">Room 5.104</option>
                </optgroup>
            </select>
        </div>

        <!-- Time Selection -->
        <div class="mb-6">
            <label for="startTime" class="block text-gray-600 font-medium mb-2">Start Time</label>
            <input type="time" id="startTime" name="startTime"
                class="w-full pl-4 pr-4 py-3 border rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition" required>
        </div>

        <div class="mb-6">
            <label for="endTime" class="block text-gray-600 font-medium mb-2">End Time</label>
            <input type="time" id="endTime" name="endTime"
                class="w-full pl-4 pr-4 py-3 border rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition" required>
        </div>

        <div class="mb-6">
            <label for="date" class="block text-gray-600 font-medium mb-2">Date</label>
            <input type="date" id="date" name="date"
                class="w-full pl-4 pr-4 py-3 border rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition" required>
        </div>

        <!-- Submit Button -->
        <button type="submit"
            class="w-full bg-blue-500 text-white font-medium py-3 rounded-lg shadow-lg hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-300 focus:ring-offset-2 transition">
            Submit Request
        </button>
    </form>
</div>

<!-- Success Popup Modal -->
<div id="successPopup" 
     class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 z-50 hidden">
    <div class="bg-white rounded-lg shadow-lg p-6 w-80 text-center">
        <h3 class="text-lg font-bold text-blue-400">Room Request Submitted!</h3>
        <p class="text-gray-600 mt-2">Your room request has been successfully submitted.</p>
        <button id="closePopupButton"
                class="mt-4 px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600">
            Close
        </button>
    </div>
</div>

<script>
    // Show popup when the form is submitted
function showPopup(event) {
    event.preventDefault(); // Prevent form submission until after popup is closed
    
    // Display the popup
    document.getElementById("successPopup").classList.remove("hidden");
    
    // Listen for the close button click
    document.getElementById("closePopupButton").addEventListener("click", function() {
        // Close the popup
        document.getElementById("successPopup").classList.add("hidden");
        
        // Submit the form after closing the popup
        document.getElementById("roomRequestForm").submit();
    });
}
</script>

    <script src="./salReq.js"></script>
</body>

</html>
