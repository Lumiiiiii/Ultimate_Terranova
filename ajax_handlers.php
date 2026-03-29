<?php
// ajax_handlers.php — Gestisce tutte le chiamate AJAX del gestionale (Pazienti, Anamnesi, Visite)
header('Content-Type: application/json');

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/Patient.php';

$patientManager = new Patient();
$action = $_POST['action'] ?? $_GET['action'] ?? '';

try {
    switch ($action) {
        
        // ── AGGIORNAMENTO PAZIENTE ──
        case 'update_patient':
            $success = $patientManager->updatePatient($_POST['id'], $_POST);
            if ($success) {
                echo json_encode(['success' => true]);
            } else {
                echo json_encode(['success' => false, 'error' => 'Errore durante l\'aggiornamento nel database.']);
            }
            break;

        // ── CREAZIONE PAZIENTE ──
        case 'create_paziente':
            $data = [
                'nome_cognome' => trim($_POST['nome_cognome'] ?? ''),
                'data_nascita' => $_POST['data_nascita'] ?? null,
                'telefono'     => trim($_POST['telefono'] ?? ''),
                'indirizzo'    => trim($_POST['indirizzo'] ?? ''),
                'email'        => trim($_POST['email'] ?? ''),
                'professione'  => trim($_POST['professione'] ?? '')
            ];

            // Validazione campi obbligatori
            if (empty($data['nome_cognome'])) {
                echo json_encode(['success' => false, 'error' => 'Il campo Nome e Cognome è obbligatorio.']);
                break;
            }
            if (empty($data['data_nascita'])) {
                echo json_encode(['success' => false, 'error' => 'Il campo Data di Nascita è obbligatorio.']);
                break;
            }

            // Controllo duplicati
            if ($patientManager->isDuplicate($data['nome_cognome'], $data['data_nascita'])) {
                echo json_encode(['success' => false, 'error' => 'Esiste già un paziente con questi dati (Nome, Cognome e Data di nascita).']);
                break;
            }

            $newId = $patientManager->createPatient($data);
            echo json_encode($newId ? ['success' => true, 'id' => $newId] : ['success' => false, 'error' => 'Errore durante il salvataggio del paziente.']);
            break;

        // ── ELIMINAZIONE PAZIENTE ──
        case 'delete_paziente':
            $paz_id = $_POST['id'] ?? null;
            if (!$paz_id) {
                echo json_encode(['success' => false, 'error' => 'ID paziente mancante.']);
                break;
            }
            $success = $patientManager->deletePatient($paz_id);
            echo json_encode($success ? ['success' => true] : ['success' => false, 'error' => 'Errore durante l\'eliminazione del paziente.']);
            break;

        // ── CREAZIONE ANAMNESI (PRIMA VISITA) ──
        case 'create_anamnesi':
            $paz_id = $_POST['paziente_id'] ?? null;
            if (!$paz_id) {
                echo json_encode(['success' => false, 'error' => 'ID paziente mancante.']);
                break;
            }

            $db = getDB();
            $stmt = $db->prepare("INSERT INTO anamnesi (
                paziente_id, allergie_intolleranze, farmaci_assunti, patologie_pregresse, 
                interventi_chirurgici, esami_clinici_recenti, alcol, fumo, 
                traumi_o_fratture, note_aggiuntive, altezza, peso
            ) VALUES (
                :paziente_id, :allergie, :farmaci, :patologie, 
                :interventi, :esami, :alcol, :fumo, 
                :traumi, :note_aggiuntive, :altezza, :peso
            )");
            
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

            echo json_encode($success ? ['success' => true] : ['success' => false, 'error' => 'Errore nel salvataggio dell\'anamnesi.']);
            break;
            
        // ── AGGIORNAMENTO RAPIDO ANAMNESI (da visita_nuova.php) ──
        case 'update_anamnesi_rapido':
            $paz_id = $_POST['paziente_id'] ?? null;
            if (!$paz_id) {
                echo json_encode(['success' => false, 'error' => 'ID paziente mancante.']);
                break;
            }

            $db = getDB();
            $stmt = $db->prepare("UPDATE anamnesi SET 
                allergie_intolleranze = :allergie, farmaci_assunti = :farmaci,
                patologie_pregresse = :patologie, interventi_chirurgici = :interventi,
                traumi_o_fratture = :traumi, note_aggiuntive = :note,
                altezza = :altezza, peso = :peso, alcol = :alcol, fumo = :fumo
                WHERE paziente_id = :paz_id");
            
            $success = $stmt->execute([
                ':paz_id'     => $paz_id,
                ':allergie'   => !empty(trim($_POST['allergie_intolleranze'])) ? trim($_POST['allergie_intolleranze']) : null,
                ':farmaci'    => !empty(trim($_POST['farmaci_assunti'])) ? trim($_POST['farmaci_assunti']) : null,
                ':patologie'  => !empty(trim($_POST['patologie_pregresse'])) ? trim($_POST['patologie_pregresse']) : null,
                ':interventi' => !empty(trim($_POST['interventi_chirurgici'])) ? trim($_POST['interventi_chirurgici']) : null,
                ':traumi'     => !empty(trim($_POST['traumi_o_fratture'])) ? trim($_POST['traumi_o_fratture']) : null,
                ':note'       => !empty(trim($_POST['note_aggiuntive'])) ? trim($_POST['note_aggiuntive']) : null,
                ':altezza'    => !empty($_POST['altezza']) ? (int)$_POST['altezza'] : null,
                ':peso'       => !empty($_POST['peso']) ? (float)$_POST['peso'] : null,
                ':alcol'      => !empty(trim($_POST['alcol'])) ? trim($_POST['alcol']) : null,
                ':fumo'       => !empty(trim($_POST['fumo'])) ? trim($_POST['fumo']) : null
            ]);

            echo json_encode($success ? ['success' => true] : ['success' => false, 'error' => 'Errore nell\'aggiornamento dell\'anamnesi.']);
            break;
            
        // ── CREAZIONE VISITA DI CONTROLLO ──
        case 'create_visita':
            $paz_id = $_POST['paziente_id'] ?? null;
            $data_visita = $_POST['data_visita'] ?? null;
            
            if (!$paz_id || !$data_visita) {
                echo json_encode(['success' => false, 'error' => 'Campi vitali mancanti: ID Paziente o Data Visita.']);
                break;
            }

            $db = getDB();
            $stmt = $db->prepare("INSERT INTO visite (
                paziente_id, data_visita, motivazione, concentrazione, 
                stato_emotivo, attivita_fisica, idratazione, qualita_sonno_percepita, 
                ore_sonno, regolarita_intestinale, appetito_e_digestione, 
                difficolta_addormentarsi_risvegli_notturni, livello_stress, livello_energia, 
                supporti_in_uso, alimentazione_recente, note_finali
            ) VALUES (
                :paz_id, :data_v, :mot, :conc, 
                :stato_emo, :att_fis, :idrat, :qsonno, 
                :hsonno, :reg_int, :appetito, 
                :diff_sonno, :stress, :energia, 
                :supporti, :alim, :note_fin
            )");
            
            // Binding parametri con sanitizzazione: campi vuoti → NULL
            $success = $stmt->execute([
                ':paz_id'     => $paz_id, 
                ':data_v'     => $data_visita,
                ':mot'        => !empty(trim($_POST['motivazione'])) ? trim($_POST['motivazione']) : null,
                ':conc'       => !empty(trim($_POST['concentrazione'])) ? trim($_POST['concentrazione']) : null,
                ':stato_emo'  => !empty(trim($_POST['stato_emotivo'])) ? trim($_POST['stato_emotivo']) : null,
                ':att_fis'    => !empty(trim($_POST['attivita_fisica'])) ? trim($_POST['attivita_fisica']) : null,
                ':idrat'      => !empty(trim($_POST['idratazione'])) ? trim($_POST['idratazione']) : null,
                ':qsonno'     => !empty(trim($_POST['qualita_sonno_percepita'])) ? trim($_POST['qualita_sonno_percepita']) : null,
                ':hsonno'     => !empty($_POST['ore_sonno']) ? (float)$_POST['ore_sonno'] : null,
                ':reg_int'    => !empty(trim($_POST['regolarita_intestinale'])) ? trim($_POST['regolarita_intestinale']) : null,
                ':appetito'   => !empty(trim($_POST['appetito_e_digestione'])) ? trim($_POST['appetito_e_digestione']) : null,
                ':diff_sonno' => !empty(trim($_POST['difficolta_addormentarsi_risvegli_notturni'])) ? trim($_POST['difficolta_addormentarsi_risvegli_notturni']) : null,
                ':stress'     => !empty($_POST['livello_stress']) ? (int)$_POST['livello_stress'] : null,
                ':energia'    => !empty($_POST['livello_energia']) ? (int)$_POST['livello_energia'] : null,
                ':supporti'   => !empty(trim($_POST['supporti_in_uso'])) ? trim($_POST['supporti_in_uso']) : null,
                ':alim'       => !empty(trim($_POST['alimentazione_recente'])) ? trim($_POST['alimentazione_recente']) : null,
                ':note_fin'   => !empty(trim($_POST['note_finali'])) ? trim($_POST['note_finali']) : null
            ]);

            echo json_encode($success ? ['success' => true] : ['success' => false, 'error' => 'Errore nel salvataggio della visita.']);
            break;

        default:
            echo json_encode(['success' => false, 'error' => 'Azione non valida o non specificata.']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>
