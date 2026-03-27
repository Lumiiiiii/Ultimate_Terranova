<?php
// elimina_evento.php
require_once __DIR__ . '/config/database.php';

session_start();

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Content-Type: application/json', true, 401);
    echo json_encode(['success' => false, 'error' => 'Non autorizzato']);
    exit;
}

try {
    $db = getDB();
    
    if (isset($_POST['id'])) {
        $query = "DELETE FROM eventi WHERE id = :id";
        $statement = $db->prepare($query);
        $success = $statement->execute([':id' => $_POST['id']]);
        
        header('Content-Type: application/json');
        echo json_encode(['success' => $success]);
    } else {
        throw new Exception('ID mancante');
    }
} catch (Exception $e) {
    header('Content-Type: application/json', true, 500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>
