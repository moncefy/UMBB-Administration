<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Documents</title>
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


if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../pages/login.php"); 
    exit();
}


$adminUsername = $_SESSION['username'] ?? null; // Use 'username' for admins

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

// Initialisation des variables de filtre
$filterTitle = $_GET['filter_title'] ?? '';
$filterSpecialite = $_GET['filter_specialite'] ?? '';
$filterNiveau = $_GET['filter_niveau'] ?? '';
$filterStatus = $_GET['filter_status'] ?? '';

$sql = "
    SELECT u.matricule, u.nom, u.prenom, u.group, u.specialite, u.email, u.niveau, d.id_document, d.document_status, d.title, d.created_at
    FROM user u
    INNER JOIN document d ON u.ID = d.id_user
    WHERE 1
";

if ($filterTitle) {
    $sql .= " AND d.title LIKE ?";
}

if ($filterSpecialite) {
    $sql .= " AND u.specialite LIKE ?";
}

if ($filterNiveau) {
    $sql .= " AND u.niveau LIKE ?";
}

if ($filterStatus) {
    $sql .= " AND d.document_status LIKE ?";
}

$stmt = $conn->prepare($sql);
$params = [];
$types = '';

if ($filterTitle) {
    $filterTitle = "%$filterTitle%";
    $params[] = $filterTitle;
    $types .= 's';
}

if ($filterSpecialite) {
    $filterSpecialite = "%$filterSpecialite%";
    $params[] = $filterSpecialite;
    $types .= 's';
}

if ($filterNiveau) {
    $filterNiveau = "%$filterNiveau%";
    $params[] = $filterNiveau;
    $types .= 's';
}

if ($filterStatus) {
    $filterStatus = "%$filterStatus%";
    $params[] = $filterStatus;
    $types .= 's';
}

if ($types) {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$result = $stmt->get_result();
$documentsUtilisateur = $result->fetch_all(MYSQLI_ASSOC);

$messageMiseAJour = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $data = explode('_', $_POST['update_status']);
    $matricule = $data[0];
    $id_document = $data[1];
    $status_key = $matricule . '_' . $id_document;
    $status = $_POST['status'][$status_key] ?? null;

    if ($status) {
        if (updateDocumentStatusByMatriculeAndDocumentId($conn, $matricule, $id_document, $status)) {
            header("Location: ../pages/adminDocument.php");
            exit;
        } else {
            $messageMiseAJour = "Erreur lors de la mise à jour du statut du document.";
        }
    } else {
        $messageMiseAJour = "Aucun statut sélectionné pour le matricule: " . htmlspecialchars($matricule);
    }
}

function updateDocumentStatusByMatriculeAndDocumentId($conn, $matricule, $id_document, $status) {
    $sql = "
        UPDATE document d
        INNER JOIN user u ON u.ID = d.id_user
        SET d.document_status = ?
        WHERE u.matricule = ? 
          AND d.id_document = ?
    ";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssi", $status, $matricule, $id_document);
    $result = $stmt->execute();
    $stmt->close();
    return $result;
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
                    class="flex items-center justify-center w-full p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 group-hover:justify-start">
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
            <!--<li>
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

<!-- Main Content Area -->
<div class="w-full h-full ml-48 p-20 bg-cover bg-fixed overflow-y-auto min-h-screen" style="background: linear-gradient(35deg, #4B5563, #D1D5DB);" >
    <!-- Filter Form -->
    <form action="" method="GET" class="flex space-x-4">
        <div class="flex items-center">
            <label for="filter_title" class="text-white mt-8">Titre</label>
            <select name="filter_title" id="filter_title" class="ml-9 px-2 py-1 rounded border mt-8 text-sm">
                <option value="">-- Select Needed Document --</option>
                <option value="">All</option>
                <option value="releves de notes" <?php echo $filterTitle == 'releves de notes' ? 'selected' : ''; ?>>Relevés de Notes</option>
                <option value="certificat scolarité" <?php echo $filterTitle == 'certificat scolarité' ? 'selected' : ''; ?>>Certificat de Scolarité</option>
                <option value="carte de parking" <?php echo $filterTitle == 'carte de parking' ? 'selected' : ''; ?>>Carte de parking</option>
                <option value="attestation de bonne conduite" <?php echo $filterTitle == 'attestation de bonne conduite' ? 'selected' : ''; ?>>Attestation de Bonne Conduite</option>
                <option value="demande personnalisée" <?php echo $filterTitle == 'demande personnalisée' ? 'selected' : ''; ?>>Demande Personnalisée</option>
            </select>
        </div>
        <div class="flex items-center">
            <label for="filter_specialite" class="text-white mt-8">Spécialité</label>
            <select name="filter_specialite" id="filter_specialite" class="ml-2 px-2 py-1 rounded border mt-8 text-sm">
                <option value="">-- Select Speciality --</option>
                <option value="">All</option>
                <option value="LMD" <?php echo $filterSpecialite == 'LMD' ? 'selected' : ''; ?>>LMD</option>
                <option value="ING" <?php echo $filterSpecialite == 'ING' ? 'selected' : ''; ?>>ING</option>
                <option value="LMD-ISIL" <?php echo $filterSpecialite == 'LMD-ISIL' ? 'selected' : ''; ?>>LMD-ISIL</option>
                <option value="LMD-SI" <?php echo $filterSpecialite == 'LMD-SI' ? 'selected' : ''; ?>>LMD-SI</option>
            </select>
        </div>
        <div class="flex items-center">
            <label for="filter_niveau" class="text-white mt-8">Niveau</label>
            <select name="filter_niveau" id="filter_niveau" class="ml-2 px-2 py-1 rounded border mt-8 text-sm">
                <option value="">-- Select Level --</option>
                <option value="">All</option>
                <option value="L1" <?php echo $filterNiveau == 'L1' ? 'selected' : ''; ?>>L1</option>
                <option value="L2" <?php echo $filterNiveau == 'L2' ? 'selected' : ''; ?>>L2</option>
                <option value="L3" <?php echo $filterNiveau == 'L3' ? 'selected' : ''; ?>>L3</option>
            </select>
        </div>
        <div class="flex items-center">
            <label for="filter_status" class="text-white mt-8">Document Status</label>
            <select name="filter_status" id="filter_status" class="ml-2 px-2 py-1 rounded border mt-8 text-sm">
                <option value="">-- Select Status --</option>
                <option value="">All</option>
                <option value="en cours" <?php echo $filterStatus == 'en cours' ? 'selected' : ''; ?>>En cours</option>
                <option value="accepté" <?php echo $filterStatus == 'accepté' ? 'selected' : ''; ?>>Accepté</option>
                <option value="refusé" <?php echo $filterStatus == 'refusé' ? 'selected' : ''; ?>>Refusé</option>
            </select>
        </div>
        <button type="submit" class="bg-green-500 text-white px-3 py-1 rounded mt-8 text-sm">Filtrer</button>
    </form>

    <!-- Table for Document Requests -->
    <div class="overflow-x-auto p-4">
        <form action="" method="POST">
        <table class="table-auto bg-gray-300 w-full text-sm text-left shadow-lg rounded-lg border border-gray-300 mt-6">
                <thead>
                    <tr class="bg-gray-700 text-white">
                        <th class="bg-gray-800 px-4 py-3 border-r border-b border-black">Matricule</th>
                        <th class="bg-gray-800 px-4 py-3 border-r border-b border-black">Nom</th>
                        <th class="bg-gray-800 px-4 py-3 border-r border-b border-black">Prénom</th>
                        <th class="bg-gray-800 px-4 py-3 border-r border-b border-black">Titre</th>
                        <th class="bg-gray-800 px-4 py-3 border-r border-b border-black">Specialite</th>
                        <th class="bg-gray-800 px-4 py-3 border-r border-b border-black">Niveau</th>
                        <th class="bg-gray-800 px-4 py-3 border-r border-b border-black">Statut</th>
                        <th class="bg-gray-800 px-4 py-3 border-r border-b border-black">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($documentsUtilisateur as $doc): ?>
                        <tr class="bg-gray-100">
                            <td class="px-4 py-3 border-r border-b border-black bg-gray-300"><?php echo htmlspecialchars($doc['matricule']); ?></td>
                            <td class="px-4 py-3 border-r border-b border-black bg-gray-300"><?php echo htmlspecialchars($doc['nom']); ?></td>
                            <td class="px-4 py-3 border-r border-b border-black bg-gray-300"><?php echo htmlspecialchars($doc['prenom']); ?></td>
                            <td class="px-4 py-3 border-r border-b border-black bg-gray-300"><?php echo htmlspecialchars($doc['title']); ?></td>
                            <td class="px-4 py-3 border-r border-b border-black bg-gray-300"><?php echo htmlspecialchars($doc['specialite']); ?></td>
                            <td class="px-4 py-3 border-r border-b border-black bg-gray-300"><?php echo htmlspecialchars($doc['niveau']); ?></td>
                            <td class="px-4 py-3 border-r border-b border-black bg-gray-300">
                                <select name="status[<?php echo $doc['matricule'] . '_' . $doc['id_document']; ?>]" class="px-2 py-1 rounded border mt-4 text-sm">
                                    <option value="en cours" <?php echo $doc['document_status'] == 'en cours' ? 'selected' : ''; ?>>En cours</option>
                                    <option value="accepté" <?php echo $doc['document_status'] == 'accepté' ? 'selected' : ''; ?>>Accepté</option>
                                    <option value="refusé" <?php echo $doc['document_status'] == 'refusé' ? 'selected' : ''; ?>>Refusé</option>
                                </select>
                            </td>
                            <td class="border border-gray-600 bg-gray-300 px-4 py-2">
                                <button type="submit" name="update_status" value="<?php echo $doc['matricule'] . '_' . $doc['id_document']; ?>" class="bg-green-500 text-white px-3 py-1 rounded mt-4 text-sm">Mettre à jour</button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                </tbody>
            </table>
        </form>
    </div>
        <!-- Update Message -->
        <?php if ($messageMiseAJour): ?>
        <div class="mt-4 text-center bg-yellow-100 text-yellow-800 py-2 rounded"><?php echo htmlspecialchars($messageMiseAJour); ?></div>
    <?php endif; ?>
</div>
</body>
</html>