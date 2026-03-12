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

        case 'create_paziente':
            $data = [
                'nome_cognome' => trim($_POST['nome_cognome'] ?? ''),
                'data_nascita' => $_POST['data_nascita'] ?? null,
                'telefono'     => trim($_POST['telefono'] ?? ''),
                'indirizzo'    => trim($_POST['indirizzo'] ?? ''),
                'email'        => trim($_POST['email'] ?? ''),
                'professione'  => trim($_POST['professione'] ?? '')
            ];

            // Validazione
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
            if ($newId) {
                echo json_encode(['success' => true, 'id' => $newId]);
            } else {
                echo json_encode(['success' => false, 'error' => 'Errore durante il salvataggio del paziente.']);
            }
            break;

        case 'delete_paziente':
            $paz_id = $_POST['id'] ?? null;
            if (!$paz_id) {
                echo json_encode(['success' => false, 'error' => 'ID paziente mancante.']);
                break;
            }
            $success = $patientManager->deletePatient($paz_id);
            if ($success) {
                echo json_encode(['success' => true]);
            } else {
                echo json_encode(['success' => false, 'error' => 'Errore durante l\'eliminazione del paziente.']);
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
            
        // --- GESTIONE MODIFICA ANAMNESI DA "VISITA_NUOVA.PHP" (ACCORDION RAPIDO) ---
        case 'update_anamnesi_rapido':
            $paz_id = $_POST['paziente_id'] ?? null;
            if (!$paz_id) {
                echo json_encode(['success' => false, 'error' => 'ID paziente mancante.']);
                break;
            }

            // Prepariamo l'update statement toccando i soli campi esposti nel form rapido.
            $queryText = "UPDATE anamnesi SET 
                allergie_intolleranze = :allergie,
                farmaci_assunti = :farmaci,
                patologie_pregresse = :patologie,
                interventi_chirurgici = :interventi,
                traumi_o_fratture = :traumi,
                note_aggiuntive = :note,
                altezza = :altezza,
                peso = :peso,
                alcol = :alcol,
                fumo = :fumo
                WHERE paziente_id = :paz_id";

            $db = getDB();
            $stmt = $db->prepare($queryText);
            
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

            if ($success) {
                echo json_encode(['success' => true]);
            } else {
                echo json_encode(['success' => false, 'error' => 'Errore nell\'aggiornamento dell\'anamnesi.']);
            }
            break;

            
        // --- GESTIONE VISITE DI CONTROLLO ---
        // --- PASSAGGIO 3: GESTORE SQL PER LE NUOVE VISITE DI CONTROLLO ---
        // Quando il server riceve 'create_visita' nell'indice action del POST, il codice devìa ed entra in questo Case.
        case 'create_visita':
            
            // 1. Estrapolazione Primaria Sicura (Estrazione Dati)
            // Lavoriamo sempre i nodi essenziali che potrebbero essere manomessi e blocchiamo tutto se assenti
            // L'operatore ?? restituisce null se l'indice dell'array POST non esiste (es se disattivano l'input form malignamente)
            $paz_id = $_POST['paziente_id'] ?? null;
            $data_visita = $_POST['data_visita'] ?? null;
            
            // Se non mi viene fornito a che paziente appartiene la visita O non c'è una data... NON SI FA NIENTE.
            if (!$paz_id || !$data_visita) {
                // Restituiamo una stringa JSON di fallimento per destare l'alert su lato Frontend (Javascript)
                echo json_encode(['success' => false, 'error' => 'Campi vitali mancanti: ID Paziente o Data Visita.']);
                // Interrompe bruscamente il ciclo 'case' e termina script.
                break;
            }

            // 2. Progettazione della Rete Parametrica (Prepared Statement)
            // Qui redigiamo l'architettura cruda SQL "INSERT INTO". 
            // Invece di concatenare le stringhe da form $_POST diretti in pasto (Vulnerabile a distruttivi SQL Injection), 
            // incaselliamo la query usando i "Segnaposti/Alias" (Placeholders), quelli che iniziano con due punti (:nomecampofinto)
            $queryText = "INSERT INTO visite (
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
            )";

            // 3. Prepariamo lo schema e connettiamoci al DB (Meccanica Architetturale Backend)
            $db = getDB(); // Instanciamo la connessione viva al DB (Questa funzione globale è dichiarata dentro database.php integrato in alto)
            $stmt = $db->prepare($queryText); // Il PDO Object "pre-compila" lo scheletro della query nel buffer senza ancora sparare alcun valore
            
            // 4. Esecuzione Parametrizzata Esatta
            // Questo execute fa il "Binding" (Incollaggio) degli alias della query con il vero input dell'utente e lo spara fuori con un Boolean Vero o Falso di successo.
            // Contemporaneamente compie "Sanitization": disarmando apici maligni o codici virali.
            // NB: Laddove il campo possa essere non scritto, lo "forziamo" a far emettere "null" piuttosto che stringa vuota, tenendo pulito il DB.
            $success = $stmt->execute([
                ':paz_id'     => $paz_id, 
                ':data_v'     => $data_visita, // Variabili controllate a monte, si bindano così.
                
                // Tutti i parametri Testuali normali se malauguratamente vuoti assumono NULL (null coalescing). Rispetta i campi TEXT MySQL
                ':mot'        => !empty(trim($_POST['motivazione'])) ? trim($_POST['motivazione']) : null,
                ':conc'       => !empty(trim($_POST['concentrazione'])) ? trim($_POST['concentrazione']) : null,
                ':stato_emo'  => !empty(trim($_POST['stato_emotivo'])) ? trim($_POST['stato_emotivo']) : null,
                ':att_fis'    => !empty(trim($_POST['attivita_fisica'])) ? trim($_POST['attivita_fisica']) : null,
                ':idrat'      => !empty(trim($_POST['idratazione'])) ? trim($_POST['idratazione']) : null,
                ':qsonno'     => !empty(trim($_POST['qualita_sonno_percepita'])) ? trim($_POST['qualita_sonno_percepita']) : null,
                
                // Il parametro Ore Sonno è un numero "DECIMAL(4,2)", se è vuoto deve emettere NULL, altrimenti lo castiamo a puro Float prima di gettarlo nel DB
                ':hsonno'     => !empty($_POST['ore_sonno']) ? (float)$_POST['ore_sonno'] : null,
                
                ':reg_int'    => !empty(trim($_POST['regolarita_intestinale'])) ? trim($_POST['regolarita_intestinale']) : null,
                ':appetito'   => !empty(trim($_POST['appetito_e_digestione'])) ? trim($_POST['appetito_e_digestione']) : null,
                ':diff_sonno' => !empty(trim($_POST['difficolta_addormentarsi_risvegli_notturni'])) ? trim($_POST['difficolta_addormentarsi_risvegli_notturni']) : null,
                
                // Stress e Energia sono Interi da 1 a 10 (Campi INT). Anche loro se tralasciati devono essere NULL. Altrimenti conversione esplicita "(int)"
                ':stress'     => !empty($_POST['livello_stress']) ? (int)$_POST['livello_stress'] : null,
                ':energia'    => !empty($_POST['livello_energia']) ? (int)$_POST['livello_energia'] : null,
                
                ':supporti'   => !empty(trim($_POST['supporti_in_uso'])) ? trim($_POST['supporti_in_uso']) : null,
                ':alim'       => !empty(trim($_POST['alimentazione_recente'])) ? trim($_POST['alimentazione_recente']) : null,
                ':note_fin'   => !empty(trim($_POST['note_finali'])) ? trim($_POST['note_finali']) : null
            ]);

            // 5. Epilogo di Ritorno per il Fetch Javascript (Risposta JSON)
            // Valutando il boolean uscito dal .execute
            if ($success) {
                // VaTuttoBene: codifichiamo una stringa di ok
                echo json_encode(['success' => true]);
            } else {
                // Errore: la insert è fallita dal server db.
                echo json_encode(['success' => false, 'error' => 'Errore fatale del Server MySQL: Salvataggio visita compromesso.']);
            }
            // Fine ramo, stacca il blocco di codice.
            break;


            
        default:
            echo json_encode(['success' => false, 'error' => 'Azione non valida o non specificata.']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>
