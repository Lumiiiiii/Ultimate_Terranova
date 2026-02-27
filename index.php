<?php
include __DIR__ . '/config/database.php'; //file con le istruzioni necessarie a collegare il database
include __DIR__ . '/includes/patient.php'; // è una classe con tutti i metodi del paziente, che verrà richiamata ogni volta che c'è bisogno
$patientManager = new Patient(); // $patientManager è utilizzato come "gestore" delle funzioni della classe Patient
$recentPatients = $patientManager->getRecentPatients(); // $recentPatients è una variabile che conterrà l'elenco dei pazienti più recenti
$totalPatients = $patientManager->countPatients(); // $totalPatients è una variabile che conterrà il numero totale dei pazienti presenti nel database
?>

<!DOCTYPE html>
<html lang="it">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TerraNova - Gestionale Naturopatia</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        :root { --color-primary: #2ecc71; }
        .glass { background: rgba(255, 255, 255, 0.7); backdrop-filter: blur(10px); border: 1px solid rgba(255,255,255,0.1); }
        .avatar-circle { width: 40px; height: 40px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: bold; }
        .hover-lift { transition: transform 0.2s ease; }
        .hover-lift:hover { transform: translateY(-5px) !important; }
        .rounded-4 { border-radius: 1rem !important; }
    </style>
</head>
<body class="bg-light">

    <nav class="navbar navbar-expand-md navbar-light glass sticky-top px-3 py-2">
        <div class="container-xl">
            <a class="navbar-brand d-flex align-items-center gap-2 fw-bold" href="index.php">
                <div class="avatar-circle" style="background-color:var(--color-primary); color:white; font-size:.85rem;">TN</div>
                TerraNova
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="mainNav">
                <ul class="navbar-nav ms-auto gap-md-3">
                    <li class="nav-item"><a class="nav-link fw-semibold text-primary" href="index.php">Dashboard</a></li>
                    <li class="nav-item"><a class="nav-link" href="paziente_nuovo.php">Nuovo Paziente</a></li>
                    <li class="nav-item"><a class="nav-link" href="medicinali_gestione.php">Medicinali</a></li>
                    <li class="nav-item"><a class="nav-link text-danger" href="logout.php">Esci</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <main class="container-xl py-5">
        <header class="mb-5">
            <h2 class="fw-bold mb-1">Benvenuta, Naturopata.</h2>
            <p class="text-muted">Ecco una panoramica della tua attività.</p>
        </header>

        <div class="row g-4">
            
            <div class="col-md-7">
                <div class="card h-100 border-0 shadow-sm p-4 rounded-4 bg-white">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <p class="text-uppercase small fw-bold text-muted mb-1">Totale Pazienti</p>
                            <div class="d-flex align-items-baseline gap-2">
                                <span class="display-4 fw-bold"><?= $totalPatients ?></span>
                                <span class="small text-muted">assistiti</span>
                            </div>
                        </div>
                        <span class="fs-1">👥</span>
                    </div>
                    <div class="mt-3">
                        <span class="badge rounded-pill bg-light text-success fw-semibold border">
                            <span class="me-1">●</span> Database attivo
                        </span>
                    </div>
                </div>
            </div>

            <div class="col-md-5">
                <a href="paziente_nuovo.php" class="card h-100 border-0 shadow-sm p-4 text-decoration-none text-white hover-lift rounded-4" 
                   style="background: linear-gradient(135deg, var(--color-primary), #4ade80);">
                    <div class="fs-1 mb-2">➕</div>
                    <h5 class="fw-bold mb-1">Nuovo Paziente</h5>
                    <p class="small opacity-75 mb-0">Registra una nuova scheda</p>
                </a>
            </div>

            <div class="col-12">
                <div class="card glass border-0 rounded-4 shadow-sm p-2">
                    <div class="input-group input-group-lg">
                        <span class="input-group-text bg-transparent border-0">🔍</span>
                        <input type="text" id="search-input" class="form-control border-0 bg-transparent" 
                               placeholder="Cerca paziente per nome, email o telefono..." autocomplete="off">
                    </div>
                </div>
            </div>

            <div class="col-md-8">
                <div class="card border-0 shadow-sm rounded-4 overflow-hidden bg-white h-100">
                    <div class="card-header bg-transparent border-bottom py-3 px-4">
                        <h5 class="fw-bold mb-0">Pazienti Recenti</h5>
                    </div>
                    <div id="patients-list" style="max-height: 420px; overflow-y: auto;">
                        <?php if (empty($recentPatients)): ?>
                            <div class="p-5 text-center text-muted">Nessun paziente trovato.</div>
                        <?php else: ?>
                            <?php foreach ($recentPatients as $patient): ?>
                                <div class="px-4 py-3 d-flex justify-content-between align-items-center border-bottom" 
                                     style="cursor:pointer" onclick="window.location.href='paziente_dettaglio.php?id=<?= $patient['id'] ?>'">
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="avatar-circle bg-light text-primary">
                                            <?= strtoupper(substr($patient['nome_cognome'], 0, 1)) ?>
                                        </div>
                                        <div>
                                            <div class="fw-semibold text-dark"><?= htmlspecialchars($patient['nome_cognome']) ?></div>
                                            <div class="text-muted small"><?= $patient['eta'] ?> anni • <?= htmlspecialchars($patient['telefono']) ?></div>
                                        </div>
                                    </div>
                                    <span class="text-muted">›</span>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <a href="medicinali_gestione.php" class="card h-100 border-0 shadow-sm p-4 text-decoration-none glass hover-lift rounded-4">
                    <div class="fs-1 mb-2">💊</div>
                    <h5 class="fw-bold text-dark mb-1">Medicinali</h5>
                    <p class="small text-muted mb-0">Gestisci l'archivio dei rimedi</p>
                </a>
            </div>
        </div>
    </main>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/main.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const searchInput = document.getElementById('search-input');
            searchInput.addEventListener('input', function (e) {
                searchPatients(e.target.value);
            });
        });
    </script>
</body>
</html>