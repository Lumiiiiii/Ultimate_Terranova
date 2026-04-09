<?php
// Visita Nuova — Form per registrare una visita di controllo
session_start();

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/Patient.php';

$paziente_id = $_GET['paziente_id'] ?? null;
if (!$paziente_id) {
    header('Location: index.php');
    exit;
}

$patientManager = new Patient();
$patient = $patientManager->getPatient($paziente_id);

if (!$patient) {
    header('Location: index.php');
    exit;
}

// Pre-caricamento per Piano Terapeutico
$db = getDB();
$stmtMed = $db->query("SELECT id, nome, dosaggio_standard FROM medicinali WHERE attivo = 1 ORDER BY nome ASC");
$lista_medicinali = $stmtMed->fetchAll(PDO::FETCH_ASSOC);

$stmtAlim = $db->query("SELECT id, nome FROM lista_alimenti ORDER BY nome ASC");
$lista_alimenti = $stmtAlim->fetchAll(PDO::FETCH_ASSOC);

// Recupera l'anamnesi precedente per il widget accordion
$anamnesi_passata = $patientManager->getAnamnesi($paziente_id);

$pageTitle = "Nuova Visita - " . htmlspecialchars($patient['nome_cognome']);
$currentPage = "index";
include 'includes/header.php';
?>

    <!-- Select2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" />
    <style>
        .select2-container--bootstrap-5 .select2-selection {
            min-height: calc(2.25rem + 2px);
            padding: .375rem .75rem;
            background-color: #f8f9fa; /* bg-light */
            border: 1px solid #dee2e6;
            border-radius: .375rem;
        }
    </style>

    <!-- Overlay di caricamento -->
    <div class="saving-overlay" id="loadingOverlay">
        <div class="spinner-border text-primary" role="status" style="width: 3rem; height: 3rem;"></div>
        <h4 class="mt-3 text-dark fw-bold">Salvataggio Visita in corso...</h4>
    </div>

    <!-- CONTENUTO PRINCIPALE (senza sidebar — pagina full-width) -->
    <div class="main-content" style="margin-left: 0;">
        
        <!-- Barra di navigazione -->
        <nav class="navbar navbar-light bg-white shadow-sm px-4 py-3 sticky-top">
            <div class="container-fluid d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center gap-3">
                    <a href="paziente_dettaglio.php?id=<?= $paziente_id ?>" class="btn btn-light border rounded-circle p-2 hover-lift d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                          <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
                        </svg>
                    </a>
                    <div>
                        <h5 class="mb-0 fw-bold">Nuova Visita di Controllo</h5>
                        <small class="text-muted">Paziente: <?= htmlspecialchars($patient['nome_cognome']) ?></small>
                    </div>
                </div>
            </div>
        </nav>

        <main class="container py-5" style="max-width: 900px;">
            
            <!-- Intestazione -->
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
            <!-- WIDGET ANAMNESI A SCOMPARSA (ACCORDION) -->
            <div class="accordion mb-5 shadow-sm rounded-4 overflow-hidden" id="accordionAnamnesi">
                <div class="accordion-item border-0">
                    <h2 class="accordion-header" id="headingAnamnesi">
                        <button class="accordion-button collapsed fw-bold custom-anamnesi-btn" type="button" data-bs-toggle="collapse" data-bs-target="#collapseAnamnesi" aria-expanded="false" aria-controls="collapseAnamnesi">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" class="me-2 text-primary">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            Consulta Storico Anamnesi (Prima Visita)
                        </button>
                    </h2>
                    <div id="collapseAnamnesi" class="accordion-collapse collapse" aria-labelledby="headingAnamnesi" data-bs-parent="#accordionAnamnesi">
                        <div class="accordion-body p-4">
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
                                    
                                    <!-- Colonna 2: Stile di Vita -->
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

            <!-- FORM VISITA DI CONTROLLO -->
            <form id="visita-form">
                <input type="hidden" name="action" value="create_visita">
                <input type="hidden" name="paziente_id" value="<?= $paziente_id ?>">

                <!-- SEZIONE 1: Dettagli Visita -->
                <h6 class="text-primary border-bottom pb-2 mt-4 mb-3 d-flex align-items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                    Dettagli Visita
                </h6>
                <div class="row g-4 mb-4">
                    <div class="col-md-4">
                        <label class="form-label small fw-semibold text-muted">Data Visita *</label>
                        <input type="date" name="data_visita" class="form-control bg-light py-2" value="<?= date('Y-m-d') ?>" required>
                    </div>
                    <div class="col-md-8">
                        <label class="form-label small fw-semibold text-muted">Motivazione Visita</label>
                        <textarea name="motivazione" class="form-control bg-light" rows="1" placeholder="Es. Visita mensile di routine, Riacutizzarsi candida..."></textarea>
                    </div>
                </div>

                <!-- SEZIONE 2: Campi Fissi -->
                <h6 class="text-primary border-bottom pb-2 mt-5 mb-3 d-flex align-items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                    </svg>
                    Informazioni Base
                </h6>
                <div class="row g-4 mb-4">
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold text-muted">Attività Fisica / Sportiva</label>
                        <textarea name="attivita_fisica" class="form-control bg-light" rows="2" placeholder="Es. Sedentario, solo una camminata la domenica..."></textarea>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold text-muted">Ore di Sonno (media)</label>
                        <input type="number" step="0.5" name="ore_sonno" class="form-control bg-light py-2" placeholder="Es. 7" min="0" max="24">
                    </div>
                </div>

                <!-- SEZIONE 3: Domande Aggiuntive (Dinamiche) -->
                <h6 class="text-primary border-bottom pb-2 mt-5 mb-3 d-flex align-items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    Domande Aggiuntive
                    <span class="badge bg-primary bg-opacity-10 text-primary rounded-pill ms-auto px-3 py-1 small" id="contatore-domande">0 domande</span>
                </h6>
                <p class="text-muted small mb-3">Aggiungi domande personalizzate per questa visita. Ogni visita può avere domande diverse.</p>

                <div id="domande-container">
                    <!-- Le domande aggiuntive verranno inserite qui dinamicamente -->
                </div>

                <button type="button" id="btn-aggiungi-domanda" class="btn btn-outline-primary rounded-3 px-4 py-2 mb-4 d-flex align-items-center gap-2 hover-lift shadow-sm" onclick="aggiungiDomanda()">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                    </svg>
                    Aggiungi Domanda
                </button>

                <!-- SEZIONE 4: Piano Terapeutico (Nuova) -->
                <h6 class="text-primary border-bottom pb-2 mt-5 mb-3 d-flex align-items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" />
                    </svg>
                    Piano Terapeutico
                </h6>
                <p class="text-muted small mb-3">Imposta i consigli nutrizionali e componi le prescrizioni di integratori per questo mese.</p>
                
                <div class="row g-4 mb-4">
                    <!-- Colonna Integratori -->
                    <div class="col-md-7">
                        <label class="form-label small fw-bold text-dark">Integratori Prescritti</label>
                        <div id="prescrizioni-container">
                            <!-- Riga 1 (Base) -->
                            <div class="d-flex gap-2 mb-2 prescrizione-row">
                                <div class="flex-grow-1" style="flex-basis: 40%;">
                                    <select name="integratori[]" class="form-select select2-medicinali">
                                        <option value=""></option>
                                        <?php foreach($lista_medicinali as $med): ?>
                                            <option value="<?= $med['id'] ?>" data-dosaggio="<?= htmlspecialchars($med['dosaggio_standard']) ?>"><?= htmlspecialchars($med['nome']) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div style="flex-basis: 35%;">
                                    <input type="text" name="dosaggi[]" class="form-control bg-light" placeholder="Dosaggio es. 2 compresse">
                                </div>
                                <div style="flex-basis: 25%;">
                                    <input type="text" name="durate[]" class="form-control bg-light" placeholder="Durata es. 30 gg">
                                </div>
                                <button type="button" class="btn btn-outline-danger btn-sm border-0 px-2 shadow-none hover-lift opacity-50" onclick="rimuoviPrescrizione(this)" title="Rimuovi riga">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                                </button>
                            </div>
                        </div>
                        <button type="button" class="btn btn-sm btn-light fw-medium mt-2 hover-lift d-flex align-items-center gap-1" onclick="aggiungiPrescrizione()">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>Aggiungi Integratore
                        </button>
                    </div>

                    <!-- Colonna Alimenti Evitare -->
                    <div class="col-md-5">
                        <label class="form-label small fw-bold text-dark">Alimenti da Evitare / Sospendere</label>
                        <select name="alimenti[]" class="form-select select2-alimenti" multiple="multiple">
                            <?php foreach($lista_alimenti as $alim): ?>
                                <option value="<?= $alim['id'] ?>"><?= htmlspecialchars($alim['nome']) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <div class="form-text small opacity-75 mt-2">Gli alimenti selezionati resteranno <span class="fw-bold">Attivi</span> nella scheda paziente finché non verranno revocati.</div>
                    </div>
                </div>

                <!-- SEZIONE 5: Note Finali -->
                <h6 class="text-primary border-bottom pb-2 mt-5 mb-3 d-flex align-items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" />
                    </svg>
                    Conclusioni
                </h6>
                <div class="row g-4 mb-4">
                    <div class="col-12">
                        <label class="form-label small fw-semibold text-muted">Note Finali del Naturopata</label>
                        <textarea name="note_finali" class="form-control bg-light" rows="4" placeholder="Appunti liberi (visibili solo a te). Es. Paziente molto collaborativo, prossimo controllo tra 40 gg."></textarea>
                    </div>
                </div>

                <!-- Pulsanti -->
                <div class="d-flex justify-content-end gap-3 mt-5 border-top pt-4">
                    <a href="paziente_dettaglio.php?id=<?= $paziente_id ?>" class="btn btn-light px-4 py-3 rounded-3 fw-semibold text-muted hover-lift">
                        Annulla e Torna Indietro
                    </a>
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

    <!-- Bootstrap/jQuery base necessari per Select2 e App -->
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <!-- SCRIPT: Invio visita via AJAX, Domande Dinamiche e Piano Terapeutico -->
    <script>
    let contatoreDomande = 0;

    // --- Inizializzazione Select2 ---
    function initSelect2() {
        $('.select2-medicinali').select2({
            theme: 'bootstrap-5',
            placeholder: "Cerca e inserisci un rimedio...",
            allowClear: true,
            width: '100%'
        }).on('select2:select', function (e) {
            // Auto compilazione dose standard
            var data = e.params.data;
            var dosaggioStandard = $(data.element).attr('data-dosaggio');
            var riga = $(this).closest('.prescrizione-row');
            var inputDosaggio = riga.find('input[name="dosaggi[]"]');
            if(dosaggioStandard && inputDosaggio.val() === '') {
                inputDosaggio.val(dosaggioStandard);
            }
        });

        $('.select2-alimenti').select2({
            theme: 'bootstrap-5',
            placeholder: "Cerca e aggiungi cibi da escludere temporaneamente...",
            width: '100%'
        });
    }

    $(document).ready(function() {
        initSelect2();
    });

    // --- Piano Terapeutico: Aggiungi/Rimuovi righe ---
    function aggiungiPrescrizione() {
        const container = document.getElementById('prescrizioni-container');
        const count = container.querySelectorAll('.prescrizione-row').length;
        
        const nuovaRiga = document.createElement('div');
        nuovaRiga.className = 'd-flex gap-2 mb-2 prescrizione-row';
        nuovaRiga.innerHTML = `
            <div class="flex-grow-1" style="flex-basis: 40%;">
                <select name="integratori[]" class="form-select select2-medicinali-dyn-${count}">
                    <option value=""></option>
                    <?php foreach($lista_medicinali as $med): ?>
                        <option value="<?= $med['id'] ?>" data-dosaggio="<?= htmlspecialchars($med['dosaggio_standard']) ?>"><?= addslashes(htmlspecialchars($med['nome'])) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div style="flex-basis: 35%;">
                <input type="text" name="dosaggi[]" class="form-control bg-light" placeholder="Dosaggio">
            </div>
            <div style="flex-basis: 25%;">
                <input type="text" name="durate[]" class="form-control bg-light" placeholder="Durata">
            </div>
            <button type="button" class="btn btn-outline-danger btn-sm border-0 px-2 shadow-none hover-lift opacity-50" onclick="rimuoviPrescrizione(this)" title="Rimuovi riga">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
            </button>
        `;
        container.appendChild(nuovaRiga);
        
        // Inizializza select2 sulla nuova select
        $('.select2-medicinali-dyn-' + count).select2({
            theme: 'bootstrap-5',
            placeholder: "Cerca e inserisci...",
            allowClear: true,
            width: '100%'
        }).on('select2:select', function (e) {
            var ds = $(e.params.data.element).attr('data-dosaggio');
            var inputD = $(this).closest('.prescrizione-row').find('input[name="dosaggi[]"]');
            if(ds && inputD.val() === '') inputD.val(ds);
        });
    }

    function rimuoviPrescrizione(btn) {
        // Se è l'ultima riga rimasta, svuotiamo solo i campi
        let container = document.getElementById('prescrizioni-container');
        if (container.querySelectorAll('.prescrizione-row').length > 1) {
            btn.closest('.prescrizione-row').remove();
        } else {
            let row = btn.closest('.prescrizione-row');
            $(row).find('select').val(null).trigger('change');
            row.querySelectorAll('input').forEach(input => input.val = '');
        }
    }

    function aggiornaContatore() {
        const badge = document.getElementById('contatore-domande');
        if (badge) badge.innerText = contatoreDomande + (contatoreDomande === 1 ? ' domanda' : ' domande');
    }

    function aggiungiDomanda() {
        contatoreDomande++;
        
        const container = document.getElementById('domande-container');
        const dDiv = document.createElement('div');
        dDiv.className = 'card border-0 shadow-sm bg-light p-3 mb-3 position-relative domanda-item';
        dDiv.innerHTML = `
            <div class="d-flex justify-content-between align-items-center mb-2">
                <label class="form-label small fw-semibold text-muted mb-0">Domanda ${contatoreDomande}:</label>
                <button type="button" class="btn btn-sm btn-outline-danger fw-bold shadow-none" onclick="rimuoviDomanda(this)">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" class="me-1">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                    Rimuovi
                </button>
            </div>
            <input type="text" name="domande[]" class="form-control mb-3" placeholder="Es. Hai avuto mal di testa questa settimana?" required>
            <div>
                <label class="form-label small fw-semibold text-muted">Risposta:</label>
                <textarea name="risposte[]" class="form-control" rows="2" placeholder="Es. Sì, soprattutto il martedì..." required></textarea>
            </div>
        `;
        container.appendChild(dDiv);
        aggiornaContatore();
    }

    window.rimuoviDomanda = function(btn) {
        btn.closest('.domanda-item').remove();
        contatoreDomande--;
        
        // Rinumeriamo le etichette per tenere ordine visivo
        const items = document.querySelectorAll('.domanda-item');
        items.forEach((item, index) => {
            const label = item.querySelector('label');
            if (label) label.innerText = 'Domanda ' + (index + 1) + ':';
        });
        
        aggiornaContatore();
    };

    document.getElementById('visita-form').addEventListener('submit', async function(e) {
        e.preventDefault();
        const overlay = document.getElementById('loadingOverlay');
        if (overlay) overlay.style.display = 'flex';
        
        try {
            const response = await fetch('ajax_handlers.php', { method: 'POST', body: new FormData(this) });
            const result = await response.json();
            
            if (result.success) {
                window.location.href = 'paziente_dettaglio.php?id=<?= $paziente_id ?>';
            } else {
                alert('Errore nel salvataggio: ' + (result.error || 'Errore sconosciuto'));
                document.getElementById('loadingOverlay').style.display = 'none';
            }
        } catch (error) {
            console.error(error);
            alert('Errore di comunicazione con il server.');
            document.getElementById('loadingOverlay').style.display = 'none';
        }
    });

    // Aggiornamento rapido anamnesi dall'accordion
    const updateAnamnesiForm = document.getElementById('anamnesi-update-form');
    if (updateAnamnesiForm) {
        updateAnamnesiForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            const btn = this.querySelector('button[type="submit"]');
            const originalText = btn.innerHTML;
            btn.innerHTML = `<span class="spinner-border spinner-border-sm" role="status"></span> Salvataggio...`;
            btn.disabled = true;

            try {
                const response = await fetch('ajax_handlers.php', { method: 'POST', body: new FormData(this) });
                const result = await response.json();
                
                if (result.success) {
                    btn.classList.remove('btn-outline-primary');
                    btn.classList.add('btn-success', 'text-white');
                    btn.innerHTML = `<i class="fas fa-check"></i> Aggiornata!`;
                    setTimeout(() => {
                        btn.classList.remove('btn-success', 'text-white');
                        btn.classList.add('btn-outline-primary');
                        btn.innerHTML = originalText;
                        btn.disabled = false;
                    }, 2500);
                } else {
                    alert('Errore aggiornamento: ' + (result.error || 'Errore DB'));
                    btn.innerHTML = originalText; btn.disabled = false;
                }
            } catch (error) {
                console.error(error);
                alert('Errore di rete.');
                btn.innerHTML = originalText; btn.disabled = false;
            }
        });
    }
    </script>
    
    <?php include 'includes/footer.php'; ?>
