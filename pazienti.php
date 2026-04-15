<?php
ob_start();
session_start();

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/Patient.php';

$patientManager = new Patient();
$allPatients = $patientManager->getAllPatients();
$totalPatients = $patientManager->countPatients();

$pageTitle = "Assistiti";
$currentPage = "pazienti";
include 'includes/header.php';
include 'includes/sidebar.php';
?>

    <div class="main-content">
    <main class="container-xl py-5">

        <!-- Header -->
        <header class="mb-5">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h2 class="fw-bold mb-1">Assistiti Registrati</h2>
                    <p class="text-muted mb-0">Elenco completo di tutti gli assistiti. Clicca su un assistito per vedere la scheda.</p>
                </div>
                <a href="paziente_nuovo.php" class="btn btn-gradient rounded-3 px-4 py-2 d-flex align-items-center gap-2 shadow-sm">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                    </svg>
                    Nuovo Assistito
                </a>
            </div>
        </header>

        <!-- Barra di ricerca + conteggio -->
        <div class="row g-3 mb-4 align-items-center">
            <div class="col-md-8">
                <div class="card border-0 rounded-4 shadow-sm p-2 search-card">
                    <div class="input-group align-items-center">
                        <span class="input-group-text bg-transparent border-0 text-primary ps-3 pe-2">
                            <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </span>
                        <input type="text" id="filter-input" class="form-control border-0 bg-transparent shadow-none py-2 fw-medium" 
                               placeholder="Filtra assistiti per nome, email o telefono..." autocomplete="off">
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-0 shadow-sm rounded-4 p-3 bg-white text-center">
                    <div class="d-flex align-items-center justify-content-center gap-2">
                        <span class="display-6 fw-bold text-primary"><?= $totalPatients ?></span>
                        <span class="text-muted small">assistiti registrati</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Lista pazienti -->
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden bg-white">
            <?php if (empty($allPatients)): ?>
                <div class="p-5 text-center text-muted">
                    <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1" class="mb-3 opacity-40 mx-auto d-block">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                    <h5 class="fw-bold mb-2">Nessun assistito registrato</h5>
                    <p class="mb-3">Inizia registrando il tuo primo assistito.</p>
                    <a href="paziente_nuovo.php" class="btn btn-gradient rounded-3 px-4 py-2">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" class="me-1">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                        </svg>
                        Registra Assistito
                    </a>
                </div>
            <?php else: ?>
                <div id="patients-list">
                    <?php foreach ($allPatients as $patient): ?>
                        <a href="paziente_dettaglio.php?id=<?= $patient['id'] ?>" 
                           class="patient-row align-items-center gap-3 px-4 py-3 text-decoration-none border-bottom hover-lift"
                           style="display:flex;"
                           data-name="<?= htmlspecialchars(strtolower($patient['nome_cognome'])) ?>"
                           data-phone="<?= htmlspecialchars(strtolower($patient['telefono'] ?? '')) ?>"
                           data-email="<?= htmlspecialchars(strtolower($patient['email'] ?? '')) ?>">
                            
                            <div class="avatar-circle bg-light text-primary flex-shrink-0" style="width:44px;height:44px;font-size:1rem;">
                                <?= strtoupper(substr($patient['nome_cognome'], 0, 1)) ?>
                            </div>
                            
                            <div class="flex-grow-1 overflow-hidden">
                                <div class="fw-semibold text-dark text-truncate"><?= htmlspecialchars($patient['nome_cognome']) ?></div>
                                <div class="text-muted small d-flex flex-wrap gap-2">
                                    <?php if (!empty($patient['eta'])): ?>
                                        <span><?= $patient['eta'] ?> anni</span>
                                    <?php endif; ?>
                                    <?php if (!empty($patient['telefono'])): ?>
                                        <span>• <?= htmlspecialchars($patient['telefono']) ?></span>
                                    <?php endif; ?>
                                    <?php if (!empty($patient['email'])): ?>
                                        <span>• <?= htmlspecialchars($patient['email']) ?></span>
                                    <?php endif; ?>
                                    <?php if (!empty($patient['professione'])): ?>
                                        <span>• <?= htmlspecialchars($patient['professione']) ?></span>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <div class="text-muted flex-shrink-0 d-flex align-items-center gap-2">
                                <span class="badge bg-light text-primary border rounded-pill px-2 py-1 small">
                                    <?php 
                                        $dataReg = !empty($patient['data_creazione']) ? date('d/m/Y', strtotime($patient['data_creazione'])) : '';
                                        echo $dataReg ? 'Reg. ' . $dataReg : '';
                                    ?>
                                </span>
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" class="opacity-40">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                                </svg>
                            </div>
                        </a>
                    <?php endforeach; ?>
                </div>
                <div id="no-results" class="p-4 text-center text-muted" style="display:none;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" class="mb-2 opacity-50">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                    <div class="small">Nessun assistito trovato per la ricerca.</div>
                </div>
            <?php endif; ?>
        </div>

    </main>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const filterInput = document.getElementById('filter-input');
        const patientRows = document.querySelectorAll('.patient-row');
        const noResults = document.getElementById('no-results');

        if (filterInput && patientRows.length > 0) {
            filterInput.addEventListener('input', function() {
                const query = this.value.trim().toLowerCase();
                let visibleCount = 0;

                patientRows.forEach(row => {
                    const name = row.dataset.name || '';
                    const phone = row.dataset.phone || '';
                    const email = row.dataset.email || '';

                    const match = !query || name.includes(query) || phone.includes(query) || email.includes(query);
                    if (match) {
                        row.style.display = 'flex';
                        visibleCount++;
                    } else {
                        row.style.display = 'none';
                    }
                });

                if (noResults) {
                    noResults.style.display = visibleCount === 0 ? '' : 'none';
                }
            });
        }
    });
    </script>

    <?php include 'includes/footer.php'; ?>
