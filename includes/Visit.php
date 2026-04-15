<?php
/**
 * Classe Visit - Gestione Visite (Sedute singole)
 */
require_once __DIR__ . '/../config/database.php';

class Visit
{
    private $db;

    public function __construct()
    {
        $this->db = getDB();
    }

    // Crea nuova visita
    public function createVisit($paziente_id)
    {
        $date = date('Y-m-d');
        $stmt = $this->db->prepare("INSERT INTO visite (paziente_id, data_visita) VALUES (?, ?)");
        if ($stmt->execute([$paziente_id, $date])) {
            return $this->db->lastInsertId();
        }
        return false;
    }

    // Ottieni dati visita
    public function getVisit($id)
    {
        $stmt = $this->db->prepare("SELECT * FROM visite WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    // Aggiorna visita
    public function updateVisit($id, $data)
    {
        $sql = "UPDATE visite SET 
                motivazione = :motivazione,
                concentrazione = :concentrazione,
                stato_emotivo = :stato_emotivo,
                attivita_fisica = :attivita_fisica,
                idratazione = :idratazione,
                qualita_sonno_percepita = :qualita_sonno,
                ore_sonno = :ore_sonno,
                sintomi_acuti = :sintomi,
                regolarita_intestinale = :regolarita,
                appetito_e_digestione = :digestione,
                difficolta_addormentarsi_risvegli_notturni = :sonno_problemi,
                livello_stress = :stress,
                livello_energia = :energia,
                supporti_in_uso = :supporti,
                alimentazione_recente = :alimentazione,
                note_finali = :note
                WHERE id = :id";

        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':id' => $id,
            ':motivazione' => $data['motivazione'] ?? '',
            ':concentrazione' => $data['concentrazione'] ?? '',
            ':stato_emotivo' => $data['stato_emotivo'] ?? '',
            ':attivita_fisica' => $data['attivita_fisica'] ?? '',
            ':idratazione' => $data['idratazione'] ?? '',
            ':qualita_sonno' => $data['qualita_sonno_percepita'] ?? '',
            ':ore_sonno' => !empty($data['ore_sonno']) ? $data['ore_sonno'] : 0,
            ':sintomi' => $data['sintomi_acuti'] ?? '',
            ':regolarita' => $data['regolarita_intestinale'] ?? '',
            ':digestione' => $data['appetito_e_digestione'] ?? '',
            ':sonno_problemi' => $data['difficolta_addormentarsi_risvegli_notturni'] ?? '',
            ':stress' => !empty($data['livello_stress']) ? $data['livello_stress'] : 0,
            ':energia' => !empty($data['livello_energia']) ? $data['livello_energia'] : 0,
            ':supporti' => $data['supporti_in_uso'] ?? '',
            ':alimentazione' => $data['alimentazione_recente'] ?? '',
            ':note' => $data['note_finali'] ?? ''
        ]);
    }

    // Storico visite paziente
    public function getVisitHistory($paziente_id)
    {
        $stmt = $this->db->prepare("SELECT * FROM visite WHERE paziente_id = ? ORDER BY data_visita DESC");
        $stmt->execute([$paziente_id]);
        return $stmt->fetchAll();
    }

    /**
     * Recupera tutte le domande aggiuntive di una visita dalla tabella normalizzata
     * Ritorna un array di righe, ciascuna con: id, visita_id, numero_ordine, domanda, risposta
     */
    public function getDomandeAggiuntive($visita_id)
    {
        try {
            $stmt = $this->db->prepare("SELECT * FROM domande_aggiuntive WHERE visita_id = ? ORDER BY numero_ordine ASC");
            $stmt->execute([$visita_id]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Errore in getDomandeAggiuntive: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Recupera le prescrizioni (integratori) assegnate in una specifica visita
     */
    public function getPrescrizioniByVisita($visita_id)
    {
        try {
            $queryText = "
                SELECT p.id, p.dosaggio, p.frequenza, p.durata, p.note_prescrizione,
                       p.data_inizio, p.data_fine, m.nome AS nome_rimedio
                FROM prescrizioni p
                JOIN medicinali m ON p.medicinale_id = m.id
                WHERE p.visita_id = :visita_id
                ORDER BY p.data_creazione ASC
            ";
            $stmt = $this->db->prepare($queryText);
            $stmt->execute([':visita_id' => $visita_id]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Errore in getPrescrizioniByVisita: " . $e->getMessage());
            return [];
        }
    }
}
