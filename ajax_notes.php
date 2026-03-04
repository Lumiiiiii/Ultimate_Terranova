<?php
session_start();
require_once __DIR__ . '/includes/Note.php';

// Controlla il login
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Non autorizzato']);
    exit;
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Ottieni il JSON inviato
    $data = json_decode(file_get_contents('php://input'), true);
    $testo = $data['testo'] ?? '';

    $noteManager = new Note();
    $result = $noteManager->updateNote($testo);

    header('Content-Type: application/json');
    if ($result) {
        echo json_encode(['success' => true]);
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Errore nel database']);
    }
}
