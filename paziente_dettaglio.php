<?php
ob_start();
session_start();
if(!isset($_SESSION['logged_in'])  || $_SESSION['logged_in'] !== true){
    header('Location: login.php');
    exit;
}
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/Patient.php';
require_once __DIR__ . '/includes/Visit.php';

$id=$_GET['id'] ?? 0;
$patientManager=new Patient();
$visitManager=new Visit();
$patient=$patientManager->getPatient($id);
if(!$patient){
    header('Location: index.php');
    exit;
}
$visits = $visitManager->getVisitHistory($id);

// Verifica se il paziente ha già fatto la prima anamnesi
$haFattoAnamnesi = $patientManager->checkAnamnesi($id);
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($patient['nome_cognome']) ?> - Dettaglio - Aequa</title>
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="assets/img/logo.png">
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Datepicker CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-datepicker@1.10.0/dist/css/bootstrap-datepicker3.min.css">
    
    <style>
<script>
  // 1. Forza immediatamente il tema light per evitare che Bootstrap applichi il nero
  document.documentElement.setAttribute('data-bs-theme', 'light');
</script>

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
</head>
<body>
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

        /* ── CUSTOM STYLES PER DETTAGLIO ────────────────────────────────────── */
        .avatar-circle-large {
            width: 80px; height: 80px; border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            font-size: 2.5rem; font-weight: bold; margin: 0 auto;
        }

        .btn-gradient {
            background: linear-gradient(135deg, var(--color-primary), var(--color-accent)) !important;
            color: white !important;
            border: none;
            transition: all 0.3s ease;
        }
        .btn-gradient:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(46, 204, 113, 0.3);
            color: white !important;
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
            content: ''; position: absolute; top: -50%; left: -50%; width: 200%; height: 200%;
            background: linear-gradient(
                135deg, transparent 0%, transparent 38%, rgba(46, 204, 113, 0.12) 44%,
                rgba(59, 130, 246, 0.18) 50%, rgba(46, 204, 113, 0.12) 56%, transparent 62%, transparent 100%
            );
            animation: saveWave 3s ease-in-out infinite; z-index: -1; pointer-events: none;
        }
        @keyframes saveWave {
            0%   { transform: translate(-60%, -60%); }
            100% { transform: translate(60%, 60%); }
        }
        .btn-save:hover {
            transform: translateY(-2px); box-shadow: 0 6px 20px rgba(46, 180, 160, 0.2);
            border-color: rgba(59, 130, 246, 0.3); color: #1e293b;
        }
    </style>
</head>
<body class="bg-light">

    <!-- Pulsante hamburger mobile -->
    <button class="mobile-menu-btn" onclick="document.querySelector('.sidebar').classList.toggle('open')">☰</button>

    <!-- ══ SIDEBAR ═══════════════════════════════════════════════════════════ -->
    <aside class="sidebar">
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
            <a href="paziente_nuovo.php">
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
                
                <!-- Colonna Sinistra: Profilo Paziente -->
                <div class="col-lg-4">
                    <div class="card border-0 shadow-sm rounded-4 bg-white text-center p-4 h-100 position-relative">
                        
                        <!-- Bottone Elimina Paziente (Cestino in alto a destra) -->
                        <button type="button" class="btn btn-sm btn-outline-danger position-absolute border-0 hover-lift" style="top: 15px; right: 15px;" data-bs-toggle="modal" data-bs-target="#deleteConfirmModal" title="Elimina Paziente">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                            </svg>
                        </button>

                        <div class="avatar-circle-large bg-light text-primary mb-3 mt-2">
                            <?= strtoupper(substr($patient['nome_cognome'], 0, 1)) ?>
                        </div>
                        
                        <h4 class="fw-bold mb-4"><?= htmlspecialchars($patient['nome_cognome']) ?></h4>

                        <?php if (!$haFattoAnamnesi): ?>
                            <a href="visita_anamnesi.php?paziente_id=<?= $id ?>" class="btn btn-gradient btn-lg w-100 mb-4 rounded-3 shadow-sm d-flex align-items-center justify-content-center gap-2">
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                                Fai anamnesi (prima visita)
                            </a>
                        <?php else: ?>
                            <a href="visita_nuova.php?paziente_id=<?= $id ?>" class="btn btn-gradient btn-lg w-100 mb-4 rounded-3 shadow-sm d-flex align-items-center justify-content-center gap-2">
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                                </svg>
                                Nuova visita
                            </a>
                        <?php endif; ?>

                        <!-- Dettagli anagrafici -->
                        <div class="text-start border-top pt-4 small flex-grow-1">
                            <div class="d-flex justify-content-between mb-3">
                                <span class="text-muted d-flex align-items-center gap-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg> Età
                                </span>
                                <span class="fw-medium text-dark"><?= !empty($patient['eta']) ? $patient['eta'] . ' anni' : '-' ?></span>
                            </div>
                            <div class="d-flex justify-content-between mb-3">
                                <span class="text-muted d-flex align-items-center gap-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                                    </svg> Telefono
                                </span>
                                <span class="fw-medium text-dark text-end ms-3"><?= !empty($patient['telefono']) ? htmlspecialchars($patient['telefono']) : '-' ?></span>
                            </div>
                            <div class="d-flex justify-content-between mb-3">
                                <span class="text-muted d-flex align-items-center gap-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                    </svg> Email
                                </span>
                                <span class="fw-medium text-dark text-end ms-3"><?= !empty($patient['email']) ? htmlspecialchars($patient['email']) : '-' ?></span>
                            </div>
                            <div class="d-flex justify-content-between mb-3">
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
                        
                        <!-- Bottone per aprire il Modal Bootstrap -->
                        <button type="button" class="btn btn-light w-100 rounded-3 mt-4 shadow-sm text-muted fw-medium d-flex align-items-center justify-content-center gap-2 hover-lift" data-bs-toggle="modal" data-bs-target="#editModal">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                              <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                            </svg>
                            Modifica Dati
                        </button>
                    </div>
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

    <!-- Edit Modal (Bootstrap) -->
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

    <!-- Script per gestire l'invio del form e l'eliminazione -->
    <script>
        document.getElementById('edit-form').addEventListener('submit', async function (e) {
            e.preventDefault(); 
            
            const btn = document.getElementById('saveEditBtn');
            const originalText = btn.innerHTML;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status"></span> Salvataggio...';
            btn.classList.add('disabled');
            
            const formData = new FormData(this);
            
            try {
                const res = await fetch('ajax_handlers.php', { method: 'POST', body: formData });
                const data = await res.json();
                
                if (data.success) {
                    location.reload(); 
                } else {
                    alert("Errore nel salvataggio: " + (data.message || 'Errore sconosciuto'));
                    btn.innerHTML = originalText;
                    btn.classList.remove('disabled');
                }
            } catch (e) { 
                console.error(e); 
                alert("Errore di connessione.");
                btn.innerHTML = originalText;
                btn.classList.remove('disabled');
            }
        });

        // Script per eliminare il paziente e ricaricare la home
        document.getElementById('confirmDeleteBtnDettaglio')?.addEventListener('click', async function () {
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
                    window.location.href = 'index.php'; // redirect alla dashboard dopo l'eliminazione
                } else {
                    alert('Errore: ' + (data.error || 'Impossibile eliminare il paziente.'));
                    btn.innerHTML = originalText;
                    btn.disabled = false;
                }
            } catch (error) {
                console.error('Errore:', error);
                alert('Errore di connessione.');
                btn.innerHTML = originalText;
                btn.disabled = false;
            }
        });
    </script>

    <!-- Bootstrap JS Bundle -->
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
            startView: 2,          // Parte dalla vista DECENNI
            minViewMode: 0,
            endDate: new Date(),   // Non oltre oggi
            startDate: '01/01/1920',
            autoclose: true,
            todayHighlight: true,
            orientation: 'bottom auto'
        }).on('changeDate', function(e) {
            // Aggiorna il campo nascosto in formato SQL per il salvataggio
            const date = e.date;
            const day = String(date.getDate()).padStart(2, '0');
            const month = String(date.getMonth() + 1).padStart(2, '0');
            const year = date.getFullYear();
            document.getElementById('data_nascita').value = year + '-' + month + '-' + day;
        });
    </script>
</body>
</html>
