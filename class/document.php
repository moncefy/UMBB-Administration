<?php
require_once '../class/UserHandler.php';
require_once '../class/db.php';
session_start();


if (!isset($_SESSION['username_or_matricule'])) {
    die("Error: Matricule not found in session.");
}


$document = $_POST['document'] ?? '';
$matricule = $_SESSION['username_or_matricule'];
$document_status = "en cours";

if (empty($document)) {
    echo "Error: Please select a document type.";
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

// Save document request
if (Person::savedocument($conn, $document, $created_at, $id_user, $document_status)) {
    // Redirect on success
    header("Location: ../pages/requestPage.php");
    exit();
} else {
    // Redirect on failure
    echo "Error: Failed to submit the document request.";
    exit();
}
