<button class="mobile-menu-btn" onclick="document.querySelector('.sidebar').classList.toggle('open')">☰</button>

<aside class="sidebar">
    <div class="sidebar-header d-flex align-items-center gap-2" style="padding-left: 20px;">
        <img src="assets/img/logo.png" alt="Aequa Logo" style="width: 46px; height: 46px; object-fit: contain; flex-shrink: 0;">
        <h3 class="mb-0 fw-bold pb-1" style="background: linear-gradient(135deg, var(--color-primary), var(--color-accent)); -webkit-background-clip: text; -webkit-text-fill-color: transparent; font-size: 1.8rem; letter-spacing: 0.5px;">Aequa</h3>
    </div>
    <nav class="sidebar-nav">
        <div class="nav-section-label">Principale</div>
        <a href="index.php" class="<?php echo ($currentPage == 'index') ? 'active' : ''; ?>">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-4 0a1 1 0 01-1-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 01-1 1" />
            </svg>
            Dashboard
        </a>
        <div class="nav-section-label">Gestione</div>
        <a href="calendario.php" class="<?php echo ($currentPage == 'calendario') ? 'active' : ''; ?>">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
            </svg>
            Calendario
        </a>
        <a href="paziente_nuovo.php" class="<?php echo ($currentPage == 'paziente_nuovo') ? 'active' : ''; ?>">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
            </svg>
            Nuovo Paziente
        </a>
        <a href="medicinali_gestione.php" class="<?php echo ($currentPage == 'archivio') ? 'active' : ''; ?>">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="m20.25 7.5-.625 10.632a2.25 2.25 0 0 1-2.247 2.118H6.622a2.25 2.25 0 0 1-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125Z" />
            </svg>
            Archivio
        </a>
    </nav>
    <div class="sidebar-footer">
        <!-- Pulsante Theme Toggle -->
        <button class="theme-toggle-btn" id="themeToggleBtn" onclick="toggleTheme()">
            <span class="theme-icon" id="themeIcon">🌙</span>
            <span id="themeLabel">Tema Scuro</span>
        </button>
        <a href="logout.php">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
            </svg>
            Esci
        </a>
    </div>
</aside>

<script>
  // Aggiorna l'icona e il testo del pulsante in base al tema corrente
  (function() {
    function updateToggleUI() {
      var isDark = document.documentElement.getAttribute('data-theme') === 'dark';
      var icon = document.getElementById('themeIcon');
      var label = document.getElementById('themeLabel');
      if (icon) icon.textContent = isDark ? '☀️' : '🌙';
      if (label) label.textContent = isDark ? 'Tema Chiaro' : 'Tema Scuro';
    }
    updateToggleUI();
  })();

  function toggleTheme() {
    var html = document.documentElement;
    var isDark = html.getAttribute('data-theme') === 'dark';
    var newTheme = isDark ? 'light' : 'dark';
    html.setAttribute('data-theme', newTheme);
    // data-bs-theme resta SEMPRE "light" — il dark mode lo gestiamo noi via CSS
    html.setAttribute('data-bs-theme', 'light');
    try { localStorage.setItem('aequa-theme', newTheme); } catch(e) {}
    // Aggiorna icona
    var icon = document.getElementById('themeIcon');
    var label = document.getElementById('themeLabel');
    if (icon) icon.textContent = newTheme === 'dark' ? '☀️' : '🌙';
    if (label) label.textContent = newTheme === 'dark' ? 'Tema Chiaro' : 'Tema Scuro';
  }
</script>
