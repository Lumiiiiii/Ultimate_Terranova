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

// Recupera i prossimi appuntamenti (per la sezione "Prossimi Appuntamenti")
$upcomingEvents = $patientManager->getUpcomingEvents(5);

// Dati per il grafico Registrazioni (Ultimi 30 giorni)
$registrationsData = $patientManager->getRegistrationsLastMonth();
$regMap = [];
foreach ($registrationsData as $row) {
    $regMap[$row['data']] = $row['totale'];
}
$regLabels = [];
$regCounts = [];
for ($i = 29; $i >= 0; $i--) {
    $dateObj = new DateTime("-$i days");
    $dateStr = $dateObj->format('Y-m-d');
    $regLabels[] = $dateObj->format('d/m');
    $regCounts[] = $regMap[$dateStr] ?? 0;
}

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

            <!-- ── Prossimi Appuntamenti ── -->
            <div class="col-md-6">
                <div class="card border-0 shadow-sm rounded-4 overflow-hidden bg-white h-100">
                    <div class="card-header bg-transparent border-bottom py-3 px-4 d-flex justify-content-between align-items-center">
                        <h5 class="fw-bold mb-0">Prossimi Appuntamenti</h5>
                        <a href="calendario.php" class="btn btn-sm btn-gradient px-3 py-1 rounded-3" style="font-size:0.78rem;">Vedi calendario</a>
                    </div>
                    <div style="max-height: 230px; overflow-y: auto;">
                        <?php if (empty($upcomingEvents)): ?>
                            <div class="p-4 text-center text-muted">
                                <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" fill="none" viewBox="0 0 24 24" stroke="currentColor" class="mb-2 opacity-40"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                                <div class="small">Nessun appuntamento in programma.</div>
                            </div>
                        <?php else: ?>
                            <?php foreach ($upcomingEvents as $ev): ?>
                                <?php
                                    $dt     = new DateTime($ev['start']);
                                    $giorno = $dt->format('d/m/Y');
                                    $ora    = $dt->format('H:i');
                                    $colore = !empty($ev['color']) ? $ev['color'] : '#2ecc71';
                                ?>
                                <div class="px-4 py-2 d-flex align-items-center gap-3 border-bottom hover-lift"
                                     style="cursor:pointer" onclick="window.location.href='calendario.php'">
                                    <span class="event-dot" style="background:<?= htmlspecialchars($colore) ?>; width:12px; height:12px; border-radius:50%; flex-shrink:0;"></span>
                                    <div class="flex-grow-1 overflow-hidden">
                                        <div class="fw-semibold text-dark small text-truncate"><?= htmlspecialchars($ev['title']) ?></div>
                                        <div class="text-muted" style="font-size:0.75rem;"><?= $giorno ?> alle <?= $ora ?></div>
                                    </div>
                                    <span class="badge rounded-pill px-2 py-1" style="background:<?= htmlspecialchars($colore) ?>20; color:<?= htmlspecialchars($colore) ?>; font-size:0.7rem; font-weight:600;"><?= $ora ?></span>
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

            <div class="col-12 mt-2 mb-3 align-items-center position-relative">
                <div class="card border-0 rounded-4 shadow-sm p-2 search-card border border-primary border-opacity-25" style="background-color: var(--bg-card); box-shadow: 0 4px 20px rgba(var(--color-primary-rgb), 0.1) !important;">
                    <div class="input-group input-group-lg align-items-center">
                        <span class="input-group-text bg-transparent border-0 text-primary ps-4 pe-2">
                            <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </span>
                        <input type="text" id="search-input" class="form-control border-0 bg-transparent shadow-none fs-5 py-3 fw-medium" 
                               placeholder="Cerca un paziente per avviare istantaneamente una visita..." autocomplete="off">
                        <span class="badge bg-primary bg-opacity-10 text-primary rounded-pill px-3 py-2 me-3 d-none d-md-block">Ricerca Rapida ⚡</span>
                    </div>
                </div>
                <!-- Dropdown risultati ricerca -->
                <div id="search-results" class="position-absolute w-100 shadow-lg rounded-4 mt-2 border border-light" style="display:none; top:100%; left:0; z-index:1050; max-height:400px; overflow-y:auto; background-color: var(--bg-card); padding-right: 5px;"></div>
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
                        <textarea id="quick-notes" class="form-control border-0 bg-light rounded-3 p-3 mb-3 flex-grow-1" placeholder="Scrivi un promemoria qui..." style="resize: none; border-left: 3px solid #f6c23e !important; box-shadow: inset 0 2px 4px rgba(0,0,0,0.02); min-height: 180px;"><?= htmlspecialchars($noteText) ?></textarea>
                        
                        <div class="mt-auto d-flex justify-content-between align-items-center">
                            <small id="save-status" class="opacity-75">Modifiche salvate in automatico</small>
                            <!-- Bottone rimosso, salva tutto da solo -->
                        </div>
                    </div>
                </div>
            </div>

            <!-- ── COLONNA 2: Grafico Andamento Registrazioni ── -->
            <div class="col-md-4">
                <div class="card border-0 shadow-sm rounded-4 bg-white h-100 p-3">
                    <div class="card-header bg-transparent border-0 d-flex justify-content-between align-items-center mb-2">
                        <h5 class="fw-bold mb-0">Nuovi Pazienti</h5>
                        <span class="badge bg-primary bg-opacity-10 text-primary rounded-pill px-3 py-1 small">Ultimi 30 giorni</span>
                    </div>
                    <div class="card-body p-0 d-flex align-items-end h-100" style="min-height: 200px; position: relative;">
                        <!-- Canvas per Chart.js -->
                        <canvas id="registrationsChart"></canvas>
                    </div>
                </div>
            </div>

            <!-- ── COLONNA 3: Medicinali (Esistente) ── -->
            <div class="col-md-4">
                <a href="catalogo_gestione.php" class="card h-100 border-0 shadow-sm p-4 text-decoration-none glass hover-lift rounded-4">
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
                        <button type="button" id="confirmDeleteBtn" class="btn btn-danger px-4 py-2 rounded-3 fw-semibold">Elimina ora</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- SweetAlert2 & Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            
            // Inizializzazione Grafico Registrazioni
            const ctxReg = document.getElementById('registrationsChart');
            if (ctxReg) {
                // Recuperiamo i dati da PHP
                const labels = <?= json_encode($regLabels) ?>;
                const dataPoints = <?= json_encode($regCounts) ?>;
                
                // Creazione gradiente
                let gradient = ctxReg.getContext('2d').createLinearGradient(0, 0, 0, 400);
                gradient.addColorStop(0, 'rgba(46, 204, 113, 0.4)'); // Colore primary con opacità
                gradient.addColorStop(1, 'rgba(46, 204, 113, 0.0)');

                new Chart(ctxReg, {
                    type: 'line',
                    data: {
                        labels: labels,
                        datasets: [{
                            label: 'Nuovi Pazienti',
                            data: dataPoints,
                            borderColor: '#2ecc71', // var(--color-primary)
                            borderWidth: 3,
                            backgroundColor: gradient,
                            fill: true,
                            tension: 0.4, // Linea curva "smooth"
                            pointBackgroundColor: '#ffffff',
                            pointBorderColor: '#2ecc71',
                            pointBorderWidth: 2,
                            pointRadius: 4,
                            pointHoverRadius: 6
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
                                displayColors: false
                            }
                        },
                        scales: {
                            x: {
                                grid: { display: false },
                                ticks: { 
                                    maxTicksLimit: 6,
                                    color: '#64748b',
                                    font: { size: 11 }
                                }
                            },
                            y: {
                                grid: { color: '#f1f5f9', borderDash: [4, 4] },
                                ticks: { 
                                    stepSize: 1, 
                                    color: '#64748b',
                                    font: { size: 11 }
                                },
                                beginAtZero: true
                            }
                        },
                        interaction: {
                            intersect: false,
                            mode: 'index',
                        },
                    }
                });
            }

            // ── RICERCA PAZIENTI ──────────────────────────────────────────
            const searchInput = document.getElementById('search-input');
            const searchResults = document.getElementById('search-results');
            let searchTimer;

            if (searchInput && searchResults) {
                searchInput.addEventListener('input', function () {
                    clearTimeout(searchTimer);
                    const query = this.value.trim();

                    if (query.length < 2) {
                        searchResults.style.display = 'none';
                        searchResults.innerHTML = '';
                        return;
                    }

                    searchTimer = setTimeout(() => {
                        fetch('ajax_handlers.php?action=search_pazienti&q=' + encodeURIComponent(query))
                            .then(r => r.json())
                            .then(data => {
                                if (!data.results || data.results.length === 0) {
                                    searchResults.innerHTML = '<div class="p-3 text-center text-muted small">Nessun risultato per "' + query + '"</div>';
                                    searchResults.style.display = 'block';
                                    return;
                                }
                                let html = '';
                                data.results.forEach(p => {
                                    const initial = p.nome_cognome.charAt(0).toUpperCase();
                                    const eta = p.eta ? p.eta + ' anni' : '';
                                    
                                    const linkVisita = p.ha_anamnesi ? `visita_nuova.php?paziente_id=${p.id}` : `visita_anamnesi.php?paziente_id=${p.id}`;
                                    const btnIcon = p.ha_anamnesi 
                                        ? `<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>` 
                                        : `<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>`;
                                    const btnText = p.ha_anamnesi ? 'Nuova Visita' : 'Anamnesi';
                                    const btnBadge = p.ha_anamnesi ? '' : '<span class="position-absolute top-0 start-100 translate-middle p-1 bg-danger border border-light rounded-circle"><span class="visually-hidden">Nuovo</span></span>';

                                    html += `
                                        <div class="d-flex align-items-center justify-content-between px-4 py-3 border-bottom hover-lift">
                                            <a href="paziente_dettaglio.php?id=${p.id}" class="d-flex align-items-center gap-3 text-decoration-none flex-grow-1" style="cursor:pointer">
                                                <div class="avatar-circle bg-light text-primary flex-shrink-0" style="width:36px;height:36px;font-size:0.85rem;">${initial}</div>
                                                <div class="overflow-hidden pe-2">
                                                    <div class="fw-semibold text-dark small text-truncate">${p.nome_cognome}</div>
                                                    <div class="text-muted text-truncate" style="font-size:0.75rem;">${eta}${p.telefono ? ' • ' + p.telefono : ''}${p.email ? ' • ' + p.email : ''}</div>
                                                </div>
                                            </a>
                                            <a href="${linkVisita}" class="btn btn-sm btn-gradient rounded-pill px-3 py-1 shadow-sm d-flex align-items-center gap-1 position-relative flex-shrink-0" style="font-size:0.75rem;">
                                                ${btnIcon} <span class="d-none d-sm-inline">${btnText}</span>
                                                ${btnBadge}
                                            </a>
                                        </div>`;
                                });
                                searchResults.innerHTML = html;
                                searchResults.style.display = 'block';
                            })
                            .catch(err => {
                                console.error('Errore ricerca:', err);
                                searchResults.style.display = 'none';
                            });
                    }, 300);
                });

                // Chiudi risultati se si clicca fuori
                document.addEventListener('click', function (e) {
                    if (!searchInput.contains(e.target) && !searchResults.contains(e.target)) {
                        searchResults.style.display = 'none';
                    }
                });
            }

            // ── ELIMINAZIONE PAZIENTE (CON MODAL) ─────────────────────────
            let pazienteIdToDelete = null;
            const deleteModalEl = document.getElementById('deleteConfirmModal');
            const confirmDeleteBtn = document.getElementById('confirmDeleteBtn');

            if (deleteModalEl) {
                const deleteModal = new bootstrap.Modal(deleteModalEl);

                window.deletePatient = function(id, nome) {
                    pazienteIdToDelete = id;
                    document.getElementById('deletePazienteNome').textContent = nome;
                    deleteModal.show();
                };

                if (confirmDeleteBtn) {
                    confirmDeleteBtn.addEventListener('click', function() {
                        if (!pazienteIdToDelete) return;

                        const originalText = confirmDeleteBtn.innerHTML;
                        confirmDeleteBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Eliminazione...';
                        confirmDeleteBtn.disabled = true;

                        const formData = new FormData();
                        formData.append('action', 'delete_paziente');
                        formData.append('id', pazienteIdToDelete);

                        fetch('ajax_handlers.php', {
                            method: 'POST',
                            body: formData
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                Swal.fire({
                                    title: 'Eliminato!', text: 'Il paziente è stato rimosso.',
                                    icon: 'success', timer: 1500, showConfirmButton: false,
                                    customClass: { popup: 'rounded-4 border-0 shadow' }
                                }).then(() => window.location.reload());
                            } else {
                                Swal.fire({ title: 'Errore', text: data.error || 'Impossibile eliminare il paziente.', icon: 'error', customClass: { popup: 'rounded-4 border-0 shadow' } });
                                confirmDeleteBtn.innerHTML = originalText;
                                confirmDeleteBtn.disabled = false;
                                deleteModal.hide();
                            }
                        })
                        .catch(error => {
                            console.error('Errore durante l\'eliminazione:', error);
                            Swal.fire({ title: 'Errore di Rete', text: 'Si è verificato un errore di rete.', icon: 'error', customClass: { popup: 'rounded-4 border-0 shadow' } });
                            confirmDeleteBtn.innerHTML = originalText;
                            confirmDeleteBtn.disabled = false;
                        });
                    });
                }
            }

            // ── SALVATAGGIO AUTOMATICO NOTE VELOCI ─────────────────────────
            const notesTextarea = document.getElementById('quick-notes');
            const saveStatus = document.getElementById('save-status');
            let typingTimer;
            const doneTypingInterval = 1000;

            if (notesTextarea && saveStatus) {
                notesTextarea.addEventListener('input', function () {
                    clearTimeout(typingTimer);
                    saveStatus.textContent = "Salvataggio in corso...";
                    saveStatus.classList.add('text-warning');
                    saveStatus.classList.remove('text-success', 'text-danger');

                    typingTimer = setTimeout(saveNotes, doneTypingInterval);
                });
            }

            function saveNotes() {
                const text = notesTextarea.value;

                fetch('ajax_notes.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ testo: text })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const now = new Date();
                        const timeString = now.getHours().toString().padStart(2, '0') + ':' + now.getMinutes().toString().padStart(2, '0');
                        saveStatus.textContent = "Salvato alle " + timeString;
                        saveStatus.classList.remove('text-warning', 'text-danger');
                        saveStatus.classList.add('text-success');
                    } else {
                        throw new Error(data.error || "Errore sconosciuto");
                    }
                })
                .catch(error => {
                    console.error('Errore durante il salvataggio:', error);
                    saveStatus.textContent = "Errore durante il salvataggio!";
                    saveStatus.classList.remove('text-warning', 'text-success');
                    saveStatus.classList.add('text-danger');
                });
            }
        });
    </script>

    <?php include 'includes/footer.php'; ?>