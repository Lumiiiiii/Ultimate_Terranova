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

            $domande  = $_POST['domande']  ?? [];
            $risposte = $_POST['risposte'] ?? [];

            $db = getDB();
            $db->beginTransaction(); // transazione: o tutto ok, o niente viene salvato

            try {
                // 1) Inserisci la visita (tabella leggera, senza colonne domande)
                $stmt = $db->prepare("INSERT INTO visite (paziente_id, data_visita, motivazione, attivita_fisica, ore_sonno, note_finali)
                                      VALUES (:paz_id, :data_v, :mot, :att_fis, :hsonno, :note_fin)");
                $stmt->execute([
                    ':paz_id'   => $paz_id,
                    ':data_v'   => $data_visita,
                    ':mot'      => !empty(trim($_POST['motivazione'] ?? '')) ? trim($_POST['motivazione']) : null,
                    ':att_fis'  => !empty(trim($_POST['attivita_fisica'] ?? '')) ? trim($_POST['attivita_fisica']) : null,
                    ':hsonno'   => !empty($_POST['ore_sonno']) ? (float)$_POST['ore_sonno'] : null,
                    ':note_fin' => !empty(trim($_POST['note_finali'] ?? '')) ? trim($_POST['note_finali']) : null
                ]);

                $visita_id = $db->lastInsertId();

                // 2) Inserisci le domande aggiuntive nella tabella normalizzata (nessun limite)
                if (!empty($domande)) {
                    $stmtDom = $db->prepare("INSERT INTO domande_aggiuntive (visita_id, numero_ordine, domanda, risposta)
                                             VALUES (:visita_id, :ordine, :domanda, :risposta)");
                    
                    foreach ($domande as $i => $domanda) {
                        $domanda  = trim($domanda ?? '');
                        $risposta = trim($risposte[$i] ?? '');
                        
                        if (!empty($domanda)) {
                            $stmtDom->execute([
                                ':visita_id' => $visita_id,
                                ':ordine'    => $i + 1,
                                ':domanda'   => $domanda,
                                ':risposta'  => !empty($risposta) ? $risposta : null
                            ]);
                        }
                    }
                }

                // 3) Inserisci Prescrizioni (Integratori)
                $integratori = $_POST['integratori'] ?? [];
                $dosaggi     = $_POST['dosaggi'] ?? [];
                $durate      = $_POST['durate'] ?? [];
                
                if (!empty($integratori)) {
                    $stmtPresc = $db->prepare("INSERT INTO prescrizioni (paziente_id, medicinale_id, visita_id, dosaggio, durata, attivo, data_inizio)
                                               VALUES (:paz_id, :med_id, :visita_id, :dosaggio, :durata, 1, CURDATE())");
                    foreach ($integratori as $idx => $med_id) {
                        if (!empty($med_id)) {
                            $stmtPresc->execute([
                                ':paz_id'    => $paz_id,
                                ':med_id'    => (int)$med_id,
                                ':visita_id' => $visita_id,
                                ':dosaggio'  => trim($dosaggi[$idx] ?? ''),
                                ':durata'    => trim($durate[$idx] ?? '')
                            ]);
                        }
                    }
                }

                // 4) Inserisci Alimenti da Evitare
                $alimenti = $_POST['alimenti'] ?? [];
                $durate_alimenti = $_POST['durate_alimenti'] ?? [];
                if (!empty($alimenti)) {
                    $stmtAlim = $db->prepare("INSERT INTO alimenti_evitare (paziente_id, lista_alimenti_id, durata, attivo) VALUES (:paz_id, :alim_id, :durata, 1)");
                    foreach ($alimenti as $idx => $alim_id) {
                        if (!empty($alim_id)) {
                            $stmtAlim->execute([
                                ':paz_id'  => $paz_id,
                                ':alim_id' => (int)$alim_id,
                                ':durata'  => trim($durate_alimenti[$idx] ?? null)
                            ]);
                        }
                    }
                }

                $db->commit();
                echo json_encode(['success' => true]);
            } catch (Exception $e) {
                $db->rollBack();
                echo json_encode(['success' => false, 'error' => 'Errore nel salvataggio della visita: ' . $e->getMessage()]);
            }
            break;

        // ── RICERCA PAZIENTI (GET) ──
        case 'search_pazienti':
            $q = $_GET['q'] ?? '';
            if (strlen(trim($q)) < 2) {
                echo json_encode(['results' => []]);
                break;
            }
            $results = $patientManager->searchPatients(trim($q));
            // Aggiungiamo checkAnamnesi per mostrare il bottone corretto nella ricerca
            foreach ($results as &$r) {
                $r['ha_anamnesi'] = $patientManager->checkAnamnesi($r['id']);
            }
            echo json_encode(['results' => $results]);
            break;

        // ── GESTIONE CATALOGO ──
        case 'create_medicinale':
            $nome = trim($_POST['nome'] ?? '');
            $tipologia = trim($_POST['tipologia'] ?? '');
            $dosaggio = trim($_POST['dosaggio_standard'] ?? '');
            if (empty($nome)) {
                echo json_encode(['success' => false, 'error' => 'Il nome è obbligatorio.']);
                break;
            }
            $db = getDB();
            $stmt = $db->prepare("INSERT INTO medicinali (nome, tipologia, dosaggio_standard, attivo) VALUES (:nome, :tipologia, :dosaggio, 1)");
            $success = $stmt->execute([':nome' => $nome, ':tipologia' => !empty($tipologia) ? $tipologia : null, ':dosaggio' => !empty($dosaggio) ? $dosaggio : null]);
            echo json_encode($success ? ['success' => true] : ['success' => false, 'error' => 'Errore nel salvataggio.']);
            break;

        case 'get_last_medicinale':
            $db = getDB();
            $stmt = $db->query("SELECT id FROM medicinali ORDER BY id DESC LIMIT 1");
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            echo json_encode($row ? ['success' => true, 'id' => $row['id']] : ['success' => false, 'error' => 'Nessun medicinale trovato.']);
            break;

        case 'get_last_alimento':
            $db = getDB();
            $stmt = $db->query("SELECT id FROM lista_alimenti ORDER BY id DESC LIMIT 1");
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            echo json_encode($row ? ['success' => true, 'id' => $row['id']] : ['success' => false, 'error' => 'Nessun alimento trovato.']);
            break;

        case 'toggle_medicinale':
            $id = $_POST['id'] ?? null;
            $attivo = isset($_POST['attivo']) ? (int)$_POST['attivo'] : null;
            if (!$id || $attivo === null) {
                echo json_encode(['success' => false, 'error' => 'Dati mancanti.']);
                break;
            }
            $db = getDB();
            $stmt = $db->prepare("UPDATE medicinali SET attivo = :attivo WHERE id = :id");
            $success = $stmt->execute([':attivo' => $attivo, ':id' => $id]);
            echo json_encode($success ? ['success' => true] : ['success' => false, 'error' => 'Errore nell\'aggiornamento.']);
            break;

        case 'create_alimento':
            $nome = trim($_POST['nome'] ?? '');
            if (empty($nome)) {
                echo json_encode(['success' => false, 'error' => 'Il nome dell\'alimento è obbligatorio.']);
                break;
            }
            $db = getDB();
            $stmt = $db->prepare("INSERT INTO lista_alimenti (nome) VALUES (:nome)");
            $success = $stmt->execute([':nome' => $nome]);
            echo json_encode($success ? ['success' => true] : ['success' => false, 'error' => 'Errore nel salvataggio.']);
            break;

        case 'delete_alimento':
            $id = $_POST['id'] ?? null;
            if (!$id) {
                echo json_encode(['success' => false, 'error' => 'ID mancante.']);
                break;
            }
            $db = getDB();
            $stmt = $db->prepare("DELETE FROM lista_alimenti WHERE id = :id");
            $success = $stmt->execute([':id' => $id]);
            echo json_encode($success ? ['success' => true] : ['success' => false, 'error' => 'Errore nell\'eliminazione.']);
            break;

        case 'finish_prescrizione':
            $id = $_POST['id'] ?? null;
            if (!$id) {
                echo json_encode(['success' => false, 'error' => 'ID mancante.']);
                break;
            }
            $db = getDB();
            $stmt = $db->prepare("UPDATE prescrizioni SET attivo = 0 WHERE id = :id");
            $success = $stmt->execute([':id' => $id]);
            echo json_encode($success ? ['success' => true] : ['success' => false, 'error' => 'Errore nell\'operazione.']);
            break;

        case 'finish_alimento':
            $id = $_POST['id'] ?? null;
            if (!$id) {
                echo json_encode(['success' => false, 'error' => 'ID mancante.']);
                break;
            }
            $db = getDB();
            $stmt = $db->prepare("UPDATE alimenti_evitare SET attivo = 0 WHERE id = :id");
            $success = $stmt->execute([':id' => $id]);
            echo json_encode($success ? ['success' => true] : ['success' => false, 'error' => 'Errore nell\'operazione.']);
            break;

        default:
            echo json_encode(['success' => false, 'error' => 'Azione non valida o non specificata.']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>
