<?php
ob_start();
session_start();

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/Patient.php';
require_once __DIR__ . '/includes/Visit.php';

$visita_id = $_GET['id'] ?? 0;

$visitManager = new Visit();
$patientManager = new Patient();

$visit = $visitManager->getVisit($visita_id);
if (!$visit) {
    header('Location: index.php');
    exit;
}

$patient = $patientManager->getPatient($visit['paziente_id']);
if (!$patient) {
    header('Location: index.php');
    exit;
}

$anamnesi = $patientManager->getAnamnesi($visit['paziente_id']);
$domande_aggiuntive = $visitManager->getDomandeAggiuntive($visita_id);
$prescrizioni_visita = $visitManager->getPrescrizioniByVisita($visita_id);

$pageTitle = "Dettaglio Visita - " . htmlspecialchars($patient['nome_cognome']);
$currentPage = "pazienti";

include 'includes/header.php';
include 'includes/sidebar.php';
?>

    <!-- ══ CONTENUTO PRINCIPALE ══════════════════════════════════════════════ -->
    <div class="main-content">
        <main class="container-xl py-5">
            
            <!-- Header della pagina -->
            <header class="mb-5">
                <div class="d-flex align-items-center gap-3 mb-1">
                    <a href="paziente_dettaglio.php?id=<?= $patient['id'] ?>" class="text-muted text-decoration-none d-flex align-items-center gap-1 hover-lift">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
                        </svg>
                        Scheda Assistito
                    </a>
                    <span class="text-muted">/</span>
                    <span class="text-dark fw-semibold">Visita del <?= date('d/m/Y', strtotime($visit['data_visita'])) ?></span>
                </div>
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h2 class="fw-bold mb-1">Resoconto Visita</h2>
                        <p class="text-muted mb-0">Riepilogo della seduta per <span class="fw-semibold text-primary"><?= htmlspecialchars($patient['nome_cognome']) ?></span>.</p>
                    </div>
                </div>
            </header>

            <div class="row g-4">
                
                <!-- Colonna Sinistra Principale: Dettagli della visita -->
                <div class="col-lg-8">
                    <div class="d-flex flex-column gap-4">
                        
                        <!-- Dettagli Base & Motivazione -->
                        <div class="card border-0 shadow-sm rounded-4 bg-white p-4">
                            <h5 class="fw-bold text-dark border-bottom pb-3 mb-4 d-flex align-items-center gap-2">
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" class="text-primary">
                                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                                Informazioni Base
                            </h5>
                            
                            <div class="mb-4">
                                <label class="small text-muted fw-bold text-uppercase tracking-wider mb-1">Motivazione Visita</label>
                                <p class="text-dark bg-light p-3 rounded-3 mb-0" style="font-size: 0.95rem;">
                                    <?= nl2br(htmlspecialchars($visit['motivazione'] ?: 'Non specificata.')) ?>
                                </p>
                            </div>
                            
                            <div class="row g-4">
                                <div class="col-md-6">
                                    <label class="small text-muted fw-bold text-uppercase tracking-wider mb-1">Attività Fisica Regolare</label>
                                    <p class="text-dark bg-light p-3 rounded-3 mb-0" style="font-size: 0.95rem;">
                                        <?= nl2br(htmlspecialchars($visit['attivita_fisica'] ?: 'Non specificata.')) ?>
                                    </p>
                                </div>
                                <div class="col-md-6">
                                    <label class="small text-muted fw-bold text-uppercase tracking-wider mb-1">Sonno (Media Ore / Notte)</label>
                                    <?php if(!empty($visit['ore_sonno'])): ?>
                                        <div class="d-flex align-items-center gap-2 bg-light p-3 rounded-3">
                                            <span class="fs-4 fw-bold text-primary"><?= (float)$visit['ore_sonno'] ?></span> 
                                            <span class="text-muted">ore</span>
                                        </div>
                                    <?php else: ?>
                                        <div class="bg-light p-3 rounded-3 text-muted">Non registrato</div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>



                        <!-- Domande Aggiuntive -->
                        <div class="card border-0 shadow-sm rounded-4 bg-white p-4">
                            <h5 class="fw-bold text-dark border-bottom pb-3 mb-4 d-flex align-items-center gap-2">
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" class="text-primary">
                                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                Risposte alle Domande Specifiche
                            </h5>
                            <?php if(!empty($domande_aggiuntive)): ?>
                                <div class="d-flex flex-column gap-3">
                                    <?php foreach($domande_aggiuntive as $index => $domanda): ?>
                                        <div class="bg-light bg-opacity-50 p-3 rounded-3 border">
                                            <p class="fw-bold text-dark mb-1 d-flex gap-2">
                                                <span class="text-primary">Q:</span> <?= htmlspecialchars($domanda['domanda']) ?>
                                            </p>
                                            <p class="text-muted mb-0 ps-4 border-start border-primary border-3 ms-1 mt-2">
                                                <?= nl2br(htmlspecialchars($domanda['risposta'] ?: 'Nessuna risposta fornita.')) ?>
                                            </p>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php else: ?>
                                <div class="bg-light p-3 rounded-3 text-muted fst-italic text-center border">
                                    Nessuna domanda specifica registrata per questa seduta.
                                </div>
                            <?php endif; ?>
                        </div>

                        <!-- Piano Terapeutico Assegnato / Integratori -->
                        <?php if(!empty($prescrizioni_visita)): ?>
                        <div class="card border-0 shadow-sm rounded-4 bg-white p-4">
                            <h5 class="fw-bold text-dark border-bottom pb-3 mb-4 d-flex align-items-center gap-2">
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" class="text-primary">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" />
                                </svg>
                                Integratori Prescritti (In questa seduta)
                            </h5>
                            <div class="row g-3">
                                <?php foreach($prescrizioni_visita as $p): ?>
                                    <div class="col-md-6">
                                        <div class="p-3 border rounded-3 bg-transparent h-100 position-relative">
                                            <span class="d-block fw-bold text-dark mb-1"><?= htmlspecialchars($p['nome_rimedio']) ?></span>
                                            
                                            <?php if(!empty($p['dosaggio'])): ?>
                                                <div class="small text-muted mb-1"><i class="bi bi-prescription2 me-1"></i> <strong>Dosi:</strong> <?= htmlspecialchars($p['dosaggio']) ?></div>
                                            <?php endif; ?>
                                            
                                            <?php if(!empty($p['durata'])): ?>
                                                <div class="small text-muted"><i class="bi bi-clock-history me-1"></i> <strong>Durata:</strong> <?= htmlspecialchars($p['durata']) ?></div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <?php endif; ?>

                        <!-- Note Finali (Conclusioni) -->
                        <div class="card border-0 shadow-sm rounded-4 bg-white p-4">
                            <h5 class="fw-bold text-dark border-bottom pb-3 mb-4 d-flex align-items-center gap-2">
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" class="text-primary">
                                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                </svg>
                                Conclusioni e Appunti del Naturopata
                            </h5>
                            <div class="bg-primary bg-opacity-10 text-dark p-4 rounded-3 border border-primary border-opacity-25" style="min-height: 120px;">
                                <?php if(!empty($visit['note_finali'])): ?>
                                    <p class="mb-0" style="white-space: pre-wrap; font-size: 0.95rem; line-height: 1.6;"><?= htmlspecialchars($visit['note_finali']) ?></p>
                                <?php else: ?>
                                    <p class="text-muted fst-italic mb-0">Nessun appunto registrato per questa seduta.</p>
                                <?php endif; ?>
                            </div>
                        </div>

                    </div>
                </div>
                
                <!-- Colonna Destra: Anamnesi Sempre Visibile -->
                <div class="col-lg-4">
                    <div class="card border-0 shadow-sm rounded-4 bg-white sticky-top" style="top: 100px; z-index: 1;">
                        <div class="card-header bg-transparent border-bottom py-3 px-4">
                            <h5 class="fw-bold mb-0 d-flex align-items-center gap-2">
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" class="text-primary">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                                Anamnesi Assistito
                            </h5>
                        </div>
                        <div class="card-body p-4">
                            <?php if ($anamnesi): ?>
                                <ul class="list-unstyled mb-0 d-flex flex-column gap-3">
                                    
                                    <li>
                                        <strong class="d-block small text-muted text-uppercase mb-1">Allergie e Intolleranze</strong>
                                        <div class="bg-light p-2 rounded-3 small text-dark">
                                            <?= nl2br(htmlspecialchars($anamnesi['allergie_intolleranze'] ?: 'Nessuna indicata')) ?>
                                        </div>
                                    </li>
                                    
                                    <li>
                                        <strong class="d-block small text-muted text-uppercase mb-1">Patologie Pregresse</strong>
                                        <div class="bg-light p-2 rounded-3 small text-dark">
                                            <?= nl2br(htmlspecialchars($anamnesi['patologie_pregresse'] ?: 'Nessuna indicata')) ?>
                                        </div>
                                    </li>
                                    
                                    <li>
                                        <strong class="d-block small text-muted text-uppercase mb-1">Farmaci Assunti</strong>
                                        <div class="bg-light p-2 rounded-3 small text-dark">
                                            <?= nl2br(htmlspecialchars($anamnesi['farmaci_assunti'] ?: 'Nessuno')) ?>
                                        </div>
                                    </li>
                                    
                                    <li>
                                        <strong class="d-block small text-muted text-uppercase mb-1">Interventi Chirurgici</strong>
                                        <div class="bg-light p-2 rounded-3 small text-dark">
                                            <?= nl2br(htmlspecialchars($anamnesi['interventi_chirurgici'] ?: 'Nessuno')) ?>
                                        </div>
                                    </li>

                                    <li>
                                        <strong class="d-block small text-muted text-uppercase mb-1">Traumi o Fratture</strong>
                                        <div class="bg-light p-2 rounded-3 small text-dark">
                                            <?= nl2br(htmlspecialchars($anamnesi['traumi_o_fratture'] ?: 'Nessuno')) ?>
                                        </div>
                                    </li>

                                    <li>
                                        <strong class="d-block small text-muted text-uppercase mb-1">Esami Clinici Recenti</strong>
                                        <div class="bg-light p-2 rounded-3 small text-dark">
                                            <?= nl2br(htmlspecialchars($anamnesi['esami_clinici_recenti'] ?: 'Nessuno')) ?>
                                        </div>
                                    </li>
                                    
                                    <li class="border-top pt-3 mt-1">
                                        <div class="row g-2">
                                            <div class="col-6">
                                                <strong class="d-block small text-muted text-uppercase mb-1">Fumo</strong>
                                                <div class="bg-light p-2 rounded-3 small text-dark"><?= htmlspecialchars($anamnesi['fumo'] ?: 'No') ?></div>
                                            </div>
                                            <div class="col-6">
                                                <strong class="d-block small text-muted text-uppercase mb-1">Alcol</strong>
                                                <div class="bg-light p-2 rounded-3 small text-dark"><?= htmlspecialchars($anamnesi['alcol'] ?: 'No') ?></div>
                                            </div>
                                        </div>
                                    </li>

                                    <li>
                                        <strong class="d-block small text-muted text-uppercase mb-1 mt-2">Dati Fisici</strong>
                                        <div class="d-flex gap-2">
                                            <?php if(!empty($anamnesi['altezza'])): ?>
                                                <span class="badge bg-light text-dark border">Altezza: <?= $anamnesi['altezza'] ?> cm</span>
                                            <?php endif; ?>
                                            <?php if(!empty($anamnesi['peso'])): ?>
                                                <span class="badge bg-light text-dark border">Peso: <?= $anamnesi['peso'] ?> kg</span>
                                            <?php endif; ?>
                                        </div>
                                    </li>
                                    
                                    <?php if(!empty($anamnesi['note_aggiuntive'])): ?>
                                    <li>
                                        <strong class="d-block small text-muted text-uppercase mb-1 mt-2">Note Anamnesi</strong>
                                        <div class="bg-light p-2 rounded-3 small text-dark">
                                            <?= nl2br(htmlspecialchars($anamnesi['note_aggiuntive'])) ?>
                                        </div>
                                    </li>
                                    <?php endif; ?>
                                    
                                </ul>
                            <?php else: ?>
                                <p class="text-muted small text-center mb-0 py-3">Nessuna anamnesi registrata.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            
        </main>
    </div>

<?php include 'includes/footer.php'; ?>
