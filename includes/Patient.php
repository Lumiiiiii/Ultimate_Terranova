<?php
/**
 * Classe Patient - Gestione pazienti
 *
 * LEGENDA VARIABILI USATE NEI METODI:
 * $queryText  → stringa di testo con la query SQL (es. "SELECT * FROM pazienti...")
 * $query      → oggetto PDOStatement: la query preparata, pronta per essere eseguita
 * $data       → array con i dati del paziente passati dall'esterno (nome, telefono, ecc.)
 * $id         → numero intero che identifica univocamente un paziente nel database
 * $searchTerm → parola che l'utente vuole cercare (es. "Mario")
 * $term       → uguale a $searchTerm ma con i jolly SQL: "%Mario%" per ricerca parziale
 * $limit      → numero massimo di risultati da restituire
 */

require_once __DIR__ . '/../config/database.php';

class Patient
{
    private $db; // connessione PDO al database, usata in tutti i metodi

    public function __construct()
    {
        $this->db = getDB(); // ottiene la connessione Singleton dal file database.php
    }

    // Crea nuovo paziente
    public function createPatient($data) // $data = array con i campi del nuovo paziente
    {
        try {
            // Testo della query con i segnaposto al posto dei valori reali
            $queryText = "INSERT INTO pazienti (nome_cognome, data_nascita, telefono, indirizzo, email, professione) 
                    VALUES (:nome_cognome, :data_nascita, :telefono, :indirizzo, :email, :professione)";

            $query = $this->db->prepare($queryText); // prepara la query (invia la bozza al DB)
            $query->execute([                        // esegue sostituendo i segnaposto con i valori reali
                ':nome_cognome' => $data['nome_cognome'],
                ':data_nascita' => !empty($data['data_nascita']) ? $data['data_nascita'] : null, // null se vuoto
                ':telefono'     => $data['telefono'] ?? null,    // null se non presente
                ':indirizzo'    => $data['indirizzo'] ?? null,
                ':email'        => $data['email'] ?? null,
                ':professione'  => $data['professione'] ?? null
            ]);

            return $this->db->lastInsertId(); // ritorna l'ID del paziente appena creato
        } catch (PDOException $e) {
            error_log("Errore crea paziente: " . $e->getMessage()); // scrive l'errore nel log
            return false; // ritorna false se qualcosa è andato storto
        }
    }

    // Ottieni dati paziente
    public function getPatient($id) // $id = numero del paziente da cercare
    {
        try {
            // TIMESTAMPDIFF calcola automaticamente l'età del paziente in anni
            $queryText = "SELECT *, TIMESTAMPDIFF(YEAR, data_nascita, CURDATE()) AS eta 
                    FROM pazienti WHERE id = :id";
            $query = $this->db->prepare($queryText); // prepara la query
            $query->execute([':id' => $id]);          // esegue con l'ID reale
            return $query->fetch();                   // ritorna una sola riga come array associativo
        } catch (PDOException $e) {
            return false;
        }
    }

    // Aggiorna dati paziente
    public function updatePatient($id, $data) // $id = paziente da aggiornare, $data = nuovi valori
    {
        try {
            $queryText = "UPDATE pazienti 
                    SET nome_cognome = :nome_cognome,
                        data_nascita = :data_nascita,
                        telefono     = :telefono,
                        indirizzo    = :indirizzo,
                        email        = :email,
                        professione  = :professione
                    WHERE id = :id";

            $query = $this->db->prepare($queryText);
            return $query->execute([          // ritorna true se aggiornato, false se errore
                ':id'           => $id,
                ':nome_cognome' => $data['nome_cognome'],
                ':data_nascita' => !empty($data['data_nascita']) ? $data['data_nascita'] : null,
                ':telefono'     => $data['telefono'] ?? null,
                ':indirizzo'    => $data['indirizzo'] ?? null,
                ':email'        => $data['email'] ?? null,
                ':professione'  => $data['professione'] ?? null
            ]);
        } catch (PDOException $e) {
            return false;
        }
    }

    // Elimina paziente
public function deletePatient($id)
{
    try {
        $queryText = "DELETE FROM pazienti WHERE id = :id";
        $query = $this->db->prepare($queryText);
        return $query->execute([':id' => $id]);
    } catch (PDOException $e) {
        return false;
    }
}


    // Lista pazienti recenti
    public function getRecentPatients($limit = 10) // $limit = quanti pazienti restituire (default 10)
    {
        try {
            $queryText = "SELECT *, TIMESTAMPDIFF(YEAR, data_nascita, CURDATE()) AS eta 
                    FROM pazienti 
                    ORDER BY data_creazione DESC 
                    LIMIT :limit";
            $query = $this->db->prepare($queryText);
            $query->bindValue(':limit', (int) $limit, PDO::PARAM_INT); // forzato a intero (LIMIT lo richiede)
            $query->execute();
            return $query->fetchAll(); // ritorna tutte le righe come array
        } catch (PDOException $e) {
            return []; // ritorna array vuoto in caso di errore
        }
    }

    // Conta totale pazienti
    public function countPatients()
    {
        $query = $this->db->query("SELECT COUNT(*) as total FROM pazienti"); // query senza parametri, nessun rischio
        return $query->fetch()['total']; // ritorna il numero intero totale
    }

    // Cerca pazienti per nome, telefono o email
    public function searchPatients($searchTerm) // $searchTerm = parola cercata dall'utente (es. "Mario")
    {
        $term = "%$searchTerm%"; // aggiunge i jolly: "%Mario%" trova "Mario Rossi", "Luigi Mario", ecc.
        $queryText = "SELECT *, TIMESTAMPDIFF(YEAR, data_nascita, CURDATE()) AS eta 
                FROM pazienti 
                WHERE nome_cognome LIKE ? OR telefono LIKE ? OR email LIKE ?
                LIMIT 20"; // massimo 20 risultati
        $query = $this->db->prepare($queryText);
        $query->execute([$term, $term, $term]); // $term mandato 3 volte: per nome, telefono ed email
        return $query->fetchAll();
    }

    // Lista tutti i pazienti registrati (per la sezione "Pazienti Registrati")
    public function getAllPatients()
    {
        try {
            $queryText = "SELECT *, TIMESTAMPDIFF(YEAR, data_nascita, CURDATE()) AS eta 
                    FROM pazienti 
                    ORDER BY id DESC";
            $query = $this->db->query($queryText);
            return $query->fetchAll();
        } catch (PDOException $e) {
            return [];
        }
    }

    // Lista visite recenti con nome del paziente
    public function getRecentVisits($limit = 10)
    {
        try {
            $queryText = "SELECT v.*, p.nome_cognome 
                    FROM visite v 
                    JOIN pazienti p ON v.paziente_id = p.id 
                    ORDER BY v.data_visita DESC, v.id DESC 
                    LIMIT :limit";
            $query = $this->db->prepare($queryText);
            $query->bindValue(':limit', (int) $limit, PDO::PARAM_INT);
            $query->execute();
            return $query->fetchAll();
        } catch (PDOException $e) {
            return [];
        }
    }

    }
