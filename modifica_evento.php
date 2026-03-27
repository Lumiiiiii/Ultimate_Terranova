<?php
// modifica_evento.php
require_once __DIR__ . '/config/database.php';

session_start();

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Content-Type: application/json', true, 401);
    echo json_encode(['success' => false, 'error' => 'Non autorizzato']);
    exit;
}

try {
    $db = getDB();
    
    if (isset($_POST['id']) && isset($_POST['title'])) {
        $id = $_POST['id'];
        $title = trim($_POST['title']);
        $date = $_POST['start'];
        $hour = $_POST['hour'] ?? '09:00';
        $color = $_POST['color'] ?? '#2ecc71';

        $start_datetime = $date . ' ' . $hour . ':00';

        $query = "UPDATE eventi SET title = :title, start = :start, color = :color WHERE id = :id";
        $statement = $db->prepare($query);
        $success = $statement->execute([
            ':id' => $id,
            ':title' => $title,
            ':start' => $start_datetime,
            ':color' => $color
        ]);
        
        header('Content-Type: application/json');
        echo json_encode(['success' => $success]);
    } else {
        throw new Exception('Dati mancanti');
    }
} catch (Exception $e) {
    header('Content-Type: application/json', true, 500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>
