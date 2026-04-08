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
        $end_datetime = date('Y-m-d H:i:s', strtotime($start_datetime) + 3600); // 1 ora di durata

        // Controllo sovrapposizioni
        $check_query = "SELECT COUNT(*) as conflicts FROM eventi 
                        WHERE (:new_start < IFNULL(end, DATE_ADD(start, INTERVAL 1 HOUR))) 
                        AND (:new_end > start)";
        $check_stmt = $db->prepare($check_query);
        $check_stmt->execute([
            ':new_start' => $start_datetime,
            ':new_end' => $end_datetime
        ]);
        $conflict_result = $check_stmt->fetch();

        if ($conflict_result && $conflict_result['conflicts'] > 0) {
            echo json_encode(['success' => false, 'error' => "Esiste già un appuntamento all'orario selezionato (durata stimata: 1 ora)."]);
            exit;
        }

        $query = "INSERT INTO eventi (title, start, end, color) VALUES (:title, :start, :end, :color)";
        $statement = $db->prepare($query);
        $success = $statement->execute([
            ':title' => $title,
            ':start' => $start_datetime,
            ':end' => $end_datetime,
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
