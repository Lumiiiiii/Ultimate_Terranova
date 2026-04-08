DROP DATABASE IF EXISTS terranova_naturopata;
CREATE DATABASE terranova_naturopata;
USE terranova_naturopata;
CREATE TABLE pazienti (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome_cognome VARCHAR(255) NOT NULL,
    data_nascita DATE,
    telefono VARCHAR(20),
    indirizzo VARCHAR(255),
    email VARCHAR(255),
    professione VARCHAR(255),
    data_creazione TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    data_modifica TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
CREATE TABLE anamnesi (
    id INT AUTO_INCREMENT PRIMARY KEY,
    paziente_id INT NOT NULL, -- collegamento con la tabella pazienti
    allergie_intolleranze TEXT, -- esempio: il paziente è allergico alle noci, intollerante al lattosio, ecc.
    farmaci_assunti TEXT, -- esempio: il paziente assume la pillola anticoncezionale, prende l'antinfiammatorio ogni tanto
    patologie_pregresse TEXT, -- esempio: il paziente soffre di ipertensione, diabete, ecc.
    interventi_chirurgici TEXT, -- esempio: il paziente ha subito un intervento chirurgico al ginocchio nel 2019
    esami_clinici_recenti TEXT, -- esempio: il paziente ha fatto le analisi del sangue l'anno scorso
    alcol VARCHAR(100), -- da rivedere, si potrebbe mettere un si/no
    fumo VARCHAR(100), -- da rivedere, si potrebbe mettere un si/no
    traumi_o_fratture TEXT, -- esempio: il paziente ha avuto una frattura al braccio sinistro nel 2020
    altezza INT,
    peso decimal(5,2),
    note_aggiuntive TEXT,
    FOREIGN KEY (paziente_id) REFERENCES pazienti(id) ON DELETE CASCADE
);
CREATE TABLE visite(
id INT AUTO_INCREMENT PRIMARY KEY,
paziente_id INT NOT NULL,
data_visita DATE,
motivazione TEXT,
attivita_fisica TEXT,
ore_sonno DECIMAL(4,2),
note_finali TEXT,
data_modifica TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
FOREIGN KEY (paziente_id) REFERENCES pazienti(id) ON DELETE CASCADE
);
-- Tabella normalizzata per le domande aggiuntive (collegate alla visita tramite FK)
-- Permette un numero illimitato di domande per ogni visita, senza sprecare colonne
CREATE TABLE domande_aggiuntive (
    id INT AUTO_INCREMENT PRIMARY KEY,
    visita_id INT NOT NULL,
    numero_ordine INT NOT NULL,          -- mantiene l'ordine di inserimento (1, 2, 3...)
    domanda VARCHAR(255) NOT NULL,
    risposta TEXT,
    FOREIGN KEY (visita_id) REFERENCES visite(id) ON DELETE CASCADE
);
CREATE TABLE medicinali (
id INT AUTO_INCREMENT PRIMARY KEY,
nome VARCHAR(255) NOT NULL,
tipologia VARCHAR(100), -- esempio: farmaco, suplemento, ecc.
formato VARCHAR(100), -- esempio: tablet, capsule, spray, ecc.
dosaggio_standard VARCHAR(100), -- esempio: 1 tablet al giorno
attivo BOOLEAN DEFAULT TRUE, 
note TEXT, -- esempio: il farmaco è per la pressione alta
data_creazione TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
data_modifica TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);  
CREATE TABLE prescrizioni (
id INT AUTO_INCREMENT PRIMARY KEY,
paziente_id INT NOT NULL,
medicinale_id INT NOT NULL,
visita_id INT NOT NULL,
dosaggio VARCHAR(100), -- esempio: 1 compressa, 10 gocce, 5mg...
frequenza VARCHAR(100), -- esempio: 2 volte al giorno, 8 ore prima dei pasti 
durata VARCHAR(100), -- esempio: per 5 giorni, per 10 mesi
note_prescrizione TEXT, -- esempio: il farmaco è per la pressione alta
data_inizio DATE,
data_fine DATE,
attivo BOOLEAN DEFAULT TRUE,
data_creazione TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
data_modifica TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
FOREIGN KEY (paziente_id) REFERENCES pazienti(id) ON DELETE CASCADE,
FOREIGN KEY (medicinale_id) REFERENCES medicinali(id) ON DELETE CASCADE,
FOREIGN KEY (visita_id) REFERENCES visite(id) ON DELETE CASCADE
);
CREATE TABLE lista_alimenti (
id INT AUTO_INCREMENT PRIMARY KEY,
nome VARCHAR(255) NOT NULL,
ordine INT
);
CREATE TABLE alimenti_evitare (
id INT AUTO_INCREMENT PRIMARY KEY,
paziente_id INT NOT NULL,
lista_alimenti_id INT NOT NULL,
attivo BOOLEAN DEFAULT TRUE,
data_aggiunta TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
data_modifica TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
FOREIGN KEY (paziente_id) REFERENCES pazienti(id) ON DELETE CASCADE,
FOREIGN KEY (lista_alimenti_id) REFERENCES lista_alimenti(id) ON DELETE CASCADE
);
CREATE TABLE promemoria_veloce (
    id INT PRIMARY KEY,
    testo TEXT,
    data_modifica TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Inseriamo subito una riga vuota che il gestionale andrà sempre ad aggiornare (invece di crearne di nuove)
INSERT INTO promemoria_veloce (id, testo) VALUES (1, '');

CREATE TABLE `eventi` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `start` datetime NOT NULL,
  `end` datetime DEFAULT NULL,
  `color` varchar(20) DEFAULT '#2ecc71',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
