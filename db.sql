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
),
CREATE TABLE anamnesi (
    id INT AUTO_INCREMENT PRIMARY KEY,
    paziente_id INT NOT NULL, --collegamento con la tabella pazienti
    allergie_intolleranze TEXT, --esempio: il paziente è allergico alle noci, intollerante al lattosio, ecc.
    farmaci_assunti TEXT, --esempio: il paziente assume la pillola anticoncezionale, prende l'antinfiammatorio ogni tanto
    patologie_pregresse TEXT, --esempio: il paziente soffre di ipertensione, diabete, ecc.
    interventi_chirurgici TEXT, --esempio: il paziente ha subito un intervento chirurgico al ginocchio nel 2019
    esami_clinici_recenti TEXT, --esempio: il paziente ha fatto le analisi del sangue l'anno scorso
    terapie_farmacologiche_croniche TEXT, --esempio: assumo la pillola anticoncezionale, prendo l'antinfiammatorio ogni tanto
    alcol VARCHAR(100), -- da rivedere, si potrebbe mettere un si/no
    fumo VARCHAR(100), -- da rivedere, si potrebbe mettere un si/no
    traumi_o_fratture TEXT, -- esempio: il paziente ha avuto una frattura al braccio sinistro nel 2020
    FOREIGN KEY (paziente_id) REFERENCES pazienti(id) ON DELETE CASCADE
);

