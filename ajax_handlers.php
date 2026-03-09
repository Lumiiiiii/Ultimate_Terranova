<?php
// ajax_handlers.php
// Questo file gesterà le chiamate AJAX generali del gestionale (Pazienti, Visite, ecc.)
header('Content-Type: application/json');

// Includiamo la connessione al database e le classi necessarie
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/Patient.php';

// Inizializziamo le classi
$patientManager = new Patient();

// Controlliamo che azione è stata richiesta dal Javascript
$action = $_POST['action'] ?? $_GET['action'] ?? '';

try {
    switch ($action) {
        
        // --- GESTIONE PAZIENTI ---
        case 'update_patient':
            // Chiamiamo la funzione updatePatient passando l'ID e tutti i campi del form
            $success = $patientManager->updatePatient($_POST['id'], $_POST);
            
            if ($success) {
                echo json_encode(['success' => true]);
            } else {
                echo json_encode(['success' => false, 'error' => 'Errore durante l\'aggiornamento nel database.']);
            }
            break;

        // In futuro, quando creeremo la classe Visit.php, aggiungeremo qui:
        // case 'create_visit':
        //     ...
        //     break;
            
        default:
            echo json_encode(['success' => false, 'error' => 'Azione non valida o non specificata.']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>
