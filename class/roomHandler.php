<?php
require_once '../class/UserHandler.php';
require_once '../class/db.php';
session_start();


if (!isset($_SESSION['username_or_matricule'])) {
    die("Error: Matricule not found in session.");
}


$bloc_room = $_POST['bloc-room'] ?? '';
$start_time = $_POST['startTime'] ?? '';
$end_time = $_POST['endTime'] ?? '';
$date = $_POST['date'] ?? '';
$matricule = $_SESSION['username_or_matricule'];
$document_status = "en cours";


if (empty($bloc_room) || empty($start_time) || empty($end_time) || empty($date)) {
    echo "Error: Please fill out all fields.";
    exit();
}


$request_start_time = date('Y-m-d H:i:s', strtotime("$date $start_time"));
$request_end_time = date('Y-m-d H:i:s', strtotime("$date $end_time"));

// Validate time logic (end time must be after start time)
if (strtotime($end_time) <= strtotime($start_time)) {
    echo "Error: End time must be after start time.";
    exit();
}


$db = new Database('localhost', 'root', '', 'php');
$conn = $db->getConnection();


$id_user = Person::getUserIdByMatricule($conn, $matricule);
if (empty($id_user)) {
    die("Error: User ID not found for the provided matricule.");
}

// Set created_at timestamp
$created_at = date('Y-m-d H:i:s');

// Save room request
$result = Person::saveRoomRequest($conn, $bloc_room, $created_at, $request_start_time, $request_end_time, $id_user, $document_status);

if ($result === true) {
    // Redirect on success
    header("Location: ../pages/requestPage.php?success=1");
    exit();
} else {
    // Display error if room is already reserved or any other issue occurs
    echo $result;
    exit();
}
?>