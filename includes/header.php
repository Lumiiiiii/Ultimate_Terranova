<!DOCTYPE html>
<html lang="it" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="color-scheme" content="light">
    <title><?php echo isset($pageTitle) ? $pageTitle . " - Aequa" : "Aequa"; ?></title>
    <link rel="icon" type="image/png" href="assets/img/logo.png">

    <script>
      // ── ANTI-FLICKER ENGINE ──────────────────────────────────────────────────
      // Eseguito PRIMA di qualsiasi CSS. Imposta data-bs-theme dal localStorage
      // Bootstrap 5.3 gestisce automaticamente il dark mode tramite questo attributo.
      (function() {
        var saved = 'light';
        try { saved = localStorage.getItem('aequa-theme') || 'light'; } catch(e) {}
        var html = document.documentElement;
        html.setAttribute('data-bs-theme', saved);
        html.style.colorScheme = saved;

        // Inietta CSS critico inline PRIMA che Bootstrap/style.css vengano caricati
        // Questo garantisce che lo sfondo corretto sia visibile fin dal primo frame
        var s = document.createElement('style');
        s.id = 'anti-flicker-critical';
        var css = '*, *::before, *::after { transition: none !important; animation-duration: 0s !important; }';
        if (saved === 'dark') {
          css += 'html, body { background-color: #0f1117 !important; color: #cbd5e1 !important; }';
        } else {
          css += 'html, body { background-color: #f8f9fa !important; color: #212529 !important; }';
        }
        s.textContent = css;
        document.head.appendChild(s);
      })();
    </script>

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Bootstrap Datepicker CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-datepicker@1.10.0/dist/css/bootstrap-datepicker3.min.css">
    
    <!-- CSS Globale -->
    <link href="assets/css/style.css?v=<?= time() ?>" rel="stylesheet">

    <script>
      // Rimuovi il blocca-transizioni dopo che il CSS è stato processato
      // Double-rAF assicura che il browser abbia completato il primo paint col tema giusto
      (function() {
        function enableTransitions() {
          requestAnimationFrame(function() {
            requestAnimationFrame(function() {
              var el = document.getElementById('anti-flicker-critical');
              if (el) el.remove();
            });
          });
        }
        // Se il body esiste già, abilita subito; altrimenti aspetta DOMContentLoaded
        if (document.body) {
          enableTransitions();
        } else {
          document.addEventListener('DOMContentLoaded', enableTransitions);
        }
      })();
    </script>
</head>
<body data-instant-intensity="15">
