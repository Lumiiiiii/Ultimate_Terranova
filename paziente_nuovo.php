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

$pageTitle = "Nuovo Paziente";
$currentPage = "paziente_nuovo";
include 'includes/header.php';
include 'includes/sidebar.php';
?>

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
            <div id="alertContainer"></div>

            <!-- Form -->
            <form id="patientForm">
                <input type="hidden" name="action" value="create_paziente">
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
                                    <div class="col-md-4">
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

                                    <!-- Bottoni integrati nella riga -->
                                    <div class="col-md-4 d-flex align-items-end">
                                        <div class="d-flex gap-2 w-100 pb-1">
                                            <a href="index.php" class="btn btn-light rounded-3 text-muted flex-fill py-2">
                                                Annulla
                                            </a>
                                            <button type="submit" class="btn btn-save rounded-3 flex-fill py-2" id="submitBtn">
                                                Salva
                                            </button>
                                        </div>
                                    </div>

                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </form>

        </main>
    </div>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap-datepicker@1.10.0/dist/js/bootstrap-datepicker.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap-datepicker@1.10.0/dist/locales/bootstrap-datepicker.it.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
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
            const date = e.date;
            const day = String(date.getDate()).padStart(2, '0');
            const month = String(date.getMonth() + 1).padStart(2, '0');
            const year = date.getFullYear();
            document.getElementById('data_nascita').value = year + '-' + month + '-' + day;
        });

        // Loading state e AJAX submit
        document.getElementById('patientForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const btn = document.getElementById('submitBtn');
            const originalText = btn.innerHTML;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status"></span> Salvataggio...';
            btn.classList.add('disabled');
            
            const alertContainer = document.getElementById('alertContainer');
            alertContainer.innerHTML = '';

            const formData = new FormData(this);
            
            try {
                const response = await fetch('ajax_handlers.php', {
                    method: 'POST',
                    body: formData
                });
                
                const data = await response.json();
                
                if (data.success) {
                    Swal.fire({
                        title: 'Paziente Registrato!',
                        text: 'Il nuovo paziente è stato salvato con successo.',
                        icon: 'success',
                        timer: 1500,
                        showConfirmButton: false,
                        customClass: { popup: 'rounded-4 border-0 shadow' }
                    }).then(() => {
                        window.location.href = 'index.php';
                    });
                } else {
                    alertContainer.innerHTML = `
                        <div class="alert alert-danger d-flex align-items-center gap-2 rounded-3 mb-4" role="alert">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            ${data.error || 'Errore durante il salvataggio.'}
                        </div>
                    `;
                    btn.innerHTML = originalText;
                    btn.classList.remove('disabled');
                    window.scrollTo({ top: 0, behavior: 'smooth' });
                }
            } catch (error) {
                console.error('Errore:', error);
                alertContainer.innerHTML = `
                    <div class="alert alert-danger d-flex align-items-center gap-2 rounded-3 mb-4" role="alert">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        Errore di connessione al server.
                    </div>
                `;
                btn.innerHTML = originalText;
                btn.classList.remove('disabled');
                window.scrollTo({ top: 0, behavior: 'smooth' });
            }
        });
    </script>
    <?php include 'includes/footer.php'; ?>
