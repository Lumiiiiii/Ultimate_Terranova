<?php
/**
 * Classe Visit - Gestione visite
 */

require_once __DIR__ . '/../config/database.php';

class Visit
{
    private $db;

    public function __construct()
    {
        $this->db = getDB();
    }

    /**
     * Ottieni tutte le visite di un paziente specifico
     * 
     * @param int $paziente_id ID del paziente
     * @return array Lista delle visite
     */
    public function getPatientVisits($paziente_id)
    {
        try {
            $queryText = "SELECT * FROM visite WHERE paziente_id = :paziente_id ORDER BY data_visita DESC, id DESC";
            $query = $this->db->prepare($queryText);
            $query->execute([':paziente_id' => $paziente_id]);
            return $query->fetchAll();
        } catch (PDOException $e) {
            error_log("Errore getPatientVisits: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Crea una nuova visita (implementazione base)
     */
    public function createVisit($data)
    {
        try {
            $queryText = "INSERT INTO visite (paziente_id, data_visita, motivazione, note_finali) 
                          VALUES (:paziente_id, :data_visita, :motivazione, :note_finali)";
            $query = $this->db->prepare($queryText);
            return $query->execute([
                ':paziente_id' => $data['paziente_id'],
                ':data_visita' => $data['data_visita'] ?? date('Y-m-d'),
                ':motivazione' => $data['motivazione'] ?? null,
                ':note_finali' => $data['note_finali'] ?? null
            ]);
        } catch (PDOException $e) {
            error_log("Errore createVisit: " . $e->getMessage());
            return false;
        }
    }
}
