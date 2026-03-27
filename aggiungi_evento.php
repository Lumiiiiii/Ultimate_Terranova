<?php
// aggiungi_evento.php
require_once __DIR__ . '/config/database.php';

session_start();

// Controllo sicurezza
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Content-Type: application/json', true, 401);
    echo json_encode(['success' => false, 'error' => 'Non autorizzato']);
    exit;
}

try {
    $db = getDB();
    
    if (isset($_POST['title'])) {
        $title = trim($_POST['title']);
        $date = $_POST['start'];
        $hour = $_POST['hour'] ?? '09:00'; // Default if not provided
        $color = $_POST['color'] ?? '#2ecc71'; // Default color

        // Combine date and hour for the 'start' datetime column
        $start_datetime = $date . ' ' . $hour . ':00';

        $query = "INSERT INTO eventi (title, start, color) VALUES (:title, :start, :color)";
        $statement = $db->prepare($query);
        $success = $statement->execute([
            ':title' => $title,
            ':start' => $start_datetime,
            ':color' => $color
        ]);
        
        header('Content-Type: application/json');
        echo json_encode(['success' => $success]);
    } else {
        throw new Exception('Titolo mancante');
    }
} catch (Exception $e) {
    header('Content-Type: application/json', true, 500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>
