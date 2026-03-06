<?php
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

// Recupera tutti i pazienti registrati (per la sezione "Pazienti Registrati")
$allPatients = $patientManager->getAllPatients();

// Recupera le visite recenti (per la sezione "Visite Recenti")
$recentVisits = $patientManager->getRecentVisits();

// Recupera la nota veloce
$noteText = $noteManager->getNote();
?>

<!DOCTYPE html>
<html lang="it">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Aequa - Gestionale Naturopatia</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        /* ── VARIABILI CSS GLOBALI ─────────────────────────────────────────────── */
        :root {
            --color-primary: #2ecc71;          /* verde principale */
            --color-primary-dark: #27ae60;     /* verde scuro (hover) */
            --color-accent: #3b82f6;           /* blu accento gradiente */
            --sidebar-width: 260px;            /* larghezza sidebar */
            --sidebar-bg: #1a1a2e;             /* sfondo sidebar: blu scurissimo */
            --sidebar-text: #a0aec0;           /* testo sidebar: grigio chiaro */
            --sidebar-active: #ffffff;         /* testo voce attiva: bianco */
        }

        /* ── CLASSI DI UTILITÀ ─────────────────────────────────────────────────── */

        /* Effetto vetro/glassmorphism */
        .glass { background: rgba(255, 255, 255, 0.7); backdrop-filter: blur(10px); border: 1px solid rgba(255,255,255,0.1); }

        /* Cerchio con l'iniziale del paziente */
        .avatar-circle { width: 40px; height: 40px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: bold; }

        /* Effetto hover: la card si solleva */
        .hover-lift { transition: transform 0.2s ease; }
        .hover-lift:hover { transform: translateY(-5px) !important; }

        /* Bordi arrotondati */
        .rounded-4 { border-radius: 1rem !important; }

        /* ── SIDEBAR (BARRA LATERALE FISSA) ────────────────────────────────────── */

        /* Contenitore principale della sidebar:
           - position: fixed → resta visibile anche quando si scrolla la pagina
           - occupa tutta l'altezza dello schermo (top:0, bottom:0)
           - larghezza definita dalla variabile --sidebar-width */
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            bottom: 0;
            width: var(--sidebar-width);
            background: var(--sidebar-bg);
            color: var(--sidebar-text);
            display: flex;
            flex-direction: column;       /* gli elementi si impilano verticalmente */
            z-index: 1000;                /* sopra al contenuto della pagina */
            box-shadow: 4px 0 15px rgba(0, 0, 0, 0.1); /* ombra a destra */
            transition: transform 0.3s ease;             /* animazione per mobile */
        }

        /* ── LOGO / INTESTAZIONE SIDEBAR ───────────────────────────────────────── */
        .sidebar-header {
            padding: 28px 24px 20px;
            border-bottom: 1px solid rgba(255,255,255,0.08); /* linea sottile separatrice */
        }
        .sidebar-header h4 {
            color: white;
            font-weight: 700;
            font-size: 1.15rem;
            margin: 0;
        }
        .sidebar-header small {
            color: var(--sidebar-text);
            font-size: 0.75rem;
            opacity: 0.7;
        }

        /* ── NAVIGAZIONE (LISTA VOCI) ──────────────────────────────────────────── */
        .sidebar-nav {
            flex: 1;            /* occupa tutto lo spazio disponibile tra header e footer */
            padding: 16px 12px;
            overflow-y: auto;   /* scroll se le voci sono troppe */
        }

        /* Etichetta di sezione (es. "PRINCIPALE", "GESTIONE") */
        .nav-section-label {
            font-size: 0.65rem;
            text-transform: uppercase;     /* tutto maiuscolo */
            letter-spacing: 1.5px;         /* spaziatura tra le lettere */
            color: rgba(255,255,255,0.25); /* quasi invisibile, solo decorativo */
            padding: 12px 16px 6px;
            font-weight: 600;
        }

        /* Singola voce di navigazione (link) */
        .sidebar-nav a {
            display: flex;
            align-items: center;
            gap: 12px;                           /* spazio tra icona e testo */
            padding: 11px 16px;
            color: var(--sidebar-text);
            text-decoration: none;
            border-radius: 10px;                 /* angoli arrotondati */
            font-size: 0.92rem;
            font-weight: 500;
            transition: all 0.2s ease;           /* animazione morbida al passaggio */
            margin-bottom: 2px;
        }

        /* Hover: sfondo leggermente più chiaro + testo bianco */
        .sidebar-nav a:hover {
            background: rgba(255,255,255,0.08);
            color: white;
        }

        /* Voce ATTIVA: sfondo verde semitrasparente + testo bianco */
        .sidebar-nav a.active {
            background: linear-gradient(135deg, rgba(46, 204, 113, 0.15), rgba(59, 130, 246, 0.15));
            color: white;
        }

        /* Icone SVG dentro i link della sidebar */
        .sidebar-nav a svg {
            width: 20px;
            height: 20px;
            flex-shrink: 0;         /* non rimpicciolire l'icona */
            opacity: 0.6;
        }
        .sidebar-nav a:hover svg,
        .sidebar-nav a.active svg {
            opacity: 1;             /* icona piena al hover o se attiva */
        }

        /* ── FOOTER SIDEBAR (ESCI) ─────────────────────────────────────────────── */
        .sidebar-footer {
            padding: 16px 12px;
            border-top: 1px solid rgba(255,255,255,0.08);
        }
        .sidebar-footer a {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 11px 16px;
            color: #e57373;                    /* rosso chiaro */
            text-decoration: none;
            border-radius: 10px;
            font-size: 0.92rem;
            font-weight: 500;
            transition: all 0.2s ease;
        }
        .sidebar-footer a:hover {
            background: rgba(229, 115, 115, 0.1);
            color: #ef5350;
        }
        .sidebar-footer a svg {
            width: 20px;
            height: 20px;
            opacity: 0.7;
        }

        /* ── CONTENUTO PRINCIPALE ──────────────────────────────────────────────── */
        /* Il contenuto della pagina inizia DOPO la sidebar,
           quindi aggiungiamo un margine sinistro pari alla larghezza della sidebar */
        .main-content {
            margin-left: var(--sidebar-width);
        }

        /* ── RESPONSIVE: su schermi piccoli la sidebar si nasconde ──────────── */
        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);  /* nascosta fuori schermo a sinistra */
            }
            .sidebar.open {
                transform: translateX(0);      /* visibile */
            }
            .main-content {
                margin-left: 0;                /* contenuto a tutta larghezza */
            }
            /* Pulsante hamburger per mobile */
            .mobile-menu-btn {
                display: flex !important;
            }
        }

        /* Pulsante hamburger (nascosto su desktop, visibile su mobile) */
        .mobile-menu-btn {
            display: none;
            position: fixed;
            top: 16px;
            left: 16px;
            z-index: 1100;
            width: 48px;
            height: 48px;
            border-radius: 12px;
            background: var(--sidebar-bg);
            border: none;
            color: white;
            font-size: 1.4rem;
            cursor: pointer;
            align-items: center;
            justify-content: center;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }
    </style>
</head>
<body class="bg-light">

    <!-- ══ PULSANTE HAMBURGER (SOLO MOBILE) ══════════════════════════════════════
         Visibile solo su schermi piccoli (< 768px).
         Cliccando, apre/chiude la sidebar aggiungendo la classe "open". -->
    <button class="mobile-menu-btn" onclick="document.querySelector('.sidebar').classList.toggle('open')">
        ☰
    </button>

    <!-- ══ SIDEBAR (BARRA LATERALE DI NAVIGAZIONE) ═══════════════════════════════
         Struttura tipica di un gestionale professionale:
           1. Header → logo/nome applicazione
           2. Nav    → voci di navigazione con icone
           3. Footer → azione di logout
    ════════════════════════════════════════════════════════════════════════════════ -->
    <aside class="sidebar">

        <!-- ── HEADER: Nome dell'applicazione ─────────────────────────────────── -->
        <div class="sidebar-header">
            <h4>Aequa</h4>
            <small>Gestionale Naturopatia</small>
        </div>

        <!-- ── NAVIGAZIONE: Voci del menu ─────────────────────────────────────── -->
        <nav class="sidebar-nav">

            <!-- Etichetta di sezione -->
            <div class="nav-section-label">Principale</div>

            <!-- Dashboard (pagina corrente → classe "active") -->
            <a href="index.php" class="active">
                <!-- Icona SVG: griglia / dashboard -->
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-4 0a1 1 0 01-1-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 01-1 1" />
                </svg>
                Dashboard
            </a>

            <!-- Etichetta di sezione -->
            <div class="nav-section-label">Gestione</div>

            <!-- Nuovo Paziente -->
            <a href="paziente_nuovo.php">
                <!-- Icona SVG: persona con "+" -->
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                </svg>
                Nuovo Paziente
            </a>

            <!-- Medicinali -->
            <a href="medicinali_gestione.php">
                <!-- Icona SVG: capsula / medicina -->
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 20.5l10-10a4.95 4.95 0 10-7-7l-10 10a4.95 4.95 0 107 7z" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.5 8.5l7 7" />
                </svg>
                Medicinali
            </a>

        </nav>

        <!-- ── FOOTER: Logout ─────────────────────────────────────────────────── -->
        <div class="sidebar-footer">
            <a href="logout.php">
                <!-- Icona SVG: freccia di uscita -->
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                </svg>
                Esci
            </a>
        </div>

    </aside><!-- fine .sidebar -->

    <!-- Contenuto principale: spostato a destra dalla classe main-content (margin-left = sidebar width) -->
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
                    <div class="mt-3">
                        <span class="badge rounded-pill bg-light text-success fw-semibold border">
                            <span class="me-1">●</span> Database attivo
                        </span>
                    </div>
                </div>
            </div>

            <!-- ── Pazienti Registrati (lista completa) ── -->
            <div class="col-md-6">
                <div class="card border-0 shadow-sm rounded-4 overflow-hidden bg-white h-100">
                    <div class="card-header bg-transparent border-bottom py-3 px-4">
                        <h5 class="fw-bold mb-0">Pazienti Registrati</h5>
                    </div>
                    <div style="max-height: 200px; overflow-y: auto;">
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
                                    <span class="text-muted">›</span>
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

            <div class="col-12">
                <div class="card glass border-0 rounded-4 shadow-sm p-2">
                    <div class="input-group input-group-lg">
                        <input type="text" id="search-input" class="form-control border-0 bg-transparent" 
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
                            <!-- Icona SVG: capsula / medicina -->
                            <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" fill="none" viewBox="0 0 24 24" stroke="var(--color-primary)" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 20.5l10-10a4.95 4.95 0 10-7-7l-10 10a4.95 4.95 0 107 7z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M8.5 8.5l7 7" />
                            </svg>
                        </div>
                        <h5 class="fw-bold text-dark mb-2">Archivio Medicinali</h5>
                        <p class="small text-muted mb-0">Gestisci i rimedi e integratori naturali →</p>
                    </div>
                </a>
            </div>
        </div>
    </main>
    </div><!-- fine .main-content -->
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/main.js"></script>
    <script>
        // Attende che tutta la pagina sia caricata prima di eseguire il JS
        document.addEventListener('DOMContentLoaded', function () {
            
            // --- GESTIONE RICERCA PAZIENTI ---
            const searchInput = document.getElementById('search-input');
            if (searchInput) {
                // Ogni volta che l'utente scrive qualcosa nella barra di ricerca...
                searchInput.addEventListener('input', function (e) {
                    // Chiama la funzione di ricerca definita nel tuo file main.js
                    if (typeof searchPatients === 'function') {
                        searchPatients(e.target.value);
                    }
                });
            }

            // --- GESTIONE SALVATAGGIO AUTOMATICO NOTE VELOCI ---
            const notesTextarea = document.getElementById('quick-notes');
            const saveStatus = document.getElementById('save-status');
            let typingTimer;
            const doneTypingInterval = 1000; // Aspetta 1 secondo prima di salvare

            if (notesTextarea && saveStatus) {
                // Quando l'utente inizia a scrivere, cambia il testo e cancella il timer
                notesTextarea.addEventListener('input', function () {
                    clearTimeout(typingTimer);
                    saveStatus.textContent = "Salvataggio in corso...";
                    saveStatus.classList.add('text-warning');
                    saveStatus.classList.remove('text-success', 'text-danger');

                    // Fa partire il timer: se l'utente non scrive nulla per 1s, salva
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
</body>
</html>