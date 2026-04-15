<?php
// catalogo_gestione.php — Gestione archivio Integratori e Alimenti
session_start();

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

require_once __DIR__ . '/config/database.php';
$db = getDB();

// Recupero dati
$stmtMed = $db->query("SELECT * FROM medicinali ORDER BY nome ASC");
$medicinali = $stmtMed->fetchAll(PDO::FETCH_ASSOC);

$stmtAlim = $db->query("SELECT * FROM lista_alimenti ORDER BY nome ASC");
$alimenti = $stmtAlim->fetchAll(PDO::FETCH_ASSOC);

$pageTitle = "Archivio Clinico";
$currentPage = "catalogo"; // Per l'evidenziazione nel menu se implementata
include 'includes/header.php';
include 'includes/sidebar.php';
?>

<div class="main-content">
    <!-- Barra Navigazione -->
    <nav class="navbar navbar-light bg-white shadow-sm px-4 py-3 sticky-top">
        <div class="container-fluid d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center gap-3">
                <a href="index.php" class="btn btn-light border rounded-circle p-2 hover-lift d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
                    </svg>
                </a>
                <div>
                    <h5 class="mb-0 fw-bold">Archivio Clinico Centrale</h5>
                    <small class="text-muted">Gestisci i rimedi e il database alimentare</small>
                </div>
            </div>
        </div>
    </nav>

    <main class="container py-5" style="max-width: 1000px;">

        <!-- Intestazione -->
        <div class="text-center mb-5">
            <div class="d-inline-flex bg-primary bg-opacity-10 text-primary rounded-circle p-3 mb-3">
                <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m20.25 7.5-.625 10.632a2.25 2.25 0 0 1-2.247 2.118H6.622a2.25 2.25 0 0 1-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125Z" />
                </svg>
            </div>
            <h2 class="fw-bold">Gestione Catalogo</h2>
            <p class="text-muted">Aggiungi o sospendi i prodotti prescritti nelle visite.</p>
        </div>

        <!-- TABS NAV -->
        <ul class="nav nav-pills mb-4 justify-content-center gap-2" id="catalogoTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active px-4 rounded-pill fw-medium" id="integratori-tab" data-bs-toggle="tab" data-bs-target="#integratori" type="button" role="tab">Integratori e Rimedi</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link px-4 rounded-pill fw-medium" id="alimenti-tab" data-bs-toggle="tab" data-bs-target="#alimenti" type="button" role="tab">Gruppi Alimentari</button>
            </li>
        </ul>

        <!-- TABS CONTENT -->
        <div class="tab-content" id="catalogoTabsContent">
            
            <!-- TAB 1: INTEGRATORI -->
            <div class="tab-pane fade show active" id="integratori" role="tabpanel">
                <div class="card border-0 shadow-sm rounded-4 bg-white p-4 mb-4">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h5 class="fw-bold mb-0">Lista Integratori</h5>
                        <button class="btn btn-primary rounded-3 text-white fw-medium shadow-sm px-4 hover-lift" data-bs-toggle="modal" data-bs-target="#modalNuovoRimedio">
                            + Nuovo Rimedio
                        </button>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light text-muted small">
                                <tr>
                                    <th>Nome Prodotto</th>
                                    <th>Tipologia</th>
                                    <th>Dosaggio Consigliato</th>

                                </tr>
                            </thead>
                            <tbody>
                                <?php if(empty($medicinali)): ?>
                                    <tr><td colspan="3" class="text-center py-4 text-muted">Nessun rimedio registrato nel catalogo. Aggiungi il primo!</td></tr>
                                <?php endif; ?>
                                <?php foreach($medicinali as $med): ?>
                                    <tr>
                                        <td class="fw-bold text-dark"><?= htmlspecialchars($med['nome']) ?></td>
                                        <td><span class="badge bg-light text-secondary border"><?= htmlspecialchars($med['tipologia'] ?? 'Generico') ?></span></td>
                                        <td class="text-muted small"><?= htmlspecialchars($med['dosaggio_standard'] ?? '-') ?></td>

                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- TAB 2: ALIMENTI -->
            <div class="tab-pane fade" id="alimenti" role="tabpanel">
                <div class="row g-4 d-flex align-items-stretch">
                    <div class="col-md-5">
                        <div class="card border-0 shadow-sm rounded-4 bg-white p-4 h-100">
                            <h5 class="fw-bold mb-3 border-bottom pb-2">Nuovo Alimento</h5>
                            <p class="text-muted small mb-4">Aggiungi voci specifiche o ampi gruppi alimentari per escluderli durante la stesura del piano nutrizionale.</p>
                            <form id="form-nuovo-alimento">
                                <input type="hidden" name="action" value="create_alimento">
                                <div class="mb-3">
                                    <label class="form-label small fw-semibold text-muted">Nome Alimento o Categoria</label>
                                    <input type="text" name="nome" class="form-control bg-light" placeholder="Es. Latticini, Zucchero raffinato" required>
                                </div>
                                <button type="submit" class="btn btn-primary w-100 rounded-3 text-white fw-bold shadow-sm mt-2 py-2 hover-lift">
                                    Aggiungi al Catalogo
                                </button>
                            </form>
                        </div>
                    </div>
                    <div class="col-md-7">
                        <div class="card border-0 shadow-sm rounded-4 bg-white p-4 h-100">
                            <h5 class="fw-bold mb-4">Archivio Esclusioni Nutrizionali</h5>
                            <div class="d-flex flex-wrap gap-2">
                                <?php if(empty($alimenti)): ?>
                                    <p class="text-muted small w-100">Nessun alimento registrato.</p>
                                <?php endif; ?>
                                <?php foreach($alimenti as $alim): ?>
                                    <span class="badge bg-light border text-dark fs-6 py-2 px-3 d-flex align-items-center gap-2">
                                        <?= htmlspecialchars($alim['nome']) ?>
                                        <button class="btn btn-sm p-0 text-danger ms-2 hover-lift" onclick="eliminaAlimento(<?= $alim['id'] ?>)" title="Elimina definitivamente">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                                        </button>
                                    </span>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>

    </main>
</div>

<!-- Modal Nuovo Integratore -->
<div class="modal fade" id="modalNuovoRimedio" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-bottom-0 pb-0 px-4 pt-4">
                <h5 class="modal-title fw-bold">Aggiungi Nuovo Rimedio</h5>
                <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <form id="form-nuovo-rimedio">
                    <input type="hidden" name="action" value="create_medicinale">
                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-muted">Nome Prodotto *</label>
                        <input type="text" name="nome" class="form-control bg-light" placeholder="Es. Vitamina C Liposomiale" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-muted">Tipologia</label>
                        <select name="tipologia" class="form-select bg-light">
                            <option value="Fitoterapico">Fitoterapico</option>
                            <option value="Micoterapico">Micoterapico</option>
                            <option value="Floriterapico">Floriterapico</option>
                            <option value="Oligoelemento">Oligoelemento</option>
                            <option value="Vitamina">Vitamina</option>
                            <option value="Generico" selected>Altro / Generico</option>
                        </select>
                    </div>
                    <div class="mb-4">
                        <label class="form-label small fw-semibold text-muted">Dosaggio consigliato predefinito</label>
                        <input type="text" name="dosaggio_standard" class="form-control bg-light" placeholder="Es. 30 gocce mattina e sera (facoltativo)">
                        <div class="form-text small opacity-75">Questo verrà autocompilato quando lo selezioni in visita.</div>
                    </div>
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary py-3 rounded-3 text-white fw-bold shadow-sm hover-lift">Registra nel Catalogo</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    // Salvataggio nuovo rimedio
    document.getElementById('form-nuovo-rimedio').addEventListener('submit', async function(e) {
        e.preventDefault();
        const data = new FormData(this);
        const res = await fetch('ajax_handlers.php', { method: 'POST', body: data }).then(r => r.json());
        if(res.success) {
            window.location.reload();
        } else {
            Swal.fire('Errore', res.error, 'error');
        }
    });

    // Modifica stato (attivo/sospeso) rimedio
    async function toggleStato(id, nuovoStato) {
        const data = new FormData();
        data.append('action', 'toggle_medicinale');
        data.append('id', id);
        data.append('attivo', nuovoStato);
        const res = await fetch('ajax_handlers.php', { method: 'POST', body: data }).then(r => r.json());
        if(res.success) {
            window.location.reload();
        } else {
            Swal.fire('Errore', res.error, 'error');
        }
    }

    // Aggiungi Alimento
    document.getElementById('form-nuovo-alimento').addEventListener('submit', async function(e) {
        e.preventDefault();
        const data = new FormData(this);
        const res = await fetch('ajax_handlers.php', { method: 'POST', body: data }).then(r => r.json());
        if(res.success) {
            window.location.reload();
        } else {
            Swal.fire('Errore', res.error, 'error');
        }
    });

    // Elimina Alimento
    function eliminaAlimento(id) {
        Swal.fire({
            title: 'Sei sicuro?',
            text: "Vuoi eliminare questa voce alimentare in via definitiva?",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#e74c3c',
            cancelButtonColor: '#bdc3c7',
            confirmButtonText: 'Sì, elimina',
            cancelButtonText: 'Annulla'
        }).then(async (result) => {
            if (result.isConfirmed) {
                const data = new FormData();
                data.append('action', 'delete_alimento');
                data.append('id', id);
                const res = await fetch('ajax_handlers.php', { method: 'POST', body: data }).then(r => r.json());
                if(res.success) {
                    window.location.reload();
                } else {
                    Swal.fire('Errore', res.error, 'error');
                }
            }
        });
    }
</script>

<?php include 'includes/footer.php'; ?>
