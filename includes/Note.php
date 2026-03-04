<?php
require_once __DIR__ . '/../config/database.php';

class Note
{
    private $db;

    public function __construct()
    {
        $this->db = getDB();
    }

    // Ottieni la nota generica della dashboard
    public function getNote()
    {
        $query = $this->db->query("SELECT testo FROM promemoria_veloce WHERE id = 1");
        $result = $query->fetch();
        return $result ? $result['testo'] : '';
    }

    // Salva la nota generica della dashboard
    public function updateNote($testo)
    {
        try {
            $queryText = "UPDATE promemoria_veloce SET testo = :testo WHERE id = 1";
            $query = $this->db->prepare($queryText);
            return $query->execute([':testo' => $testo]);
        } catch (PDOException $e) {
            return false;
        }
    }
}
