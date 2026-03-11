<?php
ob_start();
session_start();

// Se l'utente non è loggato, reindirizza alla pagina di login
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

// Include dipendenze
include __DIR__ . '/config/database.php';
include __DIR__ . '/includes/patient.php';

$patientManager = new Patient();
$successo = "";
$errore = "";

// Gestione invio form
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $data = [
        'nome_cognome' => trim($_POST['nome_cognome'] ?? ''),
        'data_nascita' => $_POST['data_nascita'] ?? null,
        'telefono'     => trim($_POST['telefono'] ?? ''),
        'indirizzo'    => trim($_POST['indirizzo'] ?? ''),
        'email'        => trim($_POST['email'] ?? ''),
        'professione'  => trim($_POST['professione'] ?? '')
    ];

    // Validazione: nome e data di nascita sono obbligatori
    if (empty($data['nome_cognome'])) {
        $errore = "Il campo Nome e Cognome è obbligatorio.";
    } elseif (empty($data['data_nascita'])) {
        $errore = "Il campo Data di Nascita è obbligatorio.";
    } else {
        // Controllo duplicati
        if ($patientManager->isDuplicate($data['nome_cognome'], $data['data_nascita'])) {
            $errore = "Esiste già un paziente con questi dati (Nome, Cognome e Data di nascita).";
        } else {
            $newId = $patientManager->createPatient($data);
            if ($newId) {
                // Redirect alla homepage
                header("Location: index.php");
                exit;
            } else {
                $errore = "Errore durante il salvataggio. Riprova.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nuovo Paziente - Aequa</title>
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="assets/img/logo.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Datepicker -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-datepicker@1.10.0/dist/css/bootstrap-datepicker3.min.css">
    <style>
        /* ── VARIABILI CSS GLOBALI ─────────────────────────────────────────────── */
        :root {
            --color-primary: #2ecc71;
            --color-primary-dark: #27ae60;
            --color-accent: #3b82f6;
            --sidebar-width: 260px;
            --sidebar-bg: #1a1a2e;
            --sidebar-text: #a0aec0;
            --sidebar-active: #ffffff;
        }

        /* ── CLASSI DI UTILITÀ E RESET ─────────────────────────────────────── */
        html, body { background-color: #f8f9fa; }
        .glass { background: rgba(255, 255, 255, 0.7); backdrop-filter: blur(10px); border: 1px solid rgba(255,255,255,0.1); }
        .hover-lift { transition: transform 0.2s ease; }
        .hover-lift:hover { transform: translateY(-5px) !important; }
        .rounded-4 { border-radius: 1rem !important; }

        /* ── SIDEBAR ────────────────────────────────────────────────────────── */
        .sidebar {
            position: fixed; top: 0; left: 0; bottom: 0;
            width: var(--sidebar-width);
            background: var(--sidebar-bg);
            color: var(--sidebar-text);
            display: flex; flex-direction: column;
            z-index: 1000;
            box-shadow: 4px 0 15px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease;
        }
        .sidebar-header { padding: 28px 24px 20px; border-bottom: 1px solid rgba(255,255,255,0.08); }
        .sidebar-header h4 { color: white; font-weight: 700; font-size: 1.15rem; margin: 0; }
        .sidebar-header small { color: var(--sidebar-text); font-size: 0.75rem; opacity: 0.7; }
        .sidebar-nav { flex: 1; padding: 16px 12px; overflow-y: auto; }
        .nav-section-label {
            font-size: 0.65rem; text-transform: uppercase; letter-spacing: 1.5px;
            color: rgba(255,255,255,0.25); padding: 12px 16px 6px; font-weight: 600;
        }
        .sidebar-nav a {
            display: flex; align-items: center; gap: 12px;
            padding: 11px 16px; color: var(--sidebar-text); text-decoration: none;
            border-radius: 10px; font-size: 0.92rem; font-weight: 500;
            transition: all 0.2s ease; margin-bottom: 2px;
        }
        .sidebar-nav a:hover { background: rgba(255,255,255,0.08); color: white; }
        .sidebar-nav a.active { background: linear-gradient(135deg, rgba(46, 204, 113, 0.15), rgba(59, 130, 246, 0.15)); color: white; }
        .sidebar-nav a svg { width: 20px; height: 20px; flex-shrink: 0; opacity: 0.6; }
        .sidebar-nav a:hover svg, .sidebar-nav a.active svg { opacity: 1; }
        .sidebar-footer { padding: 16px 12px; border-top: 1px solid rgba(255,255,255,0.08); }
        .sidebar-footer a {
            display: flex; align-items: center; gap: 12px;
            padding: 11px 16px; color: #e57373; text-decoration: none;
            border-radius: 10px; font-size: 0.92rem; font-weight: 500; transition: all 0.2s ease;
        }
        .sidebar-footer a:hover { background: rgba(229, 115, 115, 0.1); color: #ef5350; }
        .sidebar-footer a svg { width: 20px; height: 20px; opacity: 0.7; }
        .main-content { margin-left: var(--sidebar-width); }

        /* ── RESPONSIVE ────────────────────────────────────────────────────── */
        @media (max-width: 768px) {
            .sidebar { transform: translateX(-100%); }
            .sidebar.open { transform: translateX(0); }
            .main-content { margin-left: 0; }
            .mobile-menu-btn { display: flex !important; }
        }
        .mobile-menu-btn {
            display: none; position: fixed; top: 16px; left: 16px; z-index: 1100;
            width: 48px; height: 48px; border-radius: 12px; background: var(--sidebar-bg);
            border: none; color: white; font-size: 1.4rem; cursor: pointer;
            align-items: center; justify-content: center;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }

        /* ── FORM CUSTOM STYLES ────────────────────────────────────────────── */
        .form-control:focus {
            border-color: var(--color-accent);
            box-shadow: 0 0 0 0.2rem rgba(59, 130, 246, 0.15);
        }
        .form-floating > .form-control:focus ~ label,
        .form-floating > .form-control:not(:placeholder-shown) ~ label {
            color: var(--color-accent);
        }
        .btn-save {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            color: #1e293b;
            font-weight: 600;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
            z-index: 1;
        }
        .btn-save::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: linear-gradient(
                135deg,
                transparent 0%,
                transparent 38%,
                rgba(46, 204, 113, 0.12) 44%,
                rgba(59, 130, 246, 0.18) 50%,
                rgba(46, 204, 113, 0.12) 56%,
                transparent 62%,
                transparent 100%
            );
            animation: saveWave 3s ease-in-out infinite;
            z-index: -1;
            pointer-events: none;
        }
        @keyframes saveWave {
            0%   { transform: translate(-60%, -60%); }
            100% { transform: translate(60%, 60%); }
        }
        .btn-save:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(46, 180, 160, 0.2);
            border-color: rgba(59, 130, 246, 0.3);
            color: #1e293b;
        }
        .section-icon {
            width: 40px; height: 40px; border-radius: 12px;
            display: flex; align-items: center; justify-content: center;
            flex-shrink: 0;
        }
    </style>
</head>
<body class="bg-light">

    <!-- Pulsante hamburger mobile -->
    <button class="mobile-menu-btn" onclick="document.querySelector('.sidebar').classList.toggle('open')">☰</button>

    <!-- ══ SIDEBAR ═══════════════════════════════════════════════════════════ -->
    <aside class="sidebar">
        <!-- ── HEADER: Nome dell'applicazione ─────────────────────────────────── -->
        <div class="sidebar-header d-flex align-items-center gap-2" style="padding-left: 20px;">
            <img src="assets/img/logo.png" alt="Aequa Logo" style="width: 46px; height: 46px; object-fit: contain; flex-shrink: 0;">
            <h3 class="mb-0 fw-bold pb-1" style="background: linear-gradient(135deg, var(--color-primary), var(--color-accent)); -webkit-background-clip: text; -webkit-text-fill-color: transparent; font-size: 1.8rem; letter-spacing: 0.5px;">Aequa</h3>
        </div>
        <nav class="sidebar-nav">
            <div class="nav-section-label">Principale</div>
            <a href="index.php">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-4 0a1 1 0 01-1-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 01-1 1" />
                </svg>
                Dashboard
            </a>
            <div class="nav-section-label">Gestione</div>
            <a href="paziente_nuovo.php" class="active">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                </svg>
                Nuovo Paziente
            </a>
            <a href="medicinali_gestione.php">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m20.25 7.5-.625 10.632a2.25 2.25 0 0 1-2.247 2.118H6.622a2.25 2.25 0 0 1-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125Z" />
                </svg>
                Archivio
            </a>
        </nav>
        <div class="sidebar-footer">
            <a href="logout.php">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                </svg>
                Esci
            </a>
        </div>
    </aside>

    <!-- ══ CONTENUTO PRINCIPALE ══════════════════════════════════════════════ -->
    <div class="main-content">
        <main class="container-xl py-5">

            <!-- Header della pagina -->
            <header class="mb-5">
                <div class="d-flex align-items-center gap-3 mb-1">
                    <a href="index.php" class="text-muted text-decoration-none d-flex align-items-center gap-1">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
                        </svg>
                        Dashboard
                    </a>
                    <span class="text-muted">/</span>
                    <span class="text-dark fw-semibold">Nuovo Paziente</span>
                </div>
                <h2 class="fw-bold mb-1">Registra nuovo paziente</h2>
                <p class="text-muted">Compila i campi per creare una nuova scheda paziente.</p>
            </header>

            <!-- Messaggi di errore -->
            <?php if (!empty($errore)): ?>
                <div class="alert alert-danger d-flex align-items-center gap-2 rounded-3 mb-4" role="alert">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <?php echo htmlspecialchars($errore); ?>
                </div>
            <?php endif; ?>

            <!-- Form -->
            <form method="POST" action="paziente_nuovo.php" id="patientForm">
                <div class="row g-4">

                    <!-- ── SEZIONE UNICA: Dati Paziente ── -->
                    <div class="col-12">
                        <div class="card border-0 shadow-sm rounded-4 bg-white">
                            <div class="card-header bg-transparent border-bottom py-3 px-4">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="section-icon bg-success bg-opacity-10">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="var(--color-primary)" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                        </svg>
                                    </div>
                                    <div>
                                        <h5 class="fw-bold mb-0">Dati Paziente</h5>
                                        <small class="text-muted">Informazioni anagrafiche e contatti</small>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body p-4">
                                <div class="row g-3">

                                    <!-- Nome e Cognome (obbligatorio) -->
                                    <div class="col-md-6">
                                        <div class="form-floating">
                                            <input type="text" class="form-control rounded-3" id="nome_cognome" name="nome_cognome" 
                                                   placeholder="Nome e Cognome" required
                                                   value="<?= htmlspecialchars($_POST['nome_cognome'] ?? '') ?>">
                                            <label for="nome_cognome">Nome e Cognome <span class="text-danger">*</span></label>
                                        </div>
                                    </div>

                                    <!-- Data di nascita (Bootstrap Datepicker) -->
                                    <div class="col-md-3">
                                        <div class="form-floating">
                                            <input type="text" class="form-control rounded-3" id="data_visuale" 
                                                   placeholder="Seleziona data" required autocomplete="off" readonly
                                                   value="<?php
                                                       $val = $_POST['data_nascita'] ?? '';
                                                       if ($val && strpos($val, '-') !== false) {
                                                           $parts = explode('-', $val);
                                                           echo $parts[2] . '/' . $parts[1] . '/' . $parts[0];
                                                       }
                                                   ?>">
                                            <label for="data_visuale">Data di Nascita <span class="text-danger">*</span></label>
                                        </div>
                                        <input type="hidden" name="data_nascita" id="data_nascita" 
                                               value="<?= htmlspecialchars($_POST['data_nascita'] ?? '') ?>">
                                    </div>

                                    <!-- Professione -->
                                    <div class="col-md-3">
                                        <div class="form-floating">
                                            <input type="text" class="form-control rounded-3" id="professione" name="professione" 
                                                   placeholder="Professione"
                                                   value="<?= htmlspecialchars($_POST['professione'] ?? '') ?>">
                                            <label for="professione">Professione</label>
                                        </div>
                                    </div>

                                    <!-- Separatore visivo -->
                                    <div class="col-12"><hr class="my-1 text-muted opacity-25"></div>

                                    <!-- Telefono -->
                                    <div class="col-md-4">
                                        <div class="form-floating">
                                            <input type="tel" class="form-control rounded-3" id="telefono" name="telefono" 
                                                   placeholder="Telefono"
                                                   value="<?= htmlspecialchars($_POST['telefono'] ?? '') ?>">
                                            <label for="telefono">Telefono</label>
                                        </div>
                                    </div>

                                    <!-- Email -->
                                    <div class="col-md-4">
                                        <div class="form-floating">
                                            <input type="email" class="form-control rounded-3" id="email" name="email" 
                                                   placeholder="Email"
                                                   value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
                                            <label for="email">Email</label>
                                        </div>
                                    </div>

                                    <!-- Indirizzo -->
                                    <div class="col-md-4">
                                        <div class="form-floating">
                                            <input type="text" class="form-control rounded-3" id="indirizzo" name="indirizzo" 
                                                   placeholder="Indirizzo"
                                                   value="<?= htmlspecialchars($_POST['indirizzo'] ?? '') ?>">
                                            <label for="indirizzo">Indirizzo</label>
                                        </div>
                                    </div>

                                </div>
                            </div>

                            <!-- Bottoni in fondo alla card -->
                            <div class="card-footer bg-transparent border-top py-3 px-4">
                                <div class="d-flex justify-content-end gap-2">
                                    <a href="index.php" class="btn btn-light btn-lg rounded-3 text-muted px-4">
                                        Annulla
                                    </a>
                                    <button type="submit" class="btn btn-save btn-lg rounded-3 px-4" id="submitBtn">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" class="me-2" style="vertical-align: -4px;">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                        </svg>
                                        Salva Paziente
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </form>

        </main>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Bootstrap Datepicker JS + Italiano -->
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap-datepicker@1.10.0/dist/js/bootstrap-datepicker.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap-datepicker@1.10.0/dist/locales/bootstrap-datepicker.it.min.js"></script>
    <script>
        // Inizializza Bootstrap Datepicker
        $('#data_visuale').datepicker({
            language: 'it',
            format: 'dd/mm/yyyy',
            startView: 2,          // Parte dalla vista DECENNI (2=anni, 1=mesi, 0=giorni)
            minViewMode: 0,        // Permette di scendere fino ai giorni
            endDate: new Date(),   // Non oltre oggi
            startDate: '01/01/1920',
            autoclose: true,       // Si chiude automaticamente alla selezione
            todayHighlight: true,
            orientation: 'bottom auto'
        }).on('changeDate', function(e) {
            // Aggiorna il campo nascosto in formato SQL
            const date = e.date;
            const day = String(date.getDate()).padStart(2, '0');
            const month = String(date.getMonth() + 1).padStart(2, '0');
            const year = date.getFullYear();
            document.getElementById('data_nascita').value = year + '-' + month + '-' + day;
        });

        // Loading state al submit
        document.getElementById('patientForm').addEventListener('submit', function() {
            const btn = document.getElementById('submitBtn');
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status"></span> Salvataggio...';
            btn.classList.add('disabled');
        });
    </script>

</body>
</html>
