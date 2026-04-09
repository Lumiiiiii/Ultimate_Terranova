<?php
ob_start();
session_start();
if(!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true){
    header('Location: login.php');
    exit;
}
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/Patient.php';
require_once __DIR__ . '/includes/Visit.php';

$id = $_GET['id'] ?? 0;
$patientManager = new Patient();
$visitManager = new Visit();
$patient = $patientManager->getPatient($id);
if(!$patient){
    header('Location: index.php');
    exit;
}
$visits = $visitManager->getVisitHistory($id);
$haFattoAnamnesi = $patientManager->checkAnamnesi($id);

// Prepara i dati per il grafico del Sonno
$sleepTrend = $patientManager->getSleepTrend($id);
$sleepLabels = [];
$sleepData = [];
foreach ($sleepTrend as $row) {
    if (!empty($row['data_visita'])) {
        $sleepLabels[] = date('d/m/Y', strtotime($row['data_visita']));
        $sleepData[] = (float)$row['ore_sonno'];
    }
}

$pageTitle = htmlspecialchars($patient['nome_cognome']) . " - Dettaglio";
$currentPage = "index";
include 'includes/header.php';
include 'includes/sidebar.php';
?>

    <!-- ══ CONTENUTO PRINCIPALE ══════════════════════════════════════════════ -->
    <div class="main-content">
        <main class="container-xl py-5">
            
            <!-- Header della pagina -->
            <header class="mb-5">
                <div class="d-flex align-items-center gap-3 mb-1">
                    <a href="index.php" class="text-muted text-decoration-none d-flex align-items-center gap-1 hover-lift">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
                        </svg>
                        Dashboard
                    </a>
                    <span class="text-muted">/</span>
                    <span class="text-dark fw-semibold"><?= htmlspecialchars($patient['nome_cognome']) ?></span>
                </div>
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h2 class="fw-bold mb-1">Dettaglio Paziente</h2>
                        <p class="text-muted mb-0">Profilo e storico visite di <?= htmlspecialchars($patient['nome_cognome']) ?>.</p>
                    </div>
                </div>
            </header>

            <div class="row g-4">
                
                <!-- Colonna Sinistra: Profilo Paziente e Grafici -->
                <div class="col-lg-4">
                    <div class="d-flex flex-column gap-3 h-100">
                    
                        <!-- Card Profilo Paziente -->
                        <div class="card border-0 shadow-sm rounded-4 bg-white text-center p-3 position-relative">
                        
                        <!-- Bottone Elimina Paziente -->
                        <button type="button" class="btn btn-sm btn-outline-danger position-absolute border-0 hover-lift" style="top: 10px; right: 10px;" data-bs-toggle="modal" data-bs-target="#deleteConfirmModal" title="Elimina Paziente">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                            </svg>
                        </button>
                        
                        <h5 class="fw-bold mb-3 mt-1"><?= htmlspecialchars($patient['nome_cognome']) ?></h5>

                        <?php if (!$haFattoAnamnesi): ?>
                            <a href="visita_anamnesi.php?paziente_id=<?= $id ?>" class="btn btn-gradient w-100 mb-3 rounded-3 shadow-sm d-flex align-items-center justify-content-center gap-2" style="padding: 0.5rem;">
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                                Fai anamnesi (prima visita)
                            </a>
                        <?php else: ?>
                            <a href="visita_nuova.php?paziente_id=<?= $id ?>" class="btn btn-gradient w-100 mb-3 rounded-3 shadow-sm d-flex align-items-center justify-content-center gap-2" style="padding: 0.5rem;">
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                                </svg>
                                Nuova visita
                            </a>
                        <?php endif; ?>

                        <!-- Dettagli anagrafici -->
                        <div class="text-start border-top pt-3 small flex-grow-1">
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted d-flex align-items-center gap-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg> Età
                                </span>
                                <span class="fw-medium text-dark"><?= !empty($patient['eta']) ? $patient['eta'] . ' anni' : '-' ?></span>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted d-flex align-items-center gap-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                                    </svg> Telefono
                                </span>
                                <span class="fw-medium text-dark text-end ms-3"><?= !empty($patient['telefono']) ? htmlspecialchars($patient['telefono']) : '-' ?></span>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted d-flex align-items-center gap-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                    </svg> Email
                                </span>
                                <span class="fw-medium text-dark text-end ms-3"><?= !empty($patient['email']) ? htmlspecialchars($patient['email']) : '-' ?></span>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted d-flex align-items-center gap-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                    </svg> Professione
                                </span>
                                <span class="fw-medium text-dark text-end ms-3"><?= !empty($patient['professione']) ? htmlspecialchars($patient['professione']) : '-' ?></span>
                            </div>
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <span class="text-muted d-flex align-items-center gap-2" style="white-space: nowrap;">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                    </svg> Indirizzo
                                </span>
                                <span class="fw-medium text-dark text-end ms-3"><?= !empty($patient['indirizzo']) ? htmlspecialchars($patient['indirizzo']) : '-' ?></span>
                            </div>
                        </div>
                        
                        <!-- Bottone Modifica -->
                        <button type="button" class="btn btn-light w-100 rounded-3 mt-3 py-1 shadow-sm text-muted fw-medium d-flex align-items-center justify-content-center gap-2 hover-lift" data-bs-toggle="modal" data-bs-target="#editModal">
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                              <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                            </svg>
                            Modifica Dati
                        </button>
                        </div> <!-- Fine Card Profilo -->

                        <!-- Grafico Ore Sonno -->
                        <div class="card border-0 shadow-sm rounded-4 bg-white p-3 d-flex flex-column">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <h6 class="fw-bold mb-0">Sonno</h6>
                                <span class="badge bg-primary bg-opacity-10 text-primary rounded-pill px-2 py-1 small fw-medium" style="font-size: 0.75rem;">Ore/Notte</span>
                            </div>
                            <div style="height: 120px; position: relative;">
                                <?php if(empty($sleepData)): ?>
                                    <div class="d-flex align-items-center justify-content-center h-100 bg-light rounded-3 border">
                                        <p class="text-muted mb-0 small text-center p-2"><i class="bi bi-info-circle me-1"></i> Nessun dato registrato</p>
                                    </div>
                                <?php endif; ?>
                                <canvas id="sleepChart" style="<?= empty($sleepData) ? 'display:none;' : '' ?>"></canvas>
                            </div>
                        </div>

                    </div> <!-- Fine Colonna d-flex -->
                </div>
                
                <!-- Colonna Destra: Storico Visite -->
                <div class="col-lg-8">
                    <div class="card border-0 shadow-sm rounded-4 bg-white h-100">
                            <div class="card-header bg-transparent border-bottom py-3 px-4 d-flex justify-content-between align-items-center">
                            <h5 class="fw-bold mb-0">Storico Visite</h5>
                            <span class="badge bg-light text-primary border rounded-pill px-3 py-2">
                                <?= count($visits) ?> visite totali
                            </span>
                        </div>
                        <div class="card-body p-0">
                            <?php if (empty($visits)): ?>
                                <div class="text-center p-5 text-muted">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1" class="mb-3 opacity-50 mx-auto">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                    <p class="mb-0">Nessuna visita registrata per questo paziente.</p>
                                </div>
                            <?php else: ?>
                                <div class="list-group list-group-flush border-0">
                                    <?php foreach ($visits as $visit): ?>
                                        <div class="list-group-item p-4 bg-transparent border-bottom hover-lift">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <div class="d-flex align-items-start gap-3">
                                                    <div class="bg-primary bg-opacity-10 text-primary rounded-3 p-3 flex-shrink-0">
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                                                        </svg>
                                                    </div>
                                                    <div>
                                                        <h6 class="fw-bold mb-1 text-dark">
                                                            Visita del <?= date('d/m/Y', strtotime($visit['data_visita'])) ?>
                                                        </h6>
                                                        <p class="text-muted small mb-0">
                                                            <?= htmlspecialchars($visit['motivazione'] ?? 'Nessuna motivazione indicata') ?>
                                                        </p>
                                                    </div>
                                                </div>
                                                <a href="visita_dettaglio.php?id=<?= $visit['id'] ?>" class="btn btn-outline-secondary rounded-pill px-3 py-1 text-decoration-none shadow-sm flex-shrink-0 hover-lift">
                                                    <span class="small fw-medium">Dettagli</span>
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" class="ms-1">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                                                    </svg>
                                                </a>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                
            </div>
        </main>
    </div>

    <!-- Edit Modal -->
    <div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg rounded-4">
                <div class="modal-header border-bottom-0 py-4 px-4 bg-light bg-opacity-50">
                    <h5 class="modal-title fw-bold" id="editModalLabel">Modifica Dati Paziente</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body px-4 pb-4 pt-4">
                    <form id="edit-form">
                        <input type="hidden" name="action" value="update_patient">
                        <input type="hidden" name="id" value="<?= $id ?>">
                        
                        <div class="mb-3">
                            <label class="form-label small fw-medium text-muted">Nome e Cognome <span class="text-danger">*</span></label>
                            <input type="text" name="nome_cognome" required class="form-control rounded-3 py-2" value="<?= htmlspecialchars($patient['nome_cognome']) ?>">
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label small fw-medium text-muted">Data Nascita</label>
                                <input type="text" class="form-control rounded-3 py-2" id="data_visuale" 
                                       placeholder="Seleziona data" autocomplete="off" readonly
                                       value="<?php
                                           $val = $patient['data_nascita'] ?? '';
                                           if ($val && strpos($val, '-') !== false) {
                                               $parts = explode('-', $val);
                                               echo $parts[2] . '/' . $parts[1] . '/' . $parts[0];
                                           }
                                       ?>">
                                <input type="hidden" name="data_nascita" id="data_nascita" value="<?= $patient['data_nascita'] ?? '' ?>">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label small fw-medium text-muted">Telefono</label>
                                <input type="tel" name="telefono" class="form-control rounded-3 py-2" value="<?= htmlspecialchars($patient['telefono'] ?? '') ?>">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-medium text-muted">Email</label>
                            <input type="email" name="email" class="form-control rounded-3 py-2" value="<?= htmlspecialchars($patient['email'] ?? '') ?>">
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-medium text-muted">Professione</label>
                            <input type="text" name="professione" class="form-control rounded-3 py-2" value="<?= htmlspecialchars($patient['professione'] ?? '') ?>">
                        </div>
                        <div class="mb-4">
                            <label class="form-label small fw-medium text-muted">Indirizzo</label>
                            <textarea name="indirizzo" class="form-control rounded-3" rows="2"><?= htmlspecialchars($patient['indirizzo'] ?? '') ?></textarea>
                        </div>
                        
                        <div class="d-flex justify-content-end gap-2 pt-2 border-top mt-4 pt-3">
                            <button type="button" class="btn btn-light rounded-3 px-4 fw-medium text-muted" data-bs-dismiss="modal">Annulla</button>
                            <button type="submit" class="btn btn-save rounded-3 px-4 shadow-sm" id="saveEditBtn">
                                Salva Modifiche
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Conferma Eliminazione -->
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
                        <p class="text-muted">Sei sicuro di voler eliminare definitivamente <strong><?= htmlspecialchars($patient['nome_cognome']) ?></strong>? <br>L'azione cancellerà anche tutte le visite e le anamnesi collegate.</p>
                    </div>
                    <div class="d-grid gap-2 d-md-flex justify-content-center">
                        <button type="button" class="btn btn-light px-4 py-2 rounded-3 fw-semibold" data-bs-dismiss="modal">Annulla</button>
                        <button type="button" id="confirmDeleteBtnDettaglio" class="btn btn-danger px-4 py-2 rounded-3 fw-semibold">Elimina ora</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <script>
        // Gestione modifica paziente
        document.getElementById('edit-form').addEventListener('submit', async function (e) {
            e.preventDefault(); 
            const btn = document.getElementById('saveEditBtn');
            const originalText = btn.innerHTML;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status"></span> Salvataggio...';
            btn.classList.add('disabled');
            
            try {
                const res = await fetch('ajax_handlers.php', { method: 'POST', body: new FormData(this) });
                const data = await res.json();
                
                if (data.success) {
                    Swal.fire({
                        title: 'Aggiornato!', text: 'I dati del paziente sono stati salvati.',
                        icon: 'success', timer: 1500, showConfirmButton: false,
                        customClass: { popup: 'rounded-4 border-0 shadow' }
                    }).then(() => location.reload());
                } else {
                    Swal.fire({ title: 'Errore', text: (data.message || 'Errore nel salvataggio'), icon: 'error', customClass: { popup: 'rounded-4 border-0 shadow' } });
                    btn.innerHTML = originalText;
                    btn.classList.remove('disabled');
                }
            } catch (e) { 
                console.error(e); 
                Swal.fire({ title: 'Errore di Rete', text: 'Impossibile comunicare con il server.', icon: 'error', customClass: { popup: 'rounded-4 border-0 shadow' } });
                btn.innerHTML = originalText;
                btn.classList.remove('disabled');
            }
        });

        // Gestione eliminazione paziente
        document.getElementById('confirmDeleteBtnDettaglio')?.addEventListener('click', function () {
            Swal.fire({
                title: 'Sei sicuro?',
                text: "Questa azione non può essere annullata. Tutti i dati del paziente verranno eliminati.",
                icon: 'warning', showCancelButton: true,
                confirmButtonText: 'Sì, elimina definitivamente', cancelButtonText: 'Annulla',
                customClass: { popup: 'rounded-4 border-0 shadow', confirmButton: 'btn btn-danger px-4 mx-2', cancelButton: 'btn btn-light px-4 mx-2' },
                buttonsStyling: false, reverseButtons: true
            }).then(async (result) => {
                if (result.isConfirmed) {
                    const btn = this;
                    const originalText = btn.innerHTML;
                    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Eliminazione...';
                    btn.disabled = true;

                    const formData = new FormData();
                    formData.append('action', 'delete_paziente');
                    formData.append('id', <?= $id ?>);

                    try {
                        const res = await fetch('ajax_handlers.php', { method: 'POST', body: formData });
                        const data = await res.json();
                        if (data.success) {
                            Swal.fire({ title: 'Eliminato!', text: 'Il paziente è stato rimosso.', icon: 'success', timer: 1500, showConfirmButton: false, customClass: { popup: 'rounded-4 border-0 shadow' } })
                            .then(() => window.location.href = 'index.php');
                        } else {
                            Swal.fire({ title: 'Errore', text: data.error || 'Impossibile eliminare.', icon: 'error', customClass: { popup: 'rounded-4 border-0 shadow' } });
                            btn.innerHTML = originalText; btn.disabled = false;
                        }
                    } catch (error) {
                        console.error(error);
                        Swal.fire({ title: 'Errore di Rete', text: 'Impossibile completare l\'operazione.', icon: 'error', customClass: { popup: 'rounded-4 border-0 shadow' } });
                        btn.innerHTML = originalText; btn.disabled = false;
                    }
                }
            });
        });
    </script>

    <!-- Bootstrap Datepicker & SweetAlert2 & Chart.js -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-datepicker@1.10.0/dist/css/bootstrap-datepicker3.min.css">
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap-datepicker@1.10.0/dist/js/bootstrap-datepicker.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap-datepicker@1.10.0/dist/locales/bootstrap-datepicker.it.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <script>
        // Init Datepicker
        $('#data_visuale').datepicker({
            language: 'it', format: 'dd/mm/yyyy', startView: 2, minViewMode: 0,
            endDate: new Date(), startDate: '01/01/1920', autoclose: true,
            todayHighlight: true, orientation: 'bottom auto'
        }).on('changeDate', function(e) {
            const d = e.date;
            document.getElementById('data_nascita').value = 
                d.getFullYear() + '-' + String(d.getMonth()+1).padStart(2,'0') + '-' + String(d.getDate()).padStart(2,'0');
        });

        // Init Chart.js
        document.addEventListener('DOMContentLoaded', function () {
            const ctxSleep = document.getElementById('sleepChart');
            if (ctxSleep) {
                const labels = <?= json_encode($sleepLabels) ?>;
                const dataPoints = <?= json_encode($sleepData) ?>;

                let gradient = ctxSleep.getContext('2d').createLinearGradient(0, 0, 0, 300);
                gradient.addColorStop(0, 'rgba(59, 130, 246, 0.4)'); // Colore accent (blu) con opacità
                gradient.addColorStop(1, 'rgba(59, 130, 246, 0.0)');

                new Chart(ctxSleep, {
                    type: 'line',
                    data: {
                        labels: labels,
                        datasets: [{
                            label: 'Ore di Sonno',
                            data: dataPoints,
                            borderColor: '#3b82f6', 
                            borderWidth: 3,
                            backgroundColor: gradient,
                            fill: true,
                            tension: 0.4, 
                            pointBackgroundColor: '#ffffff',
                            pointBorderColor: '#3b82f6',
                            pointBorderWidth: 2,
                            pointRadius: 5,
                            pointHoverRadius: 7
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { display: false },
                            tooltip: {
                                backgroundColor: '#1e293b',
                                titleFont: { size: 13, family: "'Inter', sans-serif" },
                                bodyFont: { size: 14, weight: 'bold', family: "'Inter', sans-serif" },
                                padding: 12,
                                cornerRadius: 8,
                                displayColors: false,
                                callbacks: {
                                    label: function(context) { return context.parsed.y + ' ore'; }
                                }
                            }
                        },
                        scales: {
                            x: {
                                grid: { display: false },
                                ticks: { color: '#64748b', font: { size: 11 } }
                            },
                            y: {
                                grid: { color: '#f1f5f9', borderDash: [4, 4] },
                                ticks: { 
                                    stepSize: 1, 
                                    color: '#64748b',
                                    font: { size: 11 }
                                },
                                min: 0,
                                suggestedMax: 10
                            }
                        },
                        interaction: {
                            intersect: false,
                            mode: 'index',
                        },
                    }
                });
            }
        });
    </script>
    <?php include 'includes/footer.php'; ?>
