<?php 
   session_start();
   ob_start(); 
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
   if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
       header("Location: ../pages/login.php"); 
       exit();
   }
   
 
   $adminUsername = $_SESSION['username'] ?? null; 
   
   if ($adminUsername) {
       $adminData = Admin::getNomPrenomByUsername($conn, $adminUsername);
   
       if ($adminData) {
           $adminNom = htmlspecialchars($adminData['nom']);
           $adminPrenom = htmlspecialchars($adminData['prenom']);
           $WelcomeMessage = "Salut Mr " . $adminPrenom . " " . $adminNom . ", bienvenue sur le coté Admin du portail des services du Département d'Informatique - UMBB !";
       } else {
           
           $WelcomeMessage = "Admin not found.";
           $adminNom = "Unknown";
           $adminPrenom = "Unknown";
       }
   } else {
       header("Location: ../pages/login.php"); 
       exit();
   }

    
    $filterMateriel = isset($_POST['filter_materiel']) ? $_POST['filter_materiel'] : '';
    $filterSpecialite = isset($_POST['filter_specialite']) ? $_POST['filter_specialite'] : '';
    $filterStatus = isset($_POST['filter_status']) ? $_POST['filter_status'] : '';

    
    $sql = "
        SELECT 
            u.matricule, 
            u.nom, 
            u.prenom, 
            u.group, 
            u.specialite, 
            u.email, 
            m.id_materiel, 
            m.type_materiel, 
            m.created_at, 
            m.request_start_time, 
            m.request_end_time, 
            m.status
        FROM 
            user u
        INNER JOIN 
            materiel m 
        ON 
            u.id = m.id_user
    ";

    
    $conditions = [];
    if ($filterMateriel) {
        $conditions[] = "m.type_materiel = '$filterMateriel'";
    }
    if ($filterSpecialite) {
        $conditions[] = "u.specialite = '$filterSpecialite'";
    }
    if ($filterStatus) {
        $conditions[] = "m.status = '$filterStatus'";
    }
    
    
    if (!empty($conditions)) {
        $sql .= " WHERE " . implode(" AND ", $conditions);
    }

    
    $result = $conn->query($sql);
    $usermateriel = $result->fetch_all(MYSQLI_ASSOC);

    $updateMessage = '';
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
        
        $data = explode('_', $_POST['update_status']);
        $matricule = $data[0];
        $id_materiel = $data[1];

        
        $status = $_POST['status'][$matricule . '_' . $id_materiel] ?? null;

        
        if ($status) {
            if (updateMaterialStatusByMatriculeAndIdMateriel($conn, $matricule, $id_materiel, $status)) {
                $updateMessage = "Material status updated successfully.";
            } else {
                $updateMessage = "Error updating material status.";
            }
           
            $result = $conn->query($sql);
            $usermateriel = $result->fetch_all(MYSQLI_ASSOC);
        } else {
            $updateMessage = "No status selected for matricule: " . htmlspecialchars($matricule);
        }
    }
    
    $updateMessage = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    
    $data = explode('_', $_POST['update_status']);
    if (count($data) === 2) {
        $matricule = $data[0];
        $id_salle = $data[1];
        $status = $_POST['status'][$matricule . '_' . $id_salle] ?? null;

        if ($status) {
            if (updateSalleStatusByMatriculeAndIdSalle($conn, $matricule, $id_salle, $status)) {
                $updateMessage = "Room reservation status updated successfully.";
            } else {
                $updateMessage = "Error updating room reservation status.";
            }
            
            $usersalle = fetchSalleFiltered($conn, $sale_status_filter, '', $speciality_filter, $level_filter, '');
        } else {
            $updateMessage = "No status selected for matricule: " . htmlspecialchars($matricule);
        }
    } else {
        $updateMessage = "Invalid data received.";
    }
}

    // Function to update tools request status
    function updateMaterialStatusByMatriculeAndIdMateriel($conn, $matricule, $id_materiel, $status) {
        $sql = "
            UPDATE materiel mr
            INNER JOIN user u ON u.ID = mr.id_user
            SET mr.status = ?
            WHERE u.matricule = ? AND mr.id_materiel = ?
        ";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssi", $status, $matricule, $id_materiel);
        $result = $stmt->execute();
        $stmt->close();
        return $result;
    }
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Tools</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/flowbite/1.6.5/flowbite.min.js"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
</head>

<body style="background: linear-gradient(35deg, #4B5563, #D1D5DB);">



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
                    class="flex items-center justify-center w-full p-3 rounded-lg text-white hover:bg-gray-100 dark:hover:bg-gray-700 group-hover:justify-start peer active:bg-gray-200 dark:active:bg-gray-700">
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
                    class="flex items-center justify-center w-full p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 group-hover:justify-start">
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
        class="flex items-center justify-center w-full p-3 rounded-lg text-white  opacity-60 cursor-not-allowed pointer-events-none shadow-none">
        <i class="fas fa-envelope text-red-500"></i>
        <span class="ml-3 hidden group-hover:inline text-gray-400">Gérer Contacts</span>
    </button>
            </li> -->
        </ul>

        <!-- Logout Button -->
        <form action="../class/logout.php" method="POST" class="absolute bottom-4 w-full px-4">
            <button class="flex items-center justify-center w-full p-3 rounded-lg text-white hover:bg-gray-100 dark:hover:bg-gray-700 group-hover:justify-start peer active:bg-gray-200 dark:active:bg-gray-700">
                <i class="fas fa-sign-out-alt text-red-500"></i>
                <span class="ml-3 hidden group-hover:inline">Logout</span>
            </button>
        </form>
    </div>

    <div class="w-full h-full ml-48 p-20 bg-cover bg-fixed overflow-y-auto min-h-screen" style="background: linear-gradient(35deg, #4B5563, #D1D5DB);" >
<!-- Filter Form -->
<form method="POST" action="" class="flex space-x-4">
    <!-- Bloc Filter -->
    <div class="flex items-center">
        <label for="bloc" class="text-white mt-8">Needed Bloc</label>
        <!-- Tools Filter -->
        <select name="filter_materiel" class="ml-9 px-2 py-1 rounded border mt-8 text-sm">
            <option value="">Select Materiel</option>
            <option value="">All</option>
            <option value="datashow 1" <?php echo isset($filterMateriel) && $filterMateriel == 'datashow 1' ? 'selected' : ''; ?>>Datashow 1 - Acer Noire</option>
            <option value="datashow 2" <?php echo isset($filterMateriel) && $filterMateriel == 'datashow 2' ? 'selected' : ''; ?>>Datashow 2 - Dell Blanc</option>
            <option value="datashow 3" <?php echo isset($filterMateriel) && $filterMateriel == 'datashow 3' ? 'selected' : ''; ?>>Datashow 3 - Sony Noire</option>
            <option value="printer 1" <?php echo isset($filterMateriel) && $filterMateriel == 'printer 1' ? 'selected' : ''; ?>>Printer 1 - Nicon Gris</option>
            <option value="printer 2" <?php echo isset($filterMateriel) && $filterMateriel == 'printer 2' ? 'selected' : ''; ?>>Printer 2 - Sony Blanche</option>
            <option value="markers" <?php echo isset($filterMateriel) && $filterMateriel == 'markers' ? 'selected' : ''; ?>>Markers - Sony Blanche</option>
        </select>
</div>

        
        <div class="flex items-center">
        <label for="bloc" class="text-white mt-8">Needed Bloc</label>
        <!-- Specialite Filter -->
        <select name="filter_specialite" class="ml-9 px-2 py-1 rounded border mt-8 text-sm">
            <option value="">Select Specialite</option>
            <option value="">All</option>
            <option value="LMD" <?php echo isset($filterSpecialite) && $filterSpecialite == 'LMD' ? 'selected' : ''; ?>>LMD</option>
            <option value="ING" <?php echo isset($filterSpecialite) && $filterSpecialite == 'ING' ? 'selected' : ''; ?>>ING</option>
            <option value="LMD-ISIL" <?php echo isset($filterSpecialite) && $filterSpecialite == 'LMD-ISIL' ? 'selected' : ''; ?>>LMD-ISIL</option>
            <option value="LMD-SI" <?php echo isset($filterSpecialite) && $filterSpecialite == 'LMD-SI' ? 'selected' : ''; ?>>LMD-SI</option>
        </select>
</div>

        <!-- Status Filter -->
        <div class="flex items-center">
        <label for="bloc" class="text-white mt-8">Needed Bloc</label>
        <select name="filter_status" class="ml-9 px-2 py-1 rounded border mt-8 text-sm">
            <option value="">Select Status</option>
            <option value="">All</option>
            <option value="en cours" <?php echo isset($filterStatus) && $filterStatus == 'en cours' ? 'selected' : ''; ?>>En cours</option>
            <option value="accepté" <?php echo isset($filterStatus) && $filterStatus == 'accepté' ? 'selected' : ''; ?>>Accepté</option>
            <option value="refusé" <?php echo isset($filterStatus) && $filterStatus == 'refusé' ? 'selected' : ''; ?>>Refusé</option>
        </select>
</div>

        <div class="flex items-end">
        <button type="submit" class="bg-green-500 text-white px-3 py-1 rounded mt-8 text-sm">Filter</button>
    </div>
</form>
<!-- Table of Results -->
<div class="overflow-x-auto p-4">
    <form method="POST" action="" class="p-4">
    <table class="table-auto bg-gray-300 w-full text-sm text-left shadow-lg rounded-lg border border-gray-300 mt-6">
                <thead 
                <tr class="bg-gray-700 text-white">
                        <th class="bg-gray-800 px-4 py-3 border-r border-b border-black">Matricule</th>
                        <th class="bg-gray-800 px-4 py-3 border-r border-b border-black">Name</th>
                        <th class="bg-gray-800 px-4 py-3 border-r border-b border-black">Prenom</th>
                        <th class="bg-gray-800 px-4 py-3 border-r border-b border-black">Group</th>
                        <th class="bg-gray-800 px-4 py-3 border-r border-b border-black">Specialite</th>
                        <th class="bg-gray-800 px-4 py-3 border-r border-b border-black">Email</th>
                        <th class="bg-gray-800 px-4 py-3 border-r border-b border-black">Materiel</th>
                        <th class="bg-gray-800 px-4 py-3 border-r border-b border-black">Start Time</th>
                        <th class="bg-gray-800 px-4 py-3 border-r border-b border-black">End Time</th>
                        <th class="bg-gray-800 px-4 py-3 border-r border-b border-black">Status</th>
                        <th class="bg-gray-800 px-4 py-3 border-r border-b border-black">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($usermateriel): ?>
                        <?php foreach ($usermateriel as $materiel): ?>
                            <tr class="bg-gray-700 text-black">
                                <td class="px-4 py-3 border-r border-b border-black bg-gray-300"><?php echo htmlspecialchars($materiel['matricule']); ?></td>
                                <td class="px-4 py-3 border-r border-b border-black bg-gray-300"><?php echo htmlspecialchars($materiel['nom']); ?></td>
                                <td class="px-4 py-3 border-r border-b border-black bg-gray-300"><?php echo htmlspecialchars($materiel['prenom']); ?></td>
                                <td class="px-4 py-3 border-r border-b border-black bg-gray-300"><?php echo htmlspecialchars($materiel['group']); ?></td>
                                <td class="px-4 py-3 border-r border-b border-black bg-gray-300"><?php echo htmlspecialchars($materiel['specialite']); ?></td>
                                <td class="px-4 py-3 border-r border-b border-black bg-gray-300"><?php echo htmlspecialchars($materiel['email']); ?></td>
                                <td class="px-4 py-3 border-r border-b border-black bg-gray-300"><?php echo htmlspecialchars($materiel['type_materiel']); ?></td>
                                <td class="px-4 py-3 border-r border-b border-black bg-gray-300"><?php echo htmlspecialchars($materiel['request_start_time']); ?></td>
                                <td class="px-4 py-3 border-r border-b border-black bg-gray-300"><?php echo htmlspecialchars($materiel['request_end_time']); ?></td>
                                <td class="px-4 py-3 border-r border-b border-black bg-gray-300">
                                    <select name="status[<?php echo $materiel['matricule']; ?>_<?php echo $materiel['id_materiel']; ?>]" class="px-2 py-1 rounded border mt-4 text-sm">
                                        <option value="en cours" <?php echo $materiel['status'] == 'en cours' ? 'selected' : ''; ?>>En cours</option>
                                        <option value="accepté" <?php echo $materiel['status'] == 'accepté' ? 'selected' : ''; ?>>Accepté</option>
                                        <option value="refusé" <?php echo $materiel['status'] == 'refusé' ? 'selected' : ''; ?>>Refusé</option>
                                    </select>
                                </td>
                                <td class="border border-gray-600 bg-gray-300 px-4 py-2">
                                    <button type="submit" name="update_status" value="<?php echo $materiel['matricule'] . '_' . $materiel['id_materiel']; ?>" class="bg-green-500 text-white px-3 py-1 rounded mt-4 text-sm">Update Status</button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" class="text-center py-2">No records found</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </form>
        <!-- Update Message -->
        <?php if ($updateMessage): ?>
        <div class="mt-4 text-center bg-yellow-100 text-yellow-800 py-2 rounded"><?php echo htmlspecialchars($updateMessage); ?></div>
    <?php endif; ?>
</div>
</body>

</html>