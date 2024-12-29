<!DOCTYPE html>
<html style="background: linear-gradient(35deg, #4B5563, #D1D5DB);" lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Current Requests</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/flowbite/1.6.5/flowbite.min.js"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">

</head>
<?php
session_start();
require_once '../class/db.php';
require_once '../class/UserHandler.php';
$db = new Database('localhost', 'root', '', 'php');
$conn = $db->getConnection();
$matricule = $_SESSION['username_or_matricule'];
$id_user = Person::getUserIdByMatricule($conn, $matricule);
$nom = Person::getNomPrenomByMatricule($conn, $matricule);
$documentRequests = Person::fetchDocumentRequests($conn, $id_user);
$roomRequests = Person::fetchRoomRequests($conn, $id_user);
$materielRequests = Person::fetchMaterielRequests($conn, $id_user);

?>
<body style="background: linear-gradient(35deg, #4B5563, #D1D5DB);">

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
                    <span class="ml-3 hidden group-hover:inline">Home</span>
                </button>
            </li>
            
            <li class="group flex items-center">
                <button onclick="window.location.href='currentRequest.php'" 
                    class="flex items-center justify-center w-full p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 group-hover:justify-start">
                    <i class="fas fa-history text-blue-500"></i>
                    <span class="ml-3 hidden group-hover:inline">Check Your Requests</span>
                </button>
            </li>

            
            <li>
                <button onclick="window.location.href='docRequest.php'" 
                    class="flex items-center justify-center w-full p-3 rounded-lg text-white hover:bg-gray-100 dark:hover:bg-gray-700 group-hover:justify-start peer active:bg-gray-200 dark:active:bg-gray-700">
                    <i class="fas fa-file-alt text-green-500"></i>
                    <span class="ml-3 hidden group-hover:inline">Request documents</span>
                </button>
            </li>
            <li>
                <button onclick="window.location.href='matRequest.php'" 
                    class="flex items-center justify-center w-full p-3 rounded-lg text-white hover:bg-gray-100 dark:hover:bg-gray-700 group-hover:justify-start peer active:bg-gray-200 dark:active:bg-gray-700">
                    <i class="fas fa-tools text-purple-500"></i>
                    <span class="ml-3 hidden group-hover:inline">Request tools</span>
                </button>
            </li>
            <li>
                <button onclick="window.location.href='salRequest.php'" 
                    class="flex items-center justify-center w-full p-3 rounded-lg text-white hover:bg-gray-100 dark:hover:bg-gray-700 group-hover:justify-start peer active:bg-gray-200 dark:active:bg-gray-700">
                    <i class="fas fa-door-open text-orange-500"></i>
                    <span class="ml-3 hidden group-hover:inline">Request rooms</span>
                </button>
            </li>

            
            <li>
                <button onclick="window.location.href='Contact.php'" 
                    class="flex items-center justify-center w-full p-3 rounded-lg text-white hover:bg-gray-100 dark:hover:bg-gray-700 group-hover:justify-start peer active:bg-gray-200 dark:active:bg-gray-700">
                    <i class="fas fa-envelope text-red-500"></i>
                    <span class="ml-3 hidden group-hover:inline">Contact Professors</span>
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
<div class="w-full h-full ml-48 p-20 bg-cover bg-fixed overflow-y-auto min-h-screen" style="background: linear-gradient(35deg, #4B5563, #D1D5DB);" >
    <form method="POST" action="">
        <div>
            <!--DOCUMENTS-->
            <h2 class="text-2xl font-bold text-gray-800 mb-6">Your Document Requests</h2>
            <table class="table-auto bg-gray-300 w-full text-sm text-left shadow-lg rounded-lg border border-gray-300 mt-6">
                <thead class="bg-gray-400">
                    <tr>
                        <th class="px-4 py-3 border-r border-b border-gray-300">Request ID</th>
                        <th class="px-4 py-3 border-r border-b border-gray-300">Request Type</th>
                        <th class="px-4 py-3 border-r border-b border-gray-300">Document Type</th>
                        <th class="px-4 py-3 border-r border-b border-gray-300">Requested At</th>
                        <th class="px-4 py-3 border-r border-b border-gray-300">Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($documentRequests)): ?>
                        <?php foreach ($documentRequests as $request): ?>
                            <tr>
                                <td class="px-4 py-3 border-r border-b border-gray-300"><?= htmlspecialchars($request['id_document']); ?></td>
                                <td class="px-4 py-3 border-r border-b border-gray-300">Document</td>
                                <td class="px-4 py-3 border-r border-b border-gray-300"><?= htmlspecialchars($request['title']); ?></td>
                                <td class="px-4 py-3 border-r border-b border-gray-300"><?= htmlspecialchars($request['created_at']); ?></td>
                                <td class="px-4 py-3 border-r border-b border-gray-300"><?= htmlspecialchars($request['document_status']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="px-4 py-3 text-center text-gray-500">No requests found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>

            <!-- ROOMS-->
            <h2 class="text-2xl font-bold text-gray-800 mb-6 mt-8">Your rooms request</h2>
            <table class="table-auto bg-gray-300 w-full text-sm text-left shadow-lg rounded-lg border border-gray-300 mt-6">
                <thead class="bg-gray-400">
                    <tr>
                        <th class="px-4 py-3 border-r border-b border-gray-300">Request ID</th>
                        <th class="px-4 py-3 border-r border-b border-gray-300">Request Type</th>
                        <th class="px-4 py-3 border-r border-b border-gray-300">Room Number</th>
                        <th class="px-4 py-3 border-r border-b border-gray-300">Requested At</th>
                        <th class="px-4 py-3 border-r border-b border-gray-300">Start Time</th>
                        <th class="px-4 py-3 border-r border-b border-gray-300">End Time</th>
                        <th class="px-4 py-3 border-r border-b border-gray-300">Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($roomRequests)): ?>
                        <?php foreach ($roomRequests as $request): ?>
                            <tr>
                                <td class="px-4 py-3 border-r border-b border-gray-300"><?= htmlspecialchars($request['id_salle']); ?></td>
                                <td class="px-4 py-3 border-r border-b border-gray-300">Room</td>
                                <td class="px-4 py-3 border-r border-b border-gray-300"><?= htmlspecialchars($request['num_salle']); ?></td>
                                <td class="px-4 py-3 border-r border-b border-gray-300"><?= htmlspecialchars($request['created_at']); ?></td>
                                <td class="px-4 py-3 border-r border-b border-gray-300"><?= htmlspecialchars($request['request_start_time']); ?></td>
                                <td class="px-4 py-3 border-r border-b border-gray-300"><?= htmlspecialchars($request['request_end_time']); ?></td>
                                <td class="px-4 py-3 border-r border-b border-gray-300"><?= htmlspecialchars($request['sale_status']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="px-4 py-3 text-center text-gray-500">No requests found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>

            <!-- TOOLS-->
            <h2 class="text-2xl font-bold text-gray-800 mb-6 mt-8">Your tools requests</h2>
            <table class="table-auto bg-gray-300 w-full text-sm text-left shadow-lg rounded-lg border border-gray-300 mt-6">
                <thead class="bg-gray-400">
                    <tr>
                        <th class="px-4 py-3 border-r border-b border-gray-300">Request ID</th>
                        <th class="px-4 py-3 border-r border-b border-gray-300">Request Type</th>
                        <th class="px-4 py-3 border-r border-b border-gray-300">Materiel Type</th>
                        <th class="px-4 py-3 border-r border-b border-gray-300">Requested At</th>
                        <th class="px-4 py-3 border-r border-b border-gray-300">Start Time</th>
                        <th class="px-4 py-3 border-r border-b border-gray-300">End Time</th>
                        <th class="px-4 py-3 border-r border-b border-gray-300">Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($materielRequests)): ?>
                        <?php foreach ($materielRequests as $request): ?>
                            <tr>
                                <td class="px-4 py-3 border-r border-b border-gray-300"><?= htmlspecialchars($request['id_materiel']); ?></td>
                                <td class="px-4 py-3 border-r border-b border-gray-300">Tool</td>
                                <td class="px-4 py-3 border-r border-b border-gray-300"><?= htmlspecialchars($request['type_materiel']); ?></td>
                                <td class="px-4 py-3 border-r border-b border-gray-300"><?= htmlspecialchars($request['created_at']); ?></td>
                                <td class="px-4 py-3 border-r border-b border-gray-300"><?= htmlspecialchars($request['request_start_time']); ?></td>
                                <td class="px-4 py-3 border-r border-b border-gray-300"><?= htmlspecialchars($request['request_end_time']); ?></td>
                                <td class="px-4 py-3 border-r border-b border-gray-300"><?= htmlspecialchars($request['status']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="px-4 py-3 text-center text-gray-500">No requests found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </form>
</div>



</body>
</html>
