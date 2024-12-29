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

// Initialize sale_status filter
$sale_status_filter = $_GET['sale_status'] ?? '';
$sale_filter = $user['num_salle'] ?? '';
$speciality_filter = $_GET['specialite'] ?? '';
$level_filter = $_GET['niveau'] ?? '';
$first_char = substr($sale_filter, 0, 1);
$first_digit_of_num_salle = $_GET['num_salle'] ?? ''; 
$usersalle = fetchSalleFiltered($conn, $sale_status_filter, $sale_filter, $speciality_filter, $level_filter, $first_digit_of_num_salle);


if (!empty($usersalle) && isset($usersalle[0]['num_salle'])) {
    
    $first_num_salle = $usersalle[0]['num_salle']; 
    $first_digit_of_num_salle = substr($first_num_salle, 0, 1); 
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


function fetchSalleFiltered($conn, $sale_status, $sale_filter, $specialite, $niveau, $first_digit_of_num_salle) {
    $sql = "
        SELECT u.matricule, u.nom, u.prenom, u.group, u.specialite, u.email, u.niveau,
               s.num_salle, s.created_at, s.request_start_time, s.request_end_time, s.sale_status, s.id_salle
        FROM salle s
        INNER JOIN user u ON u.ID = s.id_user
        WHERE 1=1
    ";


    $params = [];
    $types = '';

    // Sale Status filter
    if ($sale_status) {
        $sql .= " AND s.sale_status = ?";
        $params[] = $sale_status;
        $types .= 's';
    }

    // Filter by num_salle first digit
    if ($first_digit_of_num_salle) {
        $sql .= " AND s.num_salle LIKE ?";
        $params[] = $first_digit_of_num_salle . '%'; // Match any num_salle starting with the specified digit
        $types .= 's';
    }

    // Filter by Speciality
    if ($specialite) {
        $sql .= " AND u.specialite = ?";
        $params[] = $specialite;
        $types .= 's';
    }

    // Filter by level
    if ($niveau) {
        $sql .= " AND u.niveau = ?";
        $params[] = $niveau;
        $types .= 's';
    }
    
    
    $sql .= " ORDER BY s.request_start_time DESC";

    
    $stmt = $conn->prepare($sql);
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}


function updateSalleStatusByMatriculeAndIdSalle($conn, $matricule, $id_salle, $status) {
    $sql = "
        UPDATE salle s
        INNER JOIN user u ON u.id = s.id_user
        SET s.sale_status = ? 
        WHERE u.matricule = ? AND s.id_salle = ?
    ";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssi", $status, $matricule, $id_salle);

    if ($stmt->execute()) {
        $stmt->close();
        return true;
    } else {
        $stmt->close();
        return false;
    }
}
?>
<?php $num_salle = isset($_GET['num_salle']) ? $_GET['num_salle'] : ''; ?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Rooms</title>
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
                    class="flex items-center justify-center w-full p-3 rounded-lg text-white hover:bg-gray-100 dark:hover:bg-gray-700 group-hover:justify-start peer active:bg-gray-200 dark:active:bg-gray-700">
                    <i class="fas fa-tools text-purple-500"></i>
                    <span class="ml-3 hidden group-hover:inline">Gérer Matériels</span>
                </button>
            </li>
            <li>
                <button onclick="window.location.href='adminSalle.php'" 
                    class="flex items-center justify-center w-full p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 group-hover:justify-start">
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
<form method="GET" action="" class="flex space-x-4">
    <!-- Bloc Filter -->
    <div class="flex items-center">
        <label for="bloc" class="text-white mt-8">Needed Bloc</label>
        <select id="bloc" name="num_salle" class="ml-9 px-2 py-1 rounded border mt-8 text-sm">
            <option value="">-- Select Bloc --</option>
            <option value="">All</option>
            <option value="4" <?php echo ($num_salle === '4') ? 'selected' : ''; ?>>Bloc 4</option>
            <option value="5" <?php echo ($num_salle === '5') ? 'selected' : ''; ?>>Bloc 5</option>
        </select>
    </div>

    <!-- Speciality Filter -->
    <div class="flex items-center">
        <label for="specialite" class="text-white mt-8">Speciality</label>
        <select id="specialite" name="specialite" class="ml-9 px-2 py-1 rounded border mt-8 text-sm">
            <option value="">-- Select Speciality --</option>
            <option value="">All</option>
            <option value="ING" <?php echo ($speciality_filter === 'ING') ? 'selected' : ''; ?>>ING</option>
            <option value="LMD" <?php echo ($speciality_filter === 'LMD') ? 'selected' : ''; ?>>LMD</option>
            <option value="LMD-ISIL" <?php echo ($speciality_filter === 'LMD') ? 'selected' : ''; ?>>LMD-ISIL</option>
            <option value="LMD-SI" <?php echo ($speciality_filter === 'LMD') ? 'selected' : ''; ?>>LMD-SI</option>
        </select>
    </div>

    <!-- Level Filter -->
    <div class="flex items-center">
        <label for="niveau" class="text-white mt-8">Level</label>
        <select id="niveau" name="niveau" class="ml-9 px-2 py-1 rounded border mt-8 text-sm">
            <option value="">-- Select Level --</option>
            <option value="">All</option>
            <option value="L1" <?php echo ($level_filter === 'L1') ? 'selected' : ''; ?>>L1</option>
            <option value="L2" <?php echo ($level_filter === 'L2') ? 'selected' : ''; ?>>L2</option>
            <option value="L3" <?php echo ($level_filter === 'L3') ? 'selected' : ''; ?>>L3</option>
            <option value="M1" <?php echo ($level_filter === 'M1') ? 'selected' : ''; ?>>M1</option>
            <option value="M2" <?php echo ($level_filter === 'M2') ? 'selected' : ''; ?>>M2</option>
        </select>
    </div>

    <!-- Status Filter -->
    <div class="flex items-center">
        <label for="sale_status" class="text-white mt-8">Reservation Status</label>
        <select id="sale_status" name="sale_status" class="ml-9 px-2 py-1 rounded border mt-8 text-sm">
            <option value="">-- Select Status --</option>
            <option value="">All</option>
            <option value="en cours" <?php echo ($sale_status_filter === 'en cours') ? 'selected' : ''; ?>>en cours</option>
            <option value="accepté" <?php echo ($sale_status_filter === 'accepté') ? 'selected' : ''; ?>>accepté</option>
            <option value="refusé" <?php echo ($sale_status_filter === 'refusé') ? 'selected' : ''; ?>>refusé</option>
        </select>
    </div>

    <div class="flex items-end">
        <button type="submit" class="bg-green-500 text-white px-3 py-1 rounded mt-8 text-sm">Filter</button>
    </div>
</form>

<div class="overflow-x-auto p-4">
    <form method="POST" action="" class="p-4">
    <table class="table-auto bg-gray-300 w-full text-sm text-left shadow-lg rounded-lg border border-gray-300 mt-6">
            <thead>
            <tr class="bg-gray-700 text-white">
                    <th class="bg-gray-800 px-4 py-3 border-r border-b border-black">Matricule</th>
                    <th class="bg-gray-800 px-4 py-3 border-r border-b border-black">Name</th>
                    <th class="bg-gray-800 px-4 py-3 border-r border-b border-black">Prenom</th>
                    <th class="bg-gray-800 px-4 py-3 border-r border-b border-black">Group</th>
                    <th class="bg-gray-800 px-4 py-3 border-r border-b border-black">Specialite</th>
                    <th class="bg-gray-800 px-4 py-3 border-r border-b border-black">Email</th>
                    <th class="bg-gray-800 px-4 py-3 border-r border-b border-black">Niveau</th>
                    <th class="bg-gray-800 px-4 py-3 border-r border-b border-black">Room Number</th>
                    <th class="bg-gray-800 px-4 py-3 border-r border-b border-black">Start Time</th>
                    <th class="bg-gray-800 px-4 py-3 border-r border-b border-black">End Time</th>
                    <th class="bg-gray-800 px-4 py-3 border-r border-b border-black">Reservation Status</th>
                    <th class="bg-gray-800 px-4 py-3 border-r border-b border-black">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($usersalle as $user): ?>
                    <tr class="bg-gray-700 text-black">
                        <td class="px-4 py-3 border-r border-b border-black bg-gray-300"><?php echo htmlspecialchars($user['matricule']); ?></td>
                        <td class="px-4 py-3 border-r border-b border-black bg-gray-300"><?php echo htmlspecialchars($user['nom']); ?></td>
                        <td class="px-4 py-3 border-r border-b border-black bg-gray-300"><?php echo htmlspecialchars($user['prenom']); ?></td>
                        <td class="px-4 py-3 border-r border-b border-black bg-gray-300"><?php echo htmlspecialchars($user['group']); ?></td>
                        <td class="px-4 py-3 border-r border-b border-black bg-gray-300"><?php echo htmlspecialchars($user['specialite']); ?></td>
                        <td class="px-4 py-3 border-r border-b border-black bg-gray-300"><?php echo htmlspecialchars($user['email']); ?></td>
                        <td class="px-4 py-3 border-r border-b border-black bg-gray-300"><?php echo htmlspecialchars($user['niveau']); ?></td>
                        <td class="px-4 py-3 border-r border-b border-black bg-gray-300"><?php echo htmlspecialchars($user['num_salle']); ?></td>
                        <td class="px-4 py-3 border-r border-b border-black bg-gray-300"><?php echo htmlspecialchars($user['request_start_time']); ?></td>
                        <td class="px-4 py-3 border-r border-b border-black bg-gray-300"><?php echo htmlspecialchars($user['request_end_time']); ?></td>
                        <td class="px-4 py-3 border-r border-b border-black bg-gray-300">
                            <select name="status[<?php echo $user['matricule'] . '_' . $user['id_salle']; ?>]"
                            class="px-2 py-1 rounded border mt-4 text-sm">
                                <option value="en cours" <?php echo ($user['sale_status'] === 'en cours') ? 'selected' : ''; ?>>En cours</option>
                                <option value="accepté" <?php echo ($user['sale_status'] === 'accepté') ? 'selected' : ''; ?>>Accepté</option>
                                <option value="refusé" <?php echo ($user['sale_status'] === 'refusé') ? 'selected' : ''; ?>>Refusé</option>
                            </select>
                        </td>
                        <td class="border border-gray-600 bg-gray-300 px-4 py-2">
                            <button type="submit" name="update_status" value="<?php echo $user['matricule'] . '_' . $user['id_salle']; ?>"
                            class="bg-green-500 text-white px-3 py-1 rounded mt-4 text-sm">
                                Mettre à jour
                            </button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </form>
</div>


    <!-- Update Message -->
    <?php if ($updateMessage): ?>
        <div class="mt-4 text-center bg-yellow-100 text-yellow-800 py-2 rounded"><?php echo htmlspecialchars($updateMessage); ?></div>
    <?php endif; ?>
</div>
</body>
</html>