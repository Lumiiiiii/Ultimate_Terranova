<?php
ob_start();
session_start();

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

include __DIR__ . '/config/database.php';
?>
<?php
$pageTitle = "Calendario";
$currentPage = "calendario";
include 'includes/header.php';
?>
    <!-- FullCalendar CSS Extra (Specifico per questa pagina) -->
    <link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.css" rel="stylesheet">
    <style>
        /* ── CALENDARIO (CARD STYLE) ─────────────────────────────────────────── */
        #calendar-container {
            border: 0 !important;
            border-radius: 1rem !important;
            background: #ffffff;
            padding: 24px;
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075) !important;
        }

        .fc-toolbar {
            margin-bottom: 1.5rem !important;
        }
        .fc-toolbar-chunk:first-child { display: flex; gap: 10px; }
        .fc-toolbar-chunk:last-child { display: flex; gap: 8px; }

        .fc-toolbar-title {
            font-size: 1.25rem !important;
            color: #1a1a2e;
            font-weight: 700;
            text-transform: capitalize;
        }

        .fc-button-primary {
            background-color: #ffffff !important;
            color: #4a5568 !important;
            border: 1px solid #e2e8f0 !important;
            border-radius: 10px !important;
            padding: 8px 16px !important;
            font-size: 0.88rem !important;
            font-weight: 600 !important;
            box-shadow: none !important;
            transition: all 0.2s ease !important;
        }

        .fc-button-primary:hover {
            background-color: #f8f9fa !important;
            border-color: #cbd5e0 !important;
            color: #1a1a2e !important;
        }

        .fc-button-primary:not(:disabled).fc-button-active, 
        .fc-button-primary:not(:disabled):active {
            background-color: #f1f5f9 !important;
            border-color: #cbd5e0 !important;
            color: #1a1a2e !important;
        }
        
        .fc-button-primary:focus { box-shadow: none !important; }

        .fc-theme-standard td, .fc-theme-standard th {
            border: 1px solid #f1f5f9 !important;
        }

        .fc-col-header-cell {
            background: #f8f9fa;
            padding: 14px 0 !important;
            color: #64748b;
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 1.2px;
            font-weight: 700;
        }

        .fc-event {
            border: none !important;
            padding: 4px 10px !important;
            border-radius: 8px !important;
            font-size: 0.82rem !important;
            font-weight: 600 !important;
            box-shadow: 0 2px 4px rgba(0,0,0,0.04);
        }

        .fc-day-today {
            background-color: rgba(46, 204, 113, 0.04) !important;
        }
    </style>

<?php include 'includes/sidebar.php'; ?>

    <div class="main-content">
        <main class="container-xl py-5">
            <header class="mb-5 d-flex justify-content-between align-items-center">
                <div>
                    <h2 class="fw-bold mb-1">Calendario Appuntamenti</h2>
                    <p class="text-muted mb-0">Gestisci le tue visite e appuntamenti.</p>
                </div>
                <button class="btn btn-gradient px-4 py-2 rounded-3 shadow-sm hover-lift fw-bold" id="btnAggiungiEvento">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" class="me-1">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                    </svg>
                    Aggiungi Evento
                </button>
            </header>

            <div id="calendar-container">
                <div id='calendar'></div>
            </div>
        </main>
    </div>

    <!-- Modal rimosso per usare SweetAlert2 -->

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.js'></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/@fullcalendar/core/locales/it.global.min.js"></script>
    
    <script>
      document.addEventListener('DOMContentLoaded', function() {
        var calendarEl = document.getElementById('calendar');

        var calendar = new FullCalendar.Calendar(calendarEl, {
          initialView: 'dayGridMonth',
          locale: 'it',
          height: 'auto',
          contentHeight: 'auto',
          handleWindowResize: true,
          headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,timeGridDay'
          },
          themeSystem: 'bootstrap5',
          events: 'carica_eventi.php',
          selectable: true,
          eventTimeFormat: {
            hour: '2-digit',
            minute: '2-digit',
            meridiem: false,
            hour12: false
          },
          eventDidMount: function(info) {
              if (info.event.extendedProps.color) {
                  info.el.style.backgroundColor = info.event.extendedProps.color;
                  info.el.style.borderColor = info.event.extendedProps.color;
              }
          },
          eventClick: function(info) {
              const event = info.event;
              const startDate = event.start.toISOString().split('T')[0];
              const startTime = event.start.toTimeString().split(' ')[0].substring(0, 5);
              const eventColor = event.extendedProps.color || '#2ecc71';

              Swal.fire({
                  title: 'Gestisci Appuntamento',
                  html: `
                    <div class="mb-3 text-muted" style="font-size: 0.95rem;">${event.title}</div>
                    <div class="d-flex flex-column gap-2">
                        <button id="btn-swal-modifica" class="btn btn-primary rounded-3 py-2 fw-bold d-flex align-items-center justify-content-center gap-2 hover-lift">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                            Modifica
                        </button>
                        <button id="btn-swal-elimina" class="btn btn-light text-danger rounded-3 py-2 fw-bold d-flex align-items-center justify-content-center gap-2 hover-lift" style="background: #fff5f5;">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                            Elimina
                        </button>
                        <button id="btn-swal-chiudi" class="btn btn-light text-muted rounded-3 py-2 fw-medium hover-lift mt-1">
                            Chiudi
                        </button>
                    </div>
                  `,
                  showConfirmButton: false,
                  customClass: {
                      popup: 'rounded-4 border-0 p-4',
                  },
                  didOpen: () => {
                      document.getElementById('btn-swal-modifica').onclick = () => {
                          Swal.close();
                          apriModalModifica(event, startDate, startTime, eventColor, calendar);
                      };
                      document.getElementById('btn-swal-elimina').onclick = () => {
                          Swal.close();
                          confermaElimina(event.id, calendar);
                      };
                      document.getElementById('btn-swal-chiudi').onclick = () => Swal.close();
                  }
              });
          },
          dateClick: function(info) {
              Swal.fire({
                  title: 'Nuovo Appuntamento',
                  html: `
                    <div class="text-start px-1">
                        <div class="mb-3">
                            <label class="form-label small fw-bold text-muted mb-1">Paziente o Motivo</label>
                            <input id="swal-title" class="form-control form-control-sm border-0 bg-light" placeholder="Mario Rossi - Controllo" style="font-size: 0.9rem; padding: 10px;">
                        </div>
                        
                        <div class="row g-3">
                            <div class="col-5">
                                <label class="form-label small fw-bold text-muted mb-1">Ora</label>
                                <input type="time" id="swal-hour" class="form-control form-control-sm border-0 bg-light" value="09:00" style="font-size: 0.9rem; padding: 8px;">
                            </div>
                            <div class="col-7"> 
                            <label class="form-label small fw-bold text-muted mb-1">Data</label>
                            <input type="date" id="swal-date" class="form-control form-control-sm border-0 bg-light" value="" style="font-size: 0.9rem; padding: 8px;">
                            </div>
                            <div class="col-7">
                                <label class="form-label small fw-bold text-muted mb-1">Scegli Colore</label>
                                <div class="d-flex gap-2" id="swal-color-picker">
                                    <div class="swal-color-option selected" style="background: #2ecc71" data-color="#2ecc71"></div>
                                    <div class="swal-color-option" style="background: #3b82f6" data-color="#3b82f6"></div>
                                    <div class="swal-color-option" style="background: #e74c3c" data-color="#e74c3c"></div>
                                    <div class="swal-color-option" style="background: #f1c40f" data-color="#f1c40f"></div>
                                    <div class="swal-color-option" style="background: #9b59b6" data-color="#9b59b6"></div>
                                </div>
                                <input type="hidden" id="swal-color" value="#2ecc71">
                            </div>
                        </div>
                    </div>
                  `,
                  customClass: {
                      popup: 'rounded-4 border-0',
                      confirmButton: 'btn btn-primary px-4',
                      cancelButton: 'btn btn-light px-4'
                  },
                  buttonsStyling: false,
                  didOpen: (popup) => {
                      // Pre-popola la data con quella cliccata sul calendario
                      const dateField = document.getElementById('swal-date');
                      if (dateField) dateField.value = info.dateStr;
                      const colorOptions = document.querySelectorAll('.swal-color-option');
                      colorOptions.forEach(opt => {
                          opt.addEventListener('click', () => {
                              colorOptions.forEach(o => o.classList.remove('selected'));
                              opt.classList.add('selected');
                              document.getElementById('swal-color').value = opt.getAttribute('data-color');
                          });
                      });
                  },
                  showCancelButton: true,
                  confirmButtonText: 'Salva',
                  cancelButtonText: 'Annulla',
                  reverseButtons: true,
                  preConfirm: () => {
                      const title = document.getElementById('swal-title').value;
                      const date = document.getElementById('swal-date').value;
                      const hour = document.getElementById('swal-hour').value;
                      const color = document.getElementById('swal-color').value;
                      if (!title) {
                          Swal.showValidationMessage('Inserisci un titolo');
                          return false;
                      }
                      return { title, date, hour, color };
                  }
              }).then((result) => {
                  if (result.isConfirmed) {
                      var dati = new FormData();
                      dati.append('title', result.value.title);
                      dati.append('start', result.value.date + ' ' + result.value.hour);
                      dati.append('color', result.value.color);

                      fetch('aggiungi_evento.php', {
                          method: 'POST',   
                          body: dati
                      })
                      .then(response => response.json())
                      .then(data => {
                          if (data.success) {
                              calendar.refetchEvents();
                              Swal.fire({
                                  title: 'Salvato!',
                                  icon: 'success',
                                  timer: 1000,
                                  showConfirmButton: false,
                                  customClass: { popup: 'rounded-4 border-0 shadow' }
                              });
                          } else {
                              Swal.fire('Errore', data.error || 'Errore nel salvataggio', 'error');
                          }
                      });
                  }
              });
          },
          buttonText: {
            today:    'Oggi',
            month:    'Mese',
            week:     'Settimana',
            day:      'Giorno'
          }
        });
        calendar.render();

        // ── FUNZIONI DI GESTIONE EVENTI ───────────────────────────────────────
        function apriModalModifica(event, startDate, startTime, eventColor, calendarInstance) {
            Swal.fire({
                title: 'Modifica Appuntamento',
                html: `
                <div class="text-start px-1">
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-muted mb-1">Paziente o Motivo</label>
                        <input id="swal-edit-title" class="form-control form-control-sm border-0 bg-light" value="${event.title}" style="font-size: 0.9rem; padding: 10px;">
                    </div>
                    <div class="row g-3">
                        <div class="col-5">
                            <label class="form-label small fw-bold text-muted mb-1">Ora</label>
                            <input type="time" id="swal-edit-hour" class="form-control form-control-sm border-0 bg-light" value="${startTime}" style="font-size: 0.9rem; padding: 8px;">
                        </div>
                        <div class="col-7">
                            <label class="form-label small fw-bold text-muted mb-1">Colore</label>
                            <div class="d-flex gap-2" id="swal-edit-color-picker">
                                <div class="swal-color-option ${eventColor === '#2ecc71' ? 'selected' : ''}" style="background: #2ecc71" data-color="#2ecc71"></div>
                                <div class="swal-color-option ${eventColor === '#3b82f6' ? 'selected' : ''}" style="background: #3b82f6" data-color="#3b82f6"></div>
                                <div class="swal-color-option ${eventColor === '#e74c3c' ? 'selected' : ''}" style="background: #e74c3c" data-color="#e74c3c"></div>
                                <div class="swal-color-option ${eventColor === '#f1c40f' ? 'selected' : ''}" style="background: #f1c40f" data-color="#f1c40f"></div>
                                <div class="swal-color-option ${eventColor === '#9b59b6' ? 'selected' : ''}" style="background: #9b59b6" data-color="#9b59b6"></div>
                            </div>
                            <input type="hidden" id="swal-edit-color" value="${eventColor}">
                        </div>
                    </div>
                </div>
                `,
                customClass: {
                    popup: 'rounded-4 border-0 shadow',
                    confirmButton: 'btn btn-primary px-4',
                    cancelButton: 'btn btn-light px-4'
                },
                buttonsStyling: false,
                didOpen: () => {
                    const colorOptions = document.querySelectorAll('.swal-color-option');
                    colorOptions.forEach(opt => {
                        opt.addEventListener('click', () => {
                            colorOptions.forEach(o => o.classList.remove('selected'));
                            opt.classList.add('selected');
                            document.getElementById('swal-edit-color').value = opt.getAttribute('data-color');
                        });
                    });
                },
                showCancelButton: true,
                confirmButtonText: 'Aggiorna',
                cancelButtonText: 'Annulla',
                reverseButtons: true,
                preConfirm: () => {
                    const title = document.getElementById('swal-edit-title').value;
                    const hour = document.getElementById('swal-edit-hour').value;
                    const color = document.getElementById('swal-edit-color').value;
                    if (!title) {
                        Swal.showValidationMessage('Inserisci un titolo');
                        return false;
                    }
                    return { title, hour, color };
                }
            }).then((editResult) => {
                if (editResult.isConfirmed) {
                    var dati = new FormData();
                    dati.append('id', event.id);
                    dati.append('title', editResult.value.title);
                    dati.append('start', startDate);
                    dati.append('hour', editResult.value.hour);
                    dati.append('color', editResult.value.color);

                    fetch('modifica_evento.php', { method: 'POST', body: dati })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            calendarInstance.refetchEvents();
                            Swal.fire({
                                title: 'Aggiornato!',
                                icon: 'success',
                                timer: 1000,
                                showConfirmButton: false,
                                customClass: { popup: 'rounded-4 border-0 shadow' }
                            });
                        }
                    });
                }
            });
        }

        function confermaElimina(eventId, calendarInstance) {
            Swal.fire({
                title: 'Sei sicuro?',
                text: "L'appuntamento verrà eliminato permanentemente.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Sì, elimina',
                cancelButtonText: 'Annulla',
                customClass: {
                    popup: 'rounded-4 border-0 shadow',
                    confirmButton: 'btn btn-danger px-4',
                    cancelButton: 'btn btn-light px-4'
                },
                buttonsStyling: false,
                reverseButtons: true
            }).then((deleteResult) => {
                if (deleteResult.isConfirmed) {
                    var dati = new FormData();
                    dati.append('id', eventId);

                    fetch('elimina_evento.php', { method: 'POST', body: dati })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            calendarInstance.refetchEvents();
                            Swal.fire({
                                title: 'Eliminato!',
                                icon: 'success',
                                timer: 1000,
                                showConfirmButton: false,
                                customClass: { popup: 'rounded-4 border-0 shadow' }
                            });
                        }
                    });
                }
            });
        }

        // Bottone superiore "Aggiungi Evento" (usa data odierna)
        document.getElementById('btnAggiungiEvento').addEventListener('click', function() {
            const oggi = new Date().toISOString().split('T')[0];
            calendar.trigger('dateClick', { dateStr: oggi });
        });
      });
    </script>
    <?php include 'includes/footer.php'; ?>
