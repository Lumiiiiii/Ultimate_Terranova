<?php
session_start();

// Controllo accesso
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/Patient.php';

$paziente_id = $_GET['paziente_id'] ?? null;
if (!$paziente_id) {
    header('Location: index.php');
    exit;
}

$patientManager = new Patient();
$patient = $patientManager->getPatient($paziente_id);

if (!$patient) {
    header('Location: index.php');
    exit;
}

$pageTitle = "Anamnesi";
$currentPage = "pazienti";
include 'includes/header.php';
include 'includes/sidebar.php';
?>

    <!-- MAIN CONTENT -->
    <div class="main-content">
        <!-- Header sottile -->
        <nav class="navbar navbar-light bg-white shadow-sm px-4 py-3 sticky-top">
            <div class="container-fluid d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center gap-3">
                    <a href="paziente_dettaglio.php?id=<?= $paziente_id ?>" class="btn btn-light border rounded-circle p-2 hover-lift d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                          <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
                        </svg>
                    </a>
                    <div>
                        <h5 class="mb-0 fw-bold">Prima Visita (Anamnesi)</h5>
                        <small class="text-muted">Assistito: <?= htmlspecialchars($patient['nome_cognome']) ?></small>
                    </div>
                </div>
            </div>
        </nav>

        <main class="container py-5" style="max-width: 900px;">
            
            <div class="text-center mb-5">
                <div class="d-inline-flex bg-primary bg-opacity-10 text-primary rounded-circle p-3 mb-3">
                    <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                </div>
                <h2 class="fw-bold">Raccolta Dati Anamnestici</h2>
                <p class="text-muted">Compila la scheda storica dell'assistito. Questi dati verranno richiesti una sola volta.</p>
            </div>

            <form id="anamnesi-form">
                <input type="hidden" name="action" value="create_anamnesi">
                <input type="hidden" name="paziente_id" value="<?= $paziente_id ?>">

                <!-- SEZIONE 0: Dati Biometrici -->
                <h6 class="text-primary border-bottom pb-2 mt-4 mb-3 d-flex align-items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                    Dati Biometrici Base
                </h6>
                <div class="row g-4 mb-4">
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold text-muted">Altezza (cm)</label>
                        <input type="number" name="altezza" class="form-control bg-light py-2" placeholder="Es. 175" min="100" max="250">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold text-muted">Peso (kg)</label>
                        <input type="number" step="0.1" name="peso" class="form-control bg-light py-2" placeholder="Es. 70.5" min="20" max="300">
                    </div>
                </div>

                <!-- SEZIONE 1: Stile di vita -->
                <h6 class="text-primary border-bottom pb-2 mt-4 mb-3 d-flex align-items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-4 0a1 1 0 01-1-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 01-1 1" />
                    </svg>
                    Stile di Vita e Abitudini
                </h6>
                <div class="row g-4 mb-4">
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold text-muted">Consumo di Alcol</label>
                        <input type="text" name="alcol" class="form-control bg-light py-2" placeholder="Es. Solo nel weekend">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold text-muted">Abitudine al Fumo</label>
                        <input type="text" name="fumo" class="form-control bg-light py-2" placeholder="Es. Ha smesso da 5 anni">
                    </div>
                </div>

                <!-- SEZIONE 2: Storia Clinica -->
                <h6 class="text-primary border-bottom pb-2 mt-5 mb-3 d-flex align-items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" />
                    </svg>
                    Storia Clinica Relevante
                </h6>
                <div class="row g-4 mb-4">
                    <div class="col-12">
                        <label class="form-label small fw-semibold text-muted">Allergie e Intolleranze</label>
                        <textarea name="allergie_intolleranze" class="form-control bg-light" rows="2" placeholder="Es. Lattosio, Glutine, Polline, Nichel..."></textarea>
                    </div>
                    
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold text-muted">Patologie Pregresse / Croniche</label>
                        <textarea name="patologie_pregresse" class="form-control bg-light" rows="3" placeholder="Es. Ipertensione, Diabete, Ipotiroidismo..."></textarea>
                    </div>
                    
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold text-muted">Interventi Chirurgici</label>
                        <textarea name="interventi_chirurgici" class="form-control bg-light" rows="3" placeholder="Es. Appendicectomia (2015), Intervento al ginocchio..."></textarea>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label small fw-semibold text-muted">Traumi o Fratture Ossee</label>
                        <textarea name="traumi_o_fratture" class="form-control bg-light" rows="3" placeholder="Es. Colpo di frusta, frattura polso..."></textarea>
                    </div>
                    
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold text-muted">Esami Clinici Recenti</label>
                        <textarea name="esami_clinici_recenti" class="form-control bg-light" rows="3" placeholder="Es. Analisi del sangue nella norma (Maggio 2023)..."></textarea>
                    </div>
                </div>

                <!-- SEZIONE 3: Terapie Attuali -->
                <h6 class="text-primary border-bottom pb-2 mt-5 mb-3 d-flex align-items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.5 20.5l10-10a4.95 4.95 0 10-7-7l-10 10a4.95 4.95 0 107 7z" />
                      <path stroke-linecap="round" stroke-linejoin="round" d="M8.5 8.5l7 7" />
                    </svg>
                    Farmaci e Integrazioni Attuali
                </h6>
                <div class="row g-4 mb-4">
                    <div class="col-12">
                        <label class="form-label small fw-semibold text-muted">Farmaci Assunti / Al Bisogno (Incluse Terapie Croniche)</label>
                        <textarea name="farmaci_assunti" class="form-control bg-light" rows="3" placeholder="Es. Eutirox 50mcg quotidiano, Antinfiammatori per emicrania..."></textarea>
                    </div>
                </div>

                <!-- SEZIONE 4: Note Aggiuntive -->
                <h6 class="text-primary border-bottom pb-2 mt-5 mb-3 d-flex align-items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                    Note Aggiuntive
                </h6>
                <div class="row g-4 mb-4">
                    <div class="col-12">
                        <label class="form-label small fw-semibold text-muted">Altre informazioni anamnestiche</label>
                        <textarea name="note_aggiuntive" class="form-control bg-light" rows="4" placeholder="Eventuali annotazioni, osservazioni sull'assistito o dettagli liberi..."></textarea>
                    </div>
                </div>

                <!-- Submit Area -->
                <div class="d-flex justify-content-end gap-3 mt-5">
                    <a href="paziente_dettaglio.php?id=<?= $paziente_id ?>" class="btn btn-light px-4 py-3 rounded-3 fw-semibold text-muted hover-lift">
                        Annulla
                    </a>
                    <button type="submit" class="btn btn-gradient px-5 py-3 rounded-3 shadow-sm hover-lift fw-bold fs-5">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" class="me-2">
                          <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                        </svg>
                        Salva Anamnesi
                    </button>
                </div>

            </form>
        </main>
    </div>

    <!-- Script per AJAX -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
    document.getElementById('anamnesi-form').addEventListener('submit', async function(e) {
        e.preventDefault();
        
        // mostra loading
        const overlay = document.getElementById('loadingOverlay');
        if (overlay) overlay.style.display = 'flex';
        
        const formData = new FormData(this);
        
        try {
            const response = await fetch('ajax_handlers.php', {
                method: 'POST',
                body: formData
            });
            
            const result = await response.json();
            
            if (result.success) {
                if (overlay) overlay.style.display = 'none';
                Swal.fire({
                    title: 'Salvato!',
                    text: 'L\'anamnesi è stata salvata con successo.',
                    icon: 'success',
                    timer: 1500,
                    showConfirmButton: false,
                    customClass: { popup: 'rounded-4 border-0 shadow' }
                }).then(() => {
                    window.location.href = 'paziente_dettaglio.php?id=<?= $paziente_id ?>';
                });
            } else {
                if (overlay) overlay.style.display = 'none';
                Swal.fire({
                    title: 'Errore',
                    text: result.error || 'Errore nel salvataggio',
                    icon: 'error',
                    customClass: { popup: 'rounded-4 border-0 shadow' }
                });
            }
        } catch (error) {
            console.error(error);
            if (overlay) overlay.style.display = 'none';
            Swal.fire({
                title: 'Errore di Rete',
                text: 'Si è verificato un errore durante la comunicazione con il server.',
                icon: 'error',
                customClass: { popup: 'rounded-4 border-0 shadow' }
            });
        }
    });
    </script>

    <!-- Overlay di caricamento -->
    <div class="saving-overlay" id="loadingOverlay">
        <div class="spinner-border text-primary" role="status" style="width: 3rem; height: 3rem;"></div>
        <h4 class="mt-3 text-dark fw-bold">Salvataggio Anamnesi in corso...</h4>
    </div>

    <?php include 'includes/footer.php'; ?>
