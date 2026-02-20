<?php
include __DIR__ . '/config/database.php'; //file con le istruzioni necessarie a collegare il database
include __DIR__ . '/includes/patient.php'; // è una classe con tutti i metodi del paziente, che verrà richiamata ogni volta che c'è bisogno
$patientManager = new Patient(); // $patientManager è utilizzato come "gestore" delle funzioni della classe Patient
$recentPatients = $patientManager->getRecentPatients(); // $recentPatients è una variabile che conterrà l'elenco dei pazienti più recenti
$totalPatients = $patientManager->countPatients();

?>

<!DOCTYPE html>
<html lang="it">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TerraNova - Gestionale Naturopatia</title>

<body>

    <!-- Navbar -->
    <nav class="navbar navbar-expand-md navbar-light glass sticky-top px-3 py-2">
        <div class="container-xl">
            <a class="navbar-brand d-flex align-items-center gap-2 fw-bold" href="index.php">
                <div class="avatar-circle"
                    style="background-color:var(--color-primary); color:white; font-size:.85rem;">TN</div>
                TerraNova
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="mainNav">
                <ul class="navbar-nav ms-auto gap-md-3">
                    <li class="nav-item">
                        <a class="nav-link fw-semibold text-primary" href="index.php">Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="paziente_nuovo.php">Nuovo Paziente</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="medicinali_gestione.php">Medicinali</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-danger" href="logout.php">Esci</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="container-xl py-5">

        <!-- Welcome -->
        <header class="mb-5 animate-fade-in">
            <h2 class="fw-bold mb-1">Benvenuta, Naturopata.</h2>
            <p class="text-muted">Ecco una panoramica della tua attività.</p>
        </header>

        <!-- Stats + Actions Row -->
        <div class="row g-4 mb-4">

            <!-- Card Totale Pazienti -->
            <div class="col-md-5 animate-fade-in">
                <div class="card card-stat-primary h-100 rounded-3 shadow-sm p-4 border-0">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <p class="text-uppercase small fw-bold opacity-75 mb-1">Totale Pazienti</p>
                            <div class="d-flex align-items-baseline gap-2">
                                <span class="display-4 fw-bold">
                                    <?= $totalPatients ?>
                                </span>
                                <span class="small opacity-75">assistiti</span>
                            </div>
                        </div>
                        <span class="fs-1">👥</span>
                    </div>
                    <div class="mt-3">
                        <span class="badge rounded-pill bg-light text-success fw-semibold">
                            <span class="me-1">●</span> Database attivo
                        </span>
                    </div>
                </div>
            </div>

            <!-- Nuovo Paziente -->
            <div class="col-md-3 col-sm-6 animate-fade-in delay-100">
                <a href="paziente_nuovo.php"
                    class="card h-100 rounded-3 border-0 shadow-sm p-4 text-decoration-none text-white bg-success hover-lift animate-fade-in"
                    style="background: linear-gradient(135deg, var(--color-primary), #4ade80) !important;">
                    <div class="fs-1 mb-2">➕</div>
                    <h5 class="fw-bold mb-1">Nuovo Paziente</h5>
                    <p class="small opacity-75 mb-0">Registra una nuova scheda</p>
                </a>
            </div>

            <!-- Medicinali -->
            <div class="col-md-4 col-sm-6 animate-fade-in delay-200">
                <a href="medicinali_gestione.php"
                    class="card h-100 rounded-3 border-0 shadow-sm p-4 text-decoration-none glass">
                    <div class="fs-1 mb-2">💊</div>
                    <h5 class="fw-bold text-dark mb-1">Medicinali</h5>
                    <p class="small text-muted mb-0">Gestisci archivio</p>
                </a>
            </div>
        </div>

        <!-- Ricerca -->
        <div class="card glass border-0 rounded-3 shadow-sm p-4 mb-4 animate-fade-in delay-200">
            <div class="input-group input-group-lg">
                <span class="input-group-text bg-white border-end-0">🔍</span>
                <input type="text" id="search-input" class="form-control border-start-0"
                    placeholder="Cerca paziente per nome, email o telefono..." autocomplete="off">
            </div>
        </div>

        <!-- Lista Pazienti Recenti -->
        <div class="card glass border-0 rounded-3 shadow-sm overflow-hidden animate-fade-in delay-300">
            <div
                class="card-header bg-white bg-opacity-50 border-bottom d-flex justify-content-between align-items-center py-3 px-4">
                <h5 class="fw-bold mb-0">Pazienti Recenti</h5>
            </div>
            <div id="patients-list" style="max-height: 420px; overflow-y: auto;">
                <?php if (empty($recentPatients)): ?>
                    <div class="p-5 text-center text-muted">
                        <p class="mb-0">Nessun paziente trovato. Inizia aggiungendone uno!</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($recentPatients as $patient): ?>
                        <div class="patient-row px-4 py-3 d-flex justify-content-between align-items-center border-bottom"
                            onclick="window.location.href='paziente_dettaglio.php?id=<?= $patient['id'] ?>'">
                            <div class="d-flex align-items-center gap-3">
                                <div class="avatar-circle">
                                    <?= strtoupper(substr($patient['nome_cognome'], 0, 1)) ?>
                                </div>
                                <div>
                                    <div class="fw-semibold">
                                        <?= htmlspecialchars($patient['nome_cognome']) ?>
                                    </div>
                                    <div class="text-muted small">
                                        <?= $patient['eta'] ? $patient['eta'] . ' anni' : '' ?>
                                        <?= $patient['telefono'] ? ' · ' . htmlspecialchars($patient['telefono']) : '' ?>
                                    </div>
                                </div>
                            </div>
                            <span class="text-muted">›</span>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
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