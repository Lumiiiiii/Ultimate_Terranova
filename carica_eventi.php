<?php
// carica_eventi.php
require_once __DIR__ . '/config/database.php';

try {
    $db = getDB();
    
    // 2. Query per recuperare gli eventi
    $query = "SELECT id, title, start, end, color FROM eventi";
    $statement = $db->prepare($query);
    $statement->execute();
    
    // Recuperiamo tutti i risultati come array associativo
    $risultati = $statement->fetchAll(PDO::FETCH_ASSOC);
    
    // 3. Comunichiamo al browser che stiamo inviando un file JSON
    header('Content-Type: application/json');
    
    // Trasformiamo l'array PHP in una stringa JSON e la stampiamo
    echo json_encode($risultati);

} catch (Exception $e) {
    // In caso di errore
    header('Content-Type: application/json', true, 500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>
