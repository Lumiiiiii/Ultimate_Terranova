<?php
// Avvia la sessione PHP per poter gestire l'autenticazione tramite cookie di sessione
session_start();

// Controllo Sicurezza: Verifichiamo se la variabile 'logged_in' esiste ed è uguale a true.
// Se l'utente non si è loggato, lo rimandiamo alla pagina di login.
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: login.php'); // Reindirizzamento
    exit; // Termina immediatamente lo script per evitare esecuzioni non autorizzate
}

// Includiamo il file di configurazione del database (che contiene la funzione getDB() con PDO)
require_once __DIR__ . '/config/database.php';
// Includiamo la classe Patient che ci aiuterà a recuperare i dati anagrafici del paziente
require_once __DIR__ . '/includes/Patient.php';

// Estraiamo l'ID del paziente dalla barra degli indirizzi (metodo GET).
// Usiamo l'operatore Null Coalescing (??) per assegnare 'null' se il parametro non esiste.
$paziente_id = $_GET['paziente_id'] ?? null;

// Se non c'è nessun ID, fermiamo la procedura e torniamo in dashboard.
if (!$paziente_id) {
    header('Location: index.php');
    exit;
}

// Creiamo un'istanza della classe Patient
$patientManager = new Patient();
// Interroghiamo il database per ottenere l'array associativo con i dati del paziente (es. nome_cognome)
$patient = $patientManager->getPatient($paziente_id);

// Se la query non trova il paziente (es. ID errato o paziente cancellato), torniamo in home.
if (!$patient) {
    header('Location: index.php');
    exit;
}

// Estraiamo in via autonoma anche la sua Anamnesi passata per mostrarla come "Bignami/Riassunto"
$anamnesi_passata = $patientManager->getAnamnesi($paziente_id);
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <!-- Impostazioni base della pagina (lingua, set di caratteri e responsive design per mobile) -->
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <!-- Titolo della scheda del browser visibile in alto -->
    <title>Nuova Visita di Controllo - <?= htmlspecialchars($patient['nome_cognome']) ?></title>
    
    <!-- Favicon: l'icona del sito (Logo Aequa) -->
    <link rel="icon" type="image/png" href="assets/img/logo.png">
    
    <!-- Collegamento alla libreria Bootstrap 5 per la griglia e i componenti stilizzati -->

<style>
  /* 2. Definisci subito lo sfondo esatto della tua dashboard nel root */
  :root { 
    background-color: #f8f9fa !important; /* Il grigio chiaro di Bootstrap */
  }
  body { 
    background-color: #f8f9fa !important; 
    visibility: visible !important;
  }
</style>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Foglio di Stile CSS interno -->
    <style>
        /* Definizione delle variabili CSS per mantenere coerenza nei colori */
        :root {
            --color-primary: #2ecc71; /* Verde Aequa */
            --color-accent: #3b82f6; /* Blu Aequa */
        }

        /* Classe per animare i pulsanti leggeri al passaggio del mouse (si alzano di 3px) */
        .hover-lift { transition: transform 0.2s ease; }
        .hover-lift:hover { transform: translateY(-3px) !important; }
        
        /* Forza bordi più arrotondati sulle card */
        .rounded-4 { border-radius: 1rem !important; }
            background-color: #f8f9fa;
            min-height: 100vh; /* Forza un'altezza minima pari a tutto lo schermo */
        }

        /* Stilizzazione specifica per i campi liberi Lunghi (Textarea) per renderli più eleganti */
        textarea.form-control {
            border-radius: 0.75rem; /* Angoli dolci */
            border: 1px solid #dee2e6; /* Bordo grigio classico */
            transition: border-color 0.2s; /* Effetto cambio colore morbido */
        }
        
        /* Effetto quando sto scrivendo dentro la textarea (focus attivo) */
        textarea.form-control:focus {
            border-color: var(--color-accent);
            box-shadow: 0 0 0 0.25rem rgba(59, 130, 246, 0.1); /* Crea un "alone" di luce azzurra */
        }
        
        /* Overlay CSS per il Caricamento (Spinner Blur) */
        /* NB: Questo div partirà "spento" (display: none) e verrà acceso solo via Javascript */
        .saving-overlay {
            display: none; 
            position: fixed;
            top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(255,255,255,0.8); /* Sfondo bianco trasparente */
            backdrop-filter: blur(5px); /* Sfuma gli elementi sottostanti */
            z-index: 9999; /* Sta sopra a qualsiasi altra cosa nella pagina */
            align-items: center; /* Centra l'icona sul monitor verticalmente e orizzontalmente*/
            justify-content: center;
            flex-direction: column; /* Impila la scritta sotto l'icona */
        }
    </style>
</head>
<body>

    <!-- SCHERMATA DI CARICAMENTO (Nascosta all'avvio) -->
    <!-- Questo livello blocca eventuali doppi-click compulsivi impedendo salvataggi doppi nel database -->
    <div class="saving-overlay" id="loadingOverlay">
        <div class="spinner-border text-primary" role="status" style="width: 3rem; height: 3rem;"></div>
        <h4 class="mt-3 text-dark fw-bold">Salvataggio Visita in corso...</h4>
    </div>

    <!-- CONTENTO PRINCIPALE -->
    <div class="main-content">
        
        <!-- BARRA DI NAVIGAZIONE IN ALTO (Header Sottile) -->
        <nav class="navbar navbar-light bg-white shadow-sm px-4 py-3 sticky-top">
            <div class="container-fluid d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center gap-3">
                    <!-- Tasto Freccia "Indietro": ci riporta al dettaglio del paziente saltando ogni compilazione -->
                    <a href="paziente_dettaglio.php?id=<?= $paziente_id ?>" class="btn btn-light border rounded-circle p-2 hover-lift d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                          <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
                        </svg>
                    </a>
                    
                    <div>
                        <!-- Titolo riepilogativo -->
                        <h5 class="mb-0 fw-bold">Nuova Visita di Controllo</h5>
                        <small class="text-muted">Paziente: <?= htmlspecialchars($patient['nome_cognome']) ?></small>
                    </div>
                </div>
            </div>
        </nav>

        <!-- ZONA DEL FORMULARIO CENTRALIZZATA -->
        <!-- Uso max-width 900px per impedire che i campi si "stiracchi" su monitor troppo larghi -->
        <main class="container py-5" style="max-width: 900px;">
            
            <!-- Intestazione esplicativa per il Naturopata -->
            <div class="text-center mb-5">
                <div class="d-inline-flex bg-accent bg-opacity-10 text-primary rounded-circle p-3 mb-3">
                    <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                    </svg>
                </div>
                <h2 class="fw-bold">Visita di Controllo</h2>
                <p class="text-muted">Compila i dati per registrare l'andamento del paziente in questa sessione specifica.</p>
            </div>

            <?php if ($anamnesi_passata): ?>
            <!-- ========================================== -->
            <!-- WIDGET ANAMNESI A SCOMPARSA (ACCORDION)  -->
            <!-- ========================================== -->
            <div class="accordion mb-5 shadow-sm rounded-4 overflow-hidden" id="accordionAnamnesi">
                <div class="accordion-item border-0">
                    <h2 class="accordion-header" id="headingAnamnesi">
                        <!-- Il bottone che apre/chiude la tendina -->
                        <!-- Attributi 'data-bs-toggle' e 'data-bs-target' sono i motori Javascript di Bootstrap per l'animazione -->
                        <button class="accordion-button collapsed fw-bold text-primary bg-primary bg-opacity-10" type="button" data-bs-toggle="collapse" data-bs-target="#collapseAnamnesi" aria-expanded="false" aria-controls="collapseAnamnesi">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" class="me-2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            Consulta Storico Anamnesi (Prima Visita)
                        </button>
                    </h2>
                                        <!-- Il corpo collassabile. Diventa un sotto-form autonomo per l'aggiornamento rapido -->
                        <div id="collapseAnamnesi" class="accordion-collapse collapse" aria-labelledby="headingAnamnesi" data-bs-parent="#accordionAnamnesi">
                            <div class="accordion-body bg-white p-4">
                                
                                <form id="anamnesi-update-form">
                                    <input type="hidden" name="action" value="update_anamnesi_rapido">
                                    <input type="hidden" name="paziente_id" value="<?= $paziente_id ?>">
                                    
                                    <div class="row g-4">
                                        <!-- Colonna 1: Storia Clinica -->
                                        <div class="col-md-6 border-end">
                                            <h6 class="text-muted fw-bold mb-3 border-bottom pb-2">Storia Clinica & Terapie</h6>
                                            
                                            <div class="mb-3">
                                                <label class="form-label small text-muted mb-1">Allergie/Intolleranze</label>
                                                <textarea name="allergie_intolleranze" class="form-control form-control-sm bg-light" rows="2"><?= htmlspecialchars($anamnesi_passata['allergie_intolleranze'] ?? '') ?></textarea>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label small text-muted mb-1">Patologie Pregresse</label>
                                                <textarea name="patologie_pregresse" class="form-control form-control-sm bg-light" rows="2"><?= htmlspecialchars($anamnesi_passata['patologie_pregresse'] ?? '') ?></textarea>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label small text-muted mb-1">Farmaci Assunti</label>
                                                <textarea name="farmaci_assunti" class="form-control form-control-sm bg-light" rows="2"><?= htmlspecialchars($anamnesi_passata['farmaci_assunti'] ?? '') ?></textarea>
                                            </div>
                                            <div class="row g-2 mb-3">
                                                <div class="col-6">
                                                    <label class="form-label small text-muted mb-1">Interventi Chirurgici</label>
                                                    <input type="text" name="interventi_chirurgici" class="form-control form-control-sm bg-light" value="<?= htmlspecialchars($anamnesi_passata['interventi_chirurgici'] ?? '') ?>">
                                                </div>
                                                <div class="col-6">
                                                    <label class="form-label small text-muted mb-1">Traumi/Fratture</label>
                                                    <input type="text" name="traumi_o_fratture" class="form-control form-control-sm bg-light" value="<?= htmlspecialchars($anamnesi_passata['traumi_o_fratture'] ?? '') ?>">
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <!-- Colonna 2: Stile di Vita e Biometria Base -->
                                        <div class="col-md-6">
                                            <h6 class="text-muted fw-bold mb-3 border-bottom pb-2">Stile di Vita Base & Note</h6>
                                            
                                            <div class="row g-2 mb-3">
                                                <div class="col-6">
                                                    <label class="form-label small text-muted mb-1">Altezza (cm)</label>
                                                    <input type="number" name="altezza" class="form-control form-control-sm bg-light" min="100" max="250" value="<?= htmlspecialchars($anamnesi_passata['altezza'] ?? '') ?>">
                                                </div>
                                                <div class="col-6">
                                                    <label class="form-label small text-muted mb-1">Peso Iniziale (kg)</label>
                                                    <input type="number" step="0.1" name="peso" min="20" max="300" class="form-control form-control-sm bg-light" value="<?= htmlspecialchars($anamnesi_passata['peso'] ?? '') ?>">
                                                </div>
                                            </div>

                                            <div class="mb-3">
                                                <label class="form-label small text-muted mb-1">Abitudine al Fumo</label>
                                                <input type="text" name="fumo" class="form-control form-control-sm bg-light" value="<?= htmlspecialchars($anamnesi_passata['fumo'] ?? '') ?>">
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label small text-muted mb-1">Consumo di Alcol</label>
                                                <input type="text" name="alcol" class="form-control form-control-sm bg-light" value="<?= htmlspecialchars($anamnesi_passata['alcol'] ?? '') ?>">
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label class="form-label small text-muted mb-1">Note Extra</label>
                                                <textarea name="note_aggiuntive" class="form-control form-control-sm bg-light text-primary" rows="2"><?= htmlspecialchars($anamnesi_passata['note_aggiuntive'] ?? '') ?></textarea>
                                            </div>
                                            
                                            <div class="d-flex justify-content-end mt-4">
                                                <!-- Bottone di invio per questa mini-form -->
                                                <button type="submit" class="btn btn-sm btn-outline-primary fw-bold d-flex align-items-center gap-2">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                      <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                                    </svg>
                                                    Aggiorna Anamnesi
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </form>

                            </div>
                        </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- INIZIO FORM HTML: Il blocco 'id="visita-form"' intercetterà l'invio via EventListener JS -->
            <form id="visita-form">
                
                <!-- DATI NASCOSTI MA FONDAMENTALI -->
                <!-- Indichiamo al backend PHP (ajax_handlers.php) che Azione eseguire -->
                <input type="hidden" name="action" value="create_visita">
                <!-- Portiamo con noi in via protetta per la query l'ID univoco del paziente da associare -->
                <input type="hidden" name="paziente_id" value="<?= $paziente_id ?>">

                <!-- ========================================== -->
                <!-- SEZIONE 1: Dettagli base (Data e motivo)   -->
                <!-- ========================================== -->
                <h6 class="text-primary border-bottom pb-2 mt-4 mb-3 d-flex align-items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                    Dettagli Visita
                </h6>
                <div class="row g-4 mb-4">
                    <!-- Utilizziamo col-md-4 e col-md-8 per sbilanciare la larghezza, la motivazione ha bisogno di più spazio visivo -->
                    <div class="col-md-4">
                        <label class="form-label small fw-semibold text-muted">Data Visita *</label>
                        <!-- type="date" attiva il calendario nel browser. Value in PHP imposta Di Default 'Oggi' (date('Y-m-d')) -->
                        <input type="date" name="data_visita" class="form-control bg-light py-2" value="<?= date('Y-m-d') ?>" required>
                    </div>
                    <div class="col-md-8">
                        <label class="form-label small fw-semibold text-muted">Motivazione Visita</label>
                        <!-- type="text" base per inserimento corto, utile a inquadrare il sintomo di oggi. -->
                        <textarea name="motivazione" class="form-control bg-light" rows="1" placeholder="Es. Visita mensile di routine, Riacutizzarsi candida..."></textarea>
                    </div>
                </div>

                <!-- ========================================== -->
                <!-- SEZIONE 2: Stato Psicofisico             -->
                <!-- ========================================== -->
                <h6 class="text-primary border-bottom pb-2 mt-5 mb-3 d-flex align-items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.828 14.828a4 4 0 01-5.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    Stato Psicofisico Attuale
                </h6>
                <div class="row g-4 mb-4">
                    <!-- Livello Stress & Energia: Vincolati da input numerico min/max -->
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold text-muted">Livello di Stress Percepito (1=Nullo, 10=Massimo)</label>
                        <input type="number" name="livello_stress" class="form-control bg-light py-2" min="1" max="10" placeholder="Es. 7">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold text-muted">Livello di Energia Diffusa (1=Spossato, 10=Energico)</label>
                        <input type="number" name="livello_energia" class="form-control bg-light py-2" min="1" max="10" placeholder="Es. 4">
                    </div>
                    
                    <div class="col-12">
                        <label class="form-label small fw-semibold text-muted">Stato Emotivo Generale</label>
                        <!-- textarea per input "TEXT" lunghi e descrittivi -->
                        <textarea name="stato_emotivo" class="form-control bg-light" rows="3" placeholder="Es. Sente molta ansia a causa del nuovo lavoro, piange facilmente..."></textarea>
                    </div>
                    
                    <div class="col-12">
                        <label class="form-label small fw-semibold text-muted">Livello di Concentrazione e Lucidità Mentale</label>
                        <!-- Casella Varchar usata come TextArea per permettere commenti estesi testuali -->
                        <textarea name="concentrazione" class="form-control bg-light" rows="2" placeholder="Es. Riferisce nebbia mentale specie nel pomeriggio..."></textarea>
                    </div>
                </div>

                <!-- ========================================== -->
                <!-- SEZIONE 3: Sonno e Dinamiche Notturne    -->
                <!-- ========================================== -->
                <h6 class="text-primary border-bottom pb-2 mt-5 mb-3 d-flex align-items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
                    </svg>
                    Sonno e Riposo
                </h6>
                <div class="row g-4 mb-4">
                    <div class="col-md-4">
                        <label class="form-label small fw-semibold text-muted">Peso (kg)</label>
                        <!-- type="number" step"0.1". Implica per Browser come "Chrome" di alzare un modulo numerico con potenziale incremento decadico min0 evitando errori testuali absurdi es -40kg-->
                        <input type="number" step="0.1" name="peso" class="form-control bg-light py-2" placeholder="Es. 70.5" min="20" max="300">
                    </div>
                    
                    <div class="col-md-8">
                        <label class="form-label small fw-semibold text-muted">Qualità del Sonno Percepita</label>
                        <textarea name="qualita_sonno_percepita" class="form-control bg-light" rows="2" placeholder="Es. Sonno poco profondo, si sveglia stanco..."></textarea>
                    </div>

                    <div class="col-12">
                        <label class="form-label small fw-semibold text-muted">Difficoltà ad addormentarsi o Risvegli Notturni?</label>
                        <textarea name="difficolta_addormentarsi_risvegli_notturni" class="form-control bg-light" rows="3" placeholder="Es. Difficoltà ad addormentarsi. Si sveglia fissa alle 3:00 del mattino (Fegato)..."></textarea>
                    </div>
                </div>

                <!-- ========================================== -->
                <!-- SEZIONE 4: Stile di Vita ed Evacuazione  -->
                <!-- ========================================== -->
                <h6 class="text-primary border-bottom pb-2 mt-5 mb-3 d-flex align-items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                    </svg>
                    Stile di Vita, Alimentazione e Fisiologia
                </h6>
                <div class="row g-4 mb-4">
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold text-muted">Alimentazione Recente</label>
                        <textarea name="alimentazione_recente" class="form-control bg-light" rows="3" placeholder="Es. Riconosce di aver sgarrato molto con zuccheri e latticini..."></textarea>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold text-muted">Livello Idratazione (Acqua bevuta)</label>
                        <textarea name="idratazione" class="form-control bg-light" rows="3" placeholder="Es. Beve poco, circa 3 bicchieri al giorno..."></textarea>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label small fw-semibold text-muted">Regolarità Intestinale (Feci e Alvo)</label>
                        <textarea name="regolarita_intestinale" class="form-control bg-light" rows="3" placeholder="Es. Stipsi ostinata. Evacua ogni 3 giorni, feci caprine..."></textarea>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold text-muted">Appetito e Digestione Piani</label>
                        <textarea name="appetito_e_digestione" class="form-control bg-light" rows="3" placeholder="Es. Riferisce perenne gonfiore post-prandiale..."></textarea>
                    </div>
                    
                    <div class="col-12">
                        <label class="form-label small fw-semibold text-muted">Attività Fisica / Sportiva</label>
                        <textarea name="attivita_fisica" class="form-control bg-light" rows="2" placeholder="Es. Sedentario, solo una camminata la domenica..."></textarea>
                    </div>
                </div>

                <!-- ========================================== -->
                <!-- SEZIONE 5: Trattamenti e Fine            -->
                <!-- ========================================== -->
                <h6 class="text-primary border-bottom pb-2 mt-5 mb-3 d-flex align-items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" />
                    </svg>
                    Supporti Naturopatici e Conclusioni
                </h6>
                <div class="row g-4 mb-4">
                    <div class="col-12">
                        <label class="form-label small fw-semibold text-muted">Supporti e Integrazioni Attualmente in Uso (o Consigliati)</label>
                        <textarea name="supporti_in_uso" class="form-control bg-light" rows="3" placeholder="Es. Omega 3 a colazione. Fiori di Bach prescritti oggi: Centaury e Mimulus."></textarea>
                    </div>
                    <div class="col-12">
                        <label class="form-label small fw-semibold text-muted">Note Finali del Naturopata</label>
                        <textarea name="note_finali" class="form-control bg-light" rows="4" placeholder="Appunti liberi (visibili solo a te). Es. Paziente molto collaborativo, prossimo controllo tra 40 gg."></textarea>
                    </div>
                </div>

                <!-- Modulo Azioni / Pulsanti (Flexbox per allinearli in fondo a destra) -->
                <div class="d-flex justify-content-end gap-3 mt-5 border-top pt-4">
                    <!-- Tasto per annullare: torna al Paziente perdendo le modifiche. -->
                    <a href="paziente_dettaglio.php?id=<?= $paziente_id ?>" class="btn btn-light px-4 py-3 rounded-3 fw-semibold text-muted hover-lift">
                        Annulla e Torna Indietro
                    </a>
                    
                    <!-- Pulsante SUBMIT: fa partire il form in automatico innescando intercettazione JS -->
                    <button type="submit" class="btn btn-gradient px-5 py-3 rounded-3 shadow-sm hover-lift fw-bold fs-5 d-flex align-items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" class="me-2">
                          <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                        </svg>
                        Salva Relazione Visita
                    </button>
                </div>

            </form>
        </main>
    </div>

    <!-- SCRIPT JAVASCRIPT: Gestiamo l'invio via AJAX (senza ricaricare la pagina fastidiosamente) -->
    <script>
    // Seleziono l'elemento FORM dal suo 'id' e resto in "Ascolto" (EventListener) di quando scatta l'evento "submit" (click sul btn di salvataggio)
    document.getElementById('visita-form').addEventListener('submit', async function(e) {
        
        // FONDAMENTALE (PASSAGGIO 2): Blocchiamo il comportamento automatico invadente del Form HTML Standard
        // Senza e.preventDefault() la pagina cercherebbe inesorabilmente di fare "refresh" auto-distruggendo i nostri passaggi asincroni.
        e.preventDefault();
        
        // MOSTRA LOADING: Prendiamo il "muro grigio semitrasparente" di loading da display 'none' (invisibile) a 'flex' (completamente coprente).
        // Serve a tranquillizzare che stiamo lavorando ed a proteggere i bottoni da doppi-click esasperanti.
        document.getElementById('loadingOverlay').style.display = 'flex';
        
        // Creo una FormData, è una specie di "carrello intelligente" Js che scansiona in loop da solo e prende TUTTI i campi del form (this) col proprio nome
        const formData = new FormData(this);
        
        // Iniziamo il ciclo asincrono protetto da Try/Catch, se scoppia internet entra in catch senza rompere il sito.
        try {
            // "await" obbliga il codice a sostare qua, attendendo paziente la risposta dal Backend 'ajax_handlers.php'.
            // fetch() è la potentissima API nativa di navigazione asincrona del Browser.
            const response = await fetch('ajax_handlers.php', {
                method: 'POST', // Comunichiamo col php in modalità POST protetta
                body: formData  // Spacchiamo "il carrello" FormData dritto verso le porte del server.
            });
            
            // Appena ha finito l'operazione Server, smontiamo la risposta codificata (da JSON testuale a Dizionario/Oggetto JS puro)
            const result = await response.json();
            
            // Il nostro server PHP (nel file ajax_handlers) risponderà sempre con un json_encode contenente -> { success: true/false }
            if (result.success) {
                // Se c'è esito positivo dal database, l'Operazione ha esito trionfale. 
                // Portiamo l'utente di prepotenza alla sua dashboard Paziente originale per vedere i frutti.
                window.location.href = 'paziente_dettaglio.php?id=<?= $paziente_id ?>';
            } else {
                // Oppure... Se il PDO di PHP ha sollevato un errore SQL. Notifichiamolo barbaramente (ma brutalmente onesto)
                alert('Errore nel salvataggio: ' + (result.error || 'Errore sconosciuto, query fallita'));
                // Sblocchiamo la visiera visiva abbassando il tendone Loading per fargli ricorreggere l'errore palese.
                document.getElementById('loadingOverlay').style.display = 'none';
            }
            
        } catch (error) {
            // E se salta il WIFI? E se il file ajax è irraggiungibile?
            // Alertiamo un Errore di Rete di Livello O.S. . L'app non muore qui.
            console.error('Errore chiamata asincrona Fetch:', error);
            alert('Si è verificato un errore di comunicazione con il server (Sei connesso?)');
            document.getElementById('loadingOverlay').style.display = 'none';
        }
    });

    // ==========================================
    // JS: GESTIONE AGGIORNAMENTO RAPIDO ANAMNESI
    // ==========================================
    const updateAnamnesiForm = document.getElementById('anamnesi-update-form');
    if (updateAnamnesiForm) {
        updateAnamnesiForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            const btn = this.querySelector('button[type="submit"]');
            const originalText = btn.innerHTML;
            
            // UI Feedback: faccio pulsare il bottone e cambio testo
            btn.innerHTML = `<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Salvataggio...`;
            btn.disabled = true;

            const formData = new FormData(this);
            formData.append('action', 'update_anamnesi_rapido');
            
            try {
                const response = await fetch('ajax_handlers.php', {
                    method: 'POST',
                    body: formData
                });
                const result = await response.json();
                
                if (result.success) {
                    // Successo! L'aggancio grafico (verifichiamo l'aggiornamento mettendo colore verde)
                    btn.classList.remove('btn-outline-primary');
                    btn.classList.add('btn-success', 'text-white');
                    btn.innerHTML = `<i class="fas fa-check"></i> Aggiornata!`;
                    
                    // Ripristino dopo 2 secondi
                    setTimeout(() => {
                        btn.classList.remove('btn-success', 'text-white');
                        btn.classList.add('btn-outline-primary');
                        btn.innerHTML = originalText;
                        btn.disabled = false;
                    }, 2500);
                } else {
                    alert('Errore aggiornamento Anamnesi: ' + (result.error || 'Errore DB'));
                    btn.innerHTML = originalText;
                    btn.disabled = false;
                }
            } catch (error) {
                console.error(error);
                alert('Errore di Rete durante l\'aggiornamento.');
                btn.innerHTML = originalText;
                btn.disabled = false;
            }
        });
    }
    </script>
    
    <!-- Script Bootstrap 5 JS - Obbligatorio per far funzionare la tendina animata Accordion -->
    <?php include 'includes/footer.php'; ?>
