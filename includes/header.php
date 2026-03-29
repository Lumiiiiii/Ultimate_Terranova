<!DOCTYPE html>
<html lang="it" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="color-scheme" content="light">
    <title><?php echo isset($pageTitle) ? $pageTitle . " - Aequa" : "Aequa"; ?></title>
    <link rel="icon" type="image/png" href="assets/img/logo.png">

    <script>
      // ANTI-FLICKER: eseguito PRIMA di qualsiasi CSS — imposta data-theme dal localStorage
      // NOTA: data-bs-theme resta SEMPRE "light" per non attivare il dark mode di Bootstrap
      (function() {
        var saved = 'light';
        try { saved = localStorage.getItem('aequa-theme') || 'light'; } catch(e) {}
        document.documentElement.setAttribute('data-theme', saved);
        // Forza sempre light per Bootstrap — il dark mode lo gestiamo noi via CSS
        document.documentElement.setAttribute('data-bs-theme', 'light');
        // Blocca color-scheme del browser
        document.documentElement.style.colorScheme = 'light';
      })();
    </script>

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Bootstrap Datepicker CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-datepicker@1.10.0/dist/css/bootstrap-datepicker3.min.css">
    
    <!-- CSS Globale -->
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body>
