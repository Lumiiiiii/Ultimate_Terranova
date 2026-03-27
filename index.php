<?php
ob_start(); // Sincronizza l'invio della pagina al server (previene FOUC)
session_start(); // Avvia la sessione

// Se l'utente non è loggato, reindirizza alla pagina di login
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

// Include il file che contiene le credenziali e la connessione al database
include __DIR__ . '/config/database.php'; 

// Include la definizione della classe Patient (dove sono scritte le funzioni CRUD)
include __DIR__ . '/includes/patient.php'; 

// Include la definizione della classe Note per gestire il promemoria veloce
include __DIR__ . '/includes/Note.php'; 

// Crea un'istanza della classe Patient per poter usare i suoi metodi
$patientManager = new Patient(); 

// Crea un'istanza della classe Note
$noteManager = new Note();

// Chiama il metodo che conta quanti record totali ci sono nella tabella pazienti
$totalPatients = $patientManager->countPatients(); 

// Recupera i pazienti recenti (per la sezione "Pazienti Recenti")
$allPatients = $patientManager->getRecentPatients(5);

// Recupera le visite recenti (per la sezione "Visite Recenti")
$recentVisits = $patientManager->getRecentVisits();

// Recupera la nota veloce
$noteText = $noteManager->getNote();
?>

<?php
$pageTitle = "Dashboard";
$currentPage = "index";
include 'includes/header.php';
include 'includes/sidebar.php';
?>

    <div class="main-content">
    <main class="container-xl py-5">
        <header class="mb-5">
            <h2 class="fw-bold mb-1">Benvenuta/o</h2>
            <p class="text-muted">Ecco una panoramica della tua attività.</p>
        </header>

        <div class="row g-4">
            
            <!-- ── Totale Pazienti ── -->
            <div class="col-md-3">
                <div class="card h-100 border-0 shadow-sm p-4 rounded-4 bg-white">
                    <p class="text-uppercase small fw-bold text-muted mb-1">Totale Pazienti</p>
                    <div class="d-flex align-items-baseline gap-2">
                        <span class="display-4 fw-bold"><?= $totalPatients ?></span>
                        <span class="small text-muted">assistiti</span>
                    </div>

                </div>
            </div>

            <!-- ── Pazienti Recenti ── -->
            <div class="col-md-6">
                <div class="card border-0 shadow-sm rounded-4 overflow-hidden bg-white h-100">
                    <div class="card-header bg-transparent border-bottom py-3 px-4">
                        <h5 class="fw-bold mb-0">Pazienti Recenti</h5>
                    </div>
                    <div style="max-height: 190px; overflow-y: auto;">
                        <?php if (empty($allPatients)): ?>
                            <div class="p-4 text-center text-muted">
                                Nessun paziente registrato.
                            </div>
                        <?php else: ?>
                            <?php foreach ($allPatients as $patient): ?>
                                <div class="px-4 py-2 d-flex justify-content-between align-items-center border-bottom hover-lift" 
                                     style="cursor:pointer" onclick="window.location.href='paziente_dettaglio.php?id=<?= $patient['id'] ?>'">
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="avatar-circle bg-light text-primary" style="width:32px;height:32px;font-size:0.8rem;">
                                            <?= strtoupper(substr($patient['nome_cognome'], 0, 1)) ?>
                                        </div>
                                        <div>
                                            <div class="fw-semibold text-dark small"><?= $patient['nome_cognome'] ?></div>
                                            <div class="text-muted" style="font-size:0.75rem;"><?= $patient['eta'] ?> anni • <?= $patient['telefono'] ?></div>
                                        </div>
                                    </div>
                                    <div class="d-flex align-items-center gap-2">
                                        <!-- Pulsante Elimina: event.stopPropagation() evita che cliccando qui si apra la scheda paziente -->
                                        <button class="btn btn-sm btn-delete-paziente p-1 border-0" 
                                                onclick="event.stopPropagation(); deletePatient(<?= $patient['id'] ?>, '<?= addslashes($patient['nome_cognome']) ?>')">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                        </button>
                                        <span class="text-muted">›</span>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- ── Nuovo Paziente (rimpicciolito) ── -->
            <div class="col-md-3">
                <a href="paziente_nuovo.php" class="card h-100 border-0 shadow-sm p-3 text-decoration-none text-white hover-lift rounded-4 d-flex align-items-center justify-content-center" 
                   style="background: linear-gradient(135deg, var(--color-primary), var(--color-accent));">
                    <div class="text-center">
                        <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" class="mb-2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                        </svg>
                        <h6 class="fw-bold mb-1">Nuovo Paziente</h6>
                        <p class="small opacity-75 mb-0">Registra una nuova scheda →</p>
                    </div>
                </a>
            </div>

            <div class="col-12 mt-2 mb-1">
                <div class="card border-0 rounded-4 shadow-sm p-2 hover-lift" style="background: linear-gradient(135deg, #ffffff 0%, #f4f7fe 100%); border: 1px solid rgba(59, 130, 246, 0.15) !important;">
                    <div class="input-group input-group-lg align-items-center">
                        <span class="input-group-text bg-transparent border-0 text-primary ps-4 pe-2">
                            <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </span>
                        <input type="text" id="search-input" class="form-control border-0 bg-transparent shadow-none fs-5 py-3 fw-medium" 
                               placeholder="Cerca paziente per nome, email o telefono..." autocomplete="off">
                    </div>
                </div>
            </div>

            <!-- ── COLONNA 1: Note Veloci (Nuovo) ── -->
            <div class="col-md-4">
                <div class="card border-0 shadow-sm rounded-4 bg-white h-100">
                    <div class="card-header bg-transparent border-bottom py-3 px-4 d-flex justify-content-between align-items-center">
                        <h5 class="fw-bold mb-0">Note Veloci</h5>
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" class="text-warning">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                        </svg>
                    </div>
                    <div class="card-body p-4 d-flex flex-column text-muted">
                        <!-- Area di testo col testo caricato dal DB -->
                        <textarea id="quick-notes" class="form-control border-0 bg-light rounded-3 p-3 text-dark mb-3 flex-grow-1" placeholder="Scrivi un promemoria qui..." style="resize: none; background-color: #fdfbf7 !important; border-left: 3px solid #f6c23e !important; box-shadow: inset 0 2px 4px rgba(0,0,0,0.02); min-height: 180px;"><?= htmlspecialchars($noteText) ?></textarea>
                        
                        <div class="mt-auto d-flex justify-content-between align-items-center">
                            <small id="save-status" class="opacity-75">Modifiche salvate in automatico</small>
                            <!-- Bottone rimosso, salva tutto da solo -->
                        </div>
                    </div>
                </div>
            </div>

            <!-- ── COLONNA 2: Visite Recenti ── -->
            <div class="col-md-4">
                <div class="card border-0 shadow-sm rounded-4 overflow-hidden bg-white h-100">
                    <div class="card-header bg-transparent border-bottom py-3 px-4 d-flex justify-content-between align-items-center">
                        <h5 class="fw-bold mb-0">Visite Recenti</h5>
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" class="text-primary" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                    </div>
                    <div id="visits-list" class="flex-grow-1" style="max-height: 420px; overflow-y: auto;">
                        <?php if (empty($recentVisits)): ?>
                            <div class="p-5 text-center text-muted h-100 d-flex align-items-center justify-content-center">
                                <div>Nessuna visita registrata.</div>
                            </div>
                        <?php else: ?>
                            <?php foreach ($recentVisits as $visit): ?>
                                <div class="px-4 py-3 d-flex justify-content-between align-items-center border-bottom hover-lift" 
                                     style="cursor:pointer" onclick="window.location.href='paziente_dettaglio.php?id=<?= $visit['paziente_id'] ?>'">
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="avatar-circle bg-light text-primary">
                                            <?= strtoupper(substr($visit['nome_cognome'], 0, 1)) ?>
                                        </div>
                                        <div>
                                            <div class="fw-semibold text-dark"><?= $visit['nome_cognome'] ?></div>
                                            <div class="text-muted small">
                                                <?= $visit['data_visita'] ? date('d/m/Y', strtotime($visit['data_visita'])) : 'Data n/d' ?>
                                                <?php if (!empty($visit['motivazione'])): ?>
                                                    • <?= mb_strimwidth(htmlspecialchars($visit['motivazione']), 0, 30, '…') ?>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                    <span class="text-muted">›</span>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- ── COLONNA 3: Medicinali (Esistente) ── -->
            <div class="col-md-4">
                <a href="medicinali_gestione.php" class="card h-100 border-0 shadow-sm p-4 text-decoration-none glass hover-lift rounded-4">
                    <div class="d-flex flex-column h-100 justify-content-center align-items-center text-center">
                        <div class="p-3 bg-light rounded-circle mb-3">
                            <!-- Icona SVG: cassetto / archivio -->
                            <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" fill="none" viewBox="0 0 24 24" stroke="var(--color-primary)" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m20.25 7.5-.625 10.632a2.25 2.25 0 0 1-2.247 2.118H6.622a2.25 2.25 0 0 1-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125Z" />
                            </svg>
                        </div>
                        <h5 class="fw-bold text-dark mb-2">Archivio</h5>
                        <p class="small text-muted mb-0">Gestisci i rimedi e integratori naturali →</p>
                    </div>
                </a>
            </div>
        </div>
    </main>
    </div><!-- fine .main-content -->

    <!-- ══ MODAL DI CONFERMA ELIMINAZIONE ══════════════════════════════════════ -->
    <div class="modal fade" id="deleteConfirmModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg rounded-4">
                <div class="modal-body p-5 text-center">
                    <div class="mb-4">
                        <div class="d-inline-flex bg-danger bg-opacity-10 text-danger rounded-circle p-3 mb-3">
                            <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                            </svg>
                        </div>
                        <h4 class="fw-bold mb-2">Conferma eliminazione</h4>
                        <p class="text-muted">Sei sicuro di voler eliminare definitivamente <span id="deletePazienteNome" class="fw-bold text-dark"></span>? <br>L'azione cancellerà anche tutte le visite e le anamnesi collegate.</p>
                    </div>
                    <div class="d-grid gap-2 d-md-flex justify-content-center">
                        <button type="button" class="btn btn-light px-4 py-2 rounded-3 fw-semibold" data-bs-dismiss="modal">Annulla</button>
                        <button type="button" id="confirmDeleteBtn" class="btn btn-danger px-4 py-2 rounde    <?php include 'includes/footer.php'; ?>
);
                    saveStatus.classList.add('text-danger');
                });
            }
        });
    </script>
</body>
</html>