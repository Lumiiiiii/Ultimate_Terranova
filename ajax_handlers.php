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

        // --- GESTIONE ANAMNESI ---
        case 'create_anamnesi':
            $paz_id = $_POST['paziente_id'] ?? null;
            if (!$paz_id) {
                echo json_encode(['success' => false, 'error' => 'ID paziente mancante.']);
                break;
            }

            // Prepared statement per inserire tutti i campi
            $queryText = "INSERT INTO anamnesi (
                paziente_id, allergie_intolleranze, farmaci_assunti, patologie_pregresse, 
                interventi_chirurgici, esami_clinici_recenti, alcol, fumo, 
                traumi_o_fratture, note_aggiuntive, altezza, peso
            ) VALUES (
                :paziente_id, :allergie, :farmaci, :patologie, 
                :interventi, :esami, :alcol, :fumo, 
                :traumi, :note_aggiuntive, :altezza, :peso
            )";

            $db = getDB();
            $stmt = $db->prepare($queryText);
            
            $success = $stmt->execute([
                ':paziente_id' => $paz_id,
                ':allergie'   => $_POST['allergie_intolleranze'] ?? null,
                ':farmaci'    => $_POST['farmaci_assunti'] ?? null,
                ':patologie'  => $_POST['patologie_pregresse'] ?? null,
                ':interventi' => $_POST['interventi_chirurgici'] ?? null,
                ':esami'      => $_POST['esami_clinici_recenti'] ?? null,
                ':alcol'      => $_POST['alcol'] ?? null,
                ':fumo'       => $_POST['fumo'] ?? null,
                ':traumi'     => $_POST['traumi_o_fratture'] ?? null,
                ':note_aggiuntive' => $_POST['note_aggiuntive'] ?? null,
                ':altezza'    => !empty($_POST['altezza']) ? (int)$_POST['altezza'] : null,
                ':peso'       => !empty($_POST['peso']) ? (float)$_POST['peso'] : null
            ]);

            if ($success) {
                echo json_encode(['success' => true]);
            } else {
                echo json_encode(['success' => false, 'error' => 'Errore nel salvataggio dell\'anamnesi.']);
            }
            break;
            
            
        default:
            echo json_encode(['success' => false, 'error' => 'Azione non valida o non specificata.']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>
