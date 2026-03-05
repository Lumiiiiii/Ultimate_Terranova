# 🌿 Aequa — Gestionale per Naturopatia

**Aequa** è un gestionale web progettato per uno studio di naturopatia.  
Permette di gestire pazienti, visite, prescrizioni e medicinali tramite un'interfaccia moderna e responsiva.

---

## 📚 Indice

- [Tecnologie utilizzate](#-tecnologie-utilizzate)
- [Architettura del progetto](#-architettura-del-progetto)
- [Schema del database](#-schema-del-database)
- [Struttura dei file](#-struttura-dei-file)
- [Flusso di navigazione](#-flusso-di-navigazione)
- [Pattern e concetti chiave](#-pattern-e-concetti-chiave)
- [Spiegazione dettagliata dei file](#-spiegazione-dettagliata-dei-file)
  - [config/database.php](#1-configdatabasephp--connessione-al-database)
  - [includes/Patient.php](#2-includespatientphp--classe-paziente)
  - [includes/Note.php](#3-includesnotephp--classe-nota)
  - [login.php](#4-loginphp--pagina-di-login)
  - [index.php](#5-indexphp--dashboard-principale)
  - [paziente_nuovo.php](#6-paziente_nuovophp--registrazione-nuovo-paziente)
  - [ajax_notes.php](#7-ajax_notesphp--api-per-il-salvataggio-note)
  - [logout.php](#8-logoutphp--disconnessione)
  - [db.sql](#9-dbsql--script-di-creazione-database)
- [Come installare e avviare il progetto](#-come-installare-e-avviare-il-progetto)

---

## 🛠 Tecnologie utilizzate

| Tecnologia | Ruolo | Perché l'abbiamo scelta |
|---|---|---|
| **PHP 8+** | Linguaggio backend | Linguaggio server-side maturo, integrato in XAMPP, ideale per applicazioni web con database |
| **MySQL** | Database relazionale | Perfetto per dati strutturati con relazioni (pazienti → visite → prescrizioni) |
| **PDO** | Interfaccia PHP per database | Previene SQL Injection tramite **prepared statements**, più sicuro di `mysqli` |
| **Bootstrap 5** | Framework CSS | Fornisce componenti pronti (card, form, grid) e design responsivo senza scrivere CSS da zero |
| **JavaScript (Vanilla + jQuery)** | Interattività lato client | Gestisce operazioni frontend come ricerca in tempo reale e salvataggio automatico |
| **AJAX (Fetch API)** | Comunicazione asincrona | Permette di salvare dati senza ricaricare la pagina (esperienza utente moderna) |
| **XAMPP** | Server locale di sviluppo | Include Apache + MySQL + PHP in un unico pacchetto, perfetto per lo sviluppo locale |

---

## 🏗 Architettura del progetto

Il progetto segue un'**architettura a 3 livelli** (three-tier architecture):

```
┌─────────────────────────────────────────────────────┐
│                   PRESENTAZIONE                      │
│  login.php │ index.php │ paziente_nuovo.php          │
│  (HTML + CSS + JavaScript + Bootstrap)               │
├─────────────────────────────────────────────────────┤
│                  LOGICA APPLICATIVA                   │
│  includes/Patient.php │ includes/Note.php             │
│  ajax_notes.php (API endpoint)                       │
│  (Classi PHP con metodi per operazioni CRUD)          │
├─────────────────────────────────────────────────────┤
│                    DATI                              │
│  config/database.php (connessione)                    │
│  MySQL Database: terranova_naturopata                 │
│  (7 tabelle con relazioni Foreign Key)                │
└─────────────────────────────────────────────────────┘
```

### Perché questa architettura?

- **Separazione delle responsabilità**: ogni livello ha un compito preciso
- **Manutenibilità**: si può modificare il frontend senza toccare il database
- **Riusabilità**: le classi `Patient` e `Note` possono essere usate da qualsiasi pagina

---

## 🗄 Schema del database

Il database `terranova_naturopata` contiene **7 tabelle** relazionate tra loro:

```
┌──────────────┐     ┌──────────────┐     ┌───────────────┐
│   pazienti   │────▶│   anamnesi   │     │  medicinali   │
│   (id, PK)   │     │ (paziente_id)│     │   (id, PK)    │
└──────┬───────┘     └──────────────┘     └───────┬───────┘
       │                                          │
       │             ┌──────────────┐             │
       ├────────────▶│    visite    │◀────────────┤
       │             │ (paziente_id)│             │
       │             └──────┬───────┘             │
       │                    │                     │
       │             ┌──────┴───────┐             │
       ├────────────▶│ prescrizioni │◀────────────┘
       │             │(paz_id,med_id│
       │             │  visita_id)  │
       │             └──────────────┘
       │
       │             ┌───────────────┐    ┌────────────────┐
       └────────────▶│alimenti_evit. │───▶│lista_alimenti  │
                     │ (paziente_id) │    │   (id, PK)     │
                     └───────────────┘    └────────────────┘

┌───────────────────┐
│ promemoria_veloce │  (tabella indipendente, riga unica)
│    (id=1, testo)  │
└───────────────────┘
```

### Relazioni chiave (Foreign Keys):

| Tabella figlio | → Tabella padre | Significato | ON DELETE |
|---|---|---|---|
| `anamnesi.paziente_id` | → `pazienti.id` | Ogni anamnesi appartiene a un paziente | CASCADE |
| `visite.paziente_id` | → `pazienti.id` | Ogni visita appartiene a un paziente | CASCADE |
| `prescrizioni.paziente_id` | → `pazienti.id` | Ogni prescrizione è per un paziente | CASCADE |
| `prescrizioni.medicinale_id` | → `medicinali.id` | Ogni prescrizione usa un medicinale | CASCADE |
| `prescrizioni.visita_id` | → `visite.id` | Ogni prescrizione nasce da una visita | CASCADE |
| `alimenti_evitare.paziente_id` | → `pazienti.id` | Alimenti vietati per un paziente | CASCADE |
| `alimenti_evitare.lista_alimenti_id` | → `lista_alimenti.id` | Riferimento all'alimento | CASCADE |

> **CASCADE** significa che se eliminiamo un paziente, tutte le sue visite, anamnesi e prescrizioni vengono eliminate automaticamente.

---

## 📁 Struttura dei file

```
Ultimate_Terranova/
│
├── config/
│   └── database.php          ← Connessione al database (Singleton Pattern)
│
├── includes/
│   ├── Patient.php            ← Classe per la gestione dei pazienti (CRUD)
│   └── Note.php               ← Classe per il promemoria veloce
│
├── login.php                  ← Pagina di autenticazione
├── index.php                  ← Dashboard principale (homepage)
├── paziente_nuovo.php         ← Form per registrare un nuovo paziente
├── ajax_notes.php             ← Endpoint API per il salvataggio note via AJAX
├── logout.php                 ← Script di disconnessione
├── db.sql                     ← Script SQL per creare il database e le tabelle
│
└── README.md                  ← Questo file
```

---

## 🔄 Flusso di navigazione

```
           ┌──────────┐
           │ login.php│ ← L'utente inserisce la password
           └────┬─────┘
                │ (password corretta)
                ▼
           ┌──────────┐
           │ index.php│ ← Dashboard: vede pazienti, visite, note
           └────┬─────┘
                │
       ┌────────┼────────────────────┐
       ▼        ▼                    ▼
  ┌─────────┐ ┌───────────────┐ ┌──────────┐
  │paziente │ │ajax_notes.php │ │logout.php│
  │nuovo.php│ │(salva note    │ │(esce)    │
  └─────────┘ │ in background)│ └──────────┘
              └───────────────┘
```

---

## 🧩 Pattern e concetti chiave

### 1. Singleton Pattern (`database.php`)

**Domanda tipica del professore:** *"Perché avete usato il Singleton per la connessione al database?"*

Il **Singleton** è un design pattern che garantisce che una classe abbia **una sola istanza** in tutta l'applicazione. Lo usiamo per la connessione al database perché:

- **Efficienza**: aprire una connessione MySQL è "costoso". Con il Singleton ne apriamo **una sola** e la riutilizziamo ovunque.
- **Consistenza**: tutte le query usano la stessa connessione, evitando conflitti.
- **Semplicità**: basta chiamare `getDB()` da qualsiasi file per ottenere la connessione.

```php
// SENZA Singleton (❌ male): ogni file apre una nuova connessione
$conn1 = new PDO(...); // index.php
$conn2 = new PDO(...); // paziente_nuovo.php → connessione duplicata!

// CON Singleton (✅ bene): una sola connessione condivisa
$conn = getDB(); // restituisce sempre la STESSA connessione
```

### 2. Prepared Statements (Sicurezza SQL)

**Domanda tipica del professore:** *"Come prevenite le SQL Injection?"*

Usiamo i **prepared statements** di PDO. Invece di inserire i dati direttamente nella query SQL (pericoloso), usiamo dei **segnaposto** (`:nome`, `?`):

```php
// ❌ PERICOLOSO (SQL Injection possibile):
$query = "SELECT * FROM pazienti WHERE id = " . $_GET['id'];

// ✅ SICURO (Prepared Statement):
$query = $db->prepare("SELECT * FROM pazienti WHERE id = :id");
$query->execute([':id' => $_GET['id']]);
```

Il database tratta i segnaposto come **dati puri**, mai come codice SQL. Anche se un utente malintenzionato inserisce `1; DROP TABLE pazienti`, il prepared statement lo tratta come una semplice stringa, non come un comando SQL.

### 3. Sessioni PHP (`$_SESSION`)

**Domanda tipica del professore:** *"Come gestite l'autenticazione?"*

Usiamo le **sessioni PHP** per ricordare che l'utente si è loggato:

1. `login.php` → controlla la password e salva `$_SESSION['logged_in'] = true`
2. Ogni altra pagina controlla se `$_SESSION['logged_in']` è `true`
3. Se non lo è, **reindirizza** alla pagina di login
4. `logout.php` → distrugge la sessione con `session_destroy()`

```
login.php                    index.php
────────                     ─────────
POST → password corretta?    session_start()
  Sì → $_SESSION = true     $_SESSION['logged_in'] === true?
       redirect → index.php   Sì → mostra dashboard
  No → errore                  No → redirect → login.php
```

### 4. AJAX e Fetch API (Note Veloci)

**Domanda tipica del professore:** *"Come funziona il salvataggio automatico?"*

Le Note Veloci si salvano **senza ricaricare la pagina** usando la **Fetch API**:

1. L'utente scrive nella textarea
2. JavaScript aspetta **1 secondo** dopo l'ultimo tasto premuto (debounce)
3. Se l'utente ha smesso di scrivere, invia il testo al server via **fetch (AJAX)**
4. `ajax_notes.php` riceve il testo e lo salva nel database
5. La risposta JSON conferma il successo e aggiorna il messaggio di stato

```
[Utente scrive] → [1s di pausa] → [fetch POST → ajax_notes.php] → [DB aggiornato]
                                         ↓
                                   [Risposta JSON: {success: true}]
                                         ↓
                              [UI: "Salvato alle 14:32" ✅]
```

### 5. CRUD (Create, Read, Update, Delete)

**Domanda tipica del professore:** *"Cosa significa CRUD e dove lo implementate?"*

CRUD sono le **4 operazioni fondamentali** su un database:

| Operazione | SQL | Metodo PHP | Dove si usa |
|---|---|---|---|
| **C**reate | `INSERT INTO` | `createPatient($data)` | `paziente_nuovo.php` |
| **R**ead | `SELECT` | `getPatient($id)`, `getAllPatients()` | `index.php` |
| **U**pdate | `UPDATE` | `updatePatient($id, $data)` | Pagina dettaglio paziente |
| **D**elete | `DELETE` | `deletePatient($id)` | Eliminazione paziente |

---

## 📖 Spiegazione dettagliata dei file

---

### 1. `config/database.php` — Connessione al database

📍 **Percorso:** `config/database.php` · **78 righe**

**Scopo:** Gestisce la connessione al database MySQL usando il **Singleton Pattern** e PDO.

#### Costanti di configurazione (righe 1-8)

```php
define('DB_HOST', 'localhost');              // server del database
define('DB_NAME', 'terranova_naturopata');   // nome database
define('DB_USER', 'root');                   // utente MySQL
define('DB_PASS', '');                       // password (vuota in XAMPP)
define('DB_CHARSET', 'utf8mb4');             // supporto caratteri speciali e emoji
```

`define()` crea **costanti globali**: valori che non cambiano mai durante l'esecuzione. Sono accessibili da qualsiasi punto del codice.

> **utf8mb4** supporta tutti i caratteri Unicode, incluse emoji e caratteri speciali europei (à, è, ü, ecc.).

#### Classe Database con Singleton (righe 12-69)

```php
class Database
{
    private static $instance = null;  // l'unica istanza della classe
    private $connection;              // la connessione PDO

    private function __construct() { ... }  // PRIVATO: nessuno può fare "new Database()"
```

**Perché il costruttore è `private`?**  
Per impedire che qualcuno crei più istanze con `new Database()`. L'unico modo per ottenere un'istanza è tramite `getInstance()`.

**Il costruttore `__construct()` fa questo:**

```php
$dsn = "mysql:host=localhost;dbname=terranova_naturopata;charset=utf8mb4";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,  // lancia eccezioni sugli errori
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,        // risultati come array associativo
    PDO::ATTR_EMULATE_PREPARES   => false,                   // prepared statements reali (più sicuri)
];
$this->connection = new PDO($dsn, DB_USER, DB_PASS, $options);
```

| Opzione PDO | Cosa fa | Perché è importante |
|---|---|---|
| `ERRMODE_EXCEPTION` | Lancia eccezioni PHP quando una query fallisce | Senza di essa, gli errori passerebbero inosservati |
| `FETCH_ASSOC` | I risultati sono array con chiavi = nomi colonne | `$row['nome']` invece di `$row[0]` |
| `EMULATE_PREPARES = false` | Usa i prepared statements nativi di MySQL | Maggiore sicurezza contro SQL Injection |

**Il metodo `getInstance()`:**

```php
public static function getInstance()
{
    if (self::$instance === null) {       // se non esiste ancora un'istanza...
        self::$instance = new self();     // ...creala (una volta sola)
    }
    return self::$instance;               // restituisci sempre la stessa istanza
}
```

**Protezioni aggiuntive contro la duplicazione:**

- `private function __clone() {}` → impedisce `clone $obj` (copia dell'oggetto)
- `public function __wakeup()` → impedisce `unserialize()` (deserializzazione)

#### Funzione helper `getDB()` (righe 74-77)

```php
function getDB()
{
    return Database::getInstance()->getConnection();
}
```

Scorciatoia che in una sola riga: (1) ottiene l'istanza Singleton, (2) restituisce la connessione PDO. Qualsiasi file può semplicemente chiamare `$db = getDB()`.

---

### 2. `includes/Patient.php` — Classe Paziente

📍 **Percorso:** `includes/Patient.php` · **177 righe**

**Scopo:** Contiene tutti i metodi per gestire i pazienti (CRUD + ricerca + visite).

#### Struttura della classe

```php
class Patient
{
    private $db;  // connessione PDO al database

    public function __construct()
    {
        $this->db = getDB();  // ottiene la connessione Singleton
    }
```

Ogni metodo segue sempre lo stesso schema in 3 passi:

```
1. Scrivi la query SQL con segnaposto   → $queryText = "SELECT ... WHERE id = :id"
2. Prepara la query                     → $query = $this->db->prepare($queryText)
3. Esegui con i valori reali            → $query->execute([':id' => $id])
```

#### Metodo `createPatient($data)` — Crea un nuovo paziente

```php
public function createPatient($data)
{
    $queryText = "INSERT INTO pazienti (nome_cognome, data_nascita, telefono, indirizzo, email, professione)
                  VALUES (:nome_cognome, :data_nascita, :telefono, :indirizzo, :email, :professione)";

    $query = $this->db->prepare($queryText);
    $query->execute([
        ':nome_cognome' => $data['nome_cognome'],
        ':data_nascita' => !empty($data['data_nascita']) ? $data['data_nascita'] : null,
        ':telefono'     => $data['telefono'] ?? null,
        // ... altri campi
    ]);

    return $this->db->lastInsertId();  // restituisce l'ID generato automaticamente
}
```

**Punti chiave:**
- I segnaposto `:nome_cognome` vengono sostituiti in modo sicuro da PDO
- `$data['telefono'] ?? null` → l'operatore **null coalescing** (`??`): se il campo non esiste, usa `null`
- `$this->db->lastInsertId()` → restituisce l'`id AUTO_INCREMENT` appena creato da MySQL

#### Metodo `getPatient($id)` — Legge un paziente

```php
$queryText = "SELECT *, TIMESTAMPDIFF(YEAR, data_nascita, CURDATE()) AS eta
              FROM pazienti WHERE id = :id";
```

- `SELECT *` → seleziona tutte le colonne
- `TIMESTAMPDIFF(YEAR, data_nascita, CURDATE()) AS eta` → **calcola l'età** in anni direttamente in SQL, confrontando la data di nascita con la data odierna. Il risultato è disponibile come campo `eta`.
- `->fetch()` → restituisce **una sola riga** come array associativo

#### Metodo `updatePatient($id, $data)` — Aggiorna un paziente

```php
$queryText = "UPDATE pazienti
              SET nome_cognome = :nome_cognome,
                  data_nascita = :data_nascita,
                  ...
              WHERE id = :id";
```

- `UPDATE ... SET` → modifica i valori delle colonne specificate
- `WHERE id = :id` → **fondamentale**: senza `WHERE` aggiornerebbe TUTTI i pazienti!
- Restituisce `true`/`false`

#### Metodo `deletePatient($id)` — Elimina un paziente

```php
$queryText = "DELETE FROM pazienti WHERE id = :id";
```

Grazie a `ON DELETE CASCADE` nelle foreign key, eliminando un paziente vengono automaticamente eliminati anche tutti i suoi record nelle tabelle correlate (visite, anamnesi, prescrizioni).

#### Metodo `getRecentPatients($limit)` — Ultimi pazienti registrati

```php
$queryText = "SELECT ... FROM pazienti ORDER BY data_creazione DESC LIMIT :limit";
$query->bindValue(':limit', (int) $limit, PDO::PARAM_INT);
```

**Perché `bindValue` e non `execute`?**  
La clausola `LIMIT` richiede un **intero puro**. Con `execute()` i valori sono sempre stringhe e MySQL potrebbe dare errore. `bindValue` con `PDO::PARAM_INT` forza il tipo a intero.

#### Metodo `searchPatients($searchTerm)` — Ricerca pazienti

```php
$term = "%$searchTerm%";  // "Mario" diventa "%Mario%"

$queryText = "SELECT ... FROM pazienti
              WHERE nome_cognome LIKE ? OR telefono LIKE ? OR email LIKE ?
              LIMIT 20";

$query->execute([$term, $term, $term]);  // stesso termine, 3 colonne
```

- `LIKE '%Mario%'` → trova qualsiasi stringa che **contiene** "Mario" (anche "Mario Rossi", "Luigi Mario")
- Il `%` è il **carattere jolly** SQL: significa "qualsiasi sequenza di caratteri"
- Lo stesso termine viene passato 3 volte perché cerchiamo in 3 campi diversi

#### Metodo `getRecentVisits($limit)` — Visite recenti con JOIN

```php
$queryText = "SELECT v.*, p.nome_cognome
              FROM visite v
              JOIN pazienti p ON v.paziente_id = p.id
              ORDER BY v.data_visita DESC, v.id DESC
              LIMIT :limit";
```

**Cosa fa la JOIN?**  
La tabella `visite` contiene solo `paziente_id` (un numero). Per mostrare il **nome** del paziente, facciamo una `JOIN` con la tabella `pazienti`:

```
visite (v)                     pazienti (p)
┌────┬──────────────┐         ┌────┬──────────────┐
│ id │ paziente_id  │ ──ON──▶ │ id │ nome_cognome │
│ 1  │     3        │         │ 3  │ Mario Rossi  │
└────┴──────────────┘         └────┴──────────────┘

Risultato JOIN: { id: 1, paziente_id: 3, nome_cognome: "Mario Rossi", ... }
```

---

### 3. `includes/Note.php` — Classe Nota

📍 **Percorso:** `includes/Note.php` · **33 righe**

**Scopo:** Gestisce il promemoria veloce della dashboard. Usa una **singola riga** nel database (id=1) che viene sempre aggiornata.

```php
class Note
{
    private $db;

    public function __construct()
    {
        $this->db = getDB();
    }

    // LETTURA: recupera il testo dalla riga con id = 1
    public function getNote()
    {
        $query = $this->db->query("SELECT testo FROM promemoria_veloce WHERE id = 1");
        $result = $query->fetch();
        return $result ? $result['testo'] : '';  // stringa vuota se non trovato
    }

    // SCRITTURA: aggiorna il testo della riga con id = 1
    public function updateNote($testo)
    {
        $queryText = "UPDATE promemoria_veloce SET testo = :testo WHERE id = 1";
        $query = $this->db->prepare($queryText);
        return $query->execute([':testo' => $testo]);
    }
}
```

**Perché una riga sola?**  
Il promemoria è uno spazio unico condiviso. Invece di creare nuove righe ogni volta, aggiorniamo sempre la stessa riga (`id = 1`). Nel file `db.sql` inseriamo questa riga iniziale:

```sql
INSERT INTO promemoria_veloce (id, testo) VALUES (1, '');
```

---

### 4. `login.php` — Pagina di Login

📍 **Percorso:** `login.php` · **320 righe** (PHP + HTML + CSS + JS)

**Scopo:** Pagina di autenticazione con design glassmorphism, particelle animate e validazione della password.

#### Parte PHP — Autenticazione (righe 1-18)

```php
session_start();  // avvia il meccanismo delle sessioni PHP

$password_corretta = "naturopata";  // password fissa

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $password_inserita = $_POST['password'] ?? '';

    if ($password_inserita === $password_corretta) {
        $_SESSION['logged_in'] = true;    // salva il login nella sessione
        header('Location: index.php');     // reindirizza alla dashboard
        exit;
    } else {
        $errore = "Password non corretta. Riprova.";
    }
}
```

**Flusso di autenticazione:**
1. La prima volta che visiti la pagina, `$_SERVER["REQUEST_METHOD"]` è `GET` → il blocco `if (POST)` viene saltato e si mostra il form
2. Quando invii il form, il metodo diventa `POST` → si confronta la password
3. Se corretta: `$_SESSION['logged_in'] = true` + redirect a `index.php`
4. Se sbagliata: si mostra il messaggio di errore

#### Parte CSS — Design Glassmorphism (righe 36-203)

**Glassmorphism** è una tendenza di design che simula pannelli di vetro smerigliato:

```css
.glass-card {
    background: rgba(255, 255, 255, 0.04);   /* sfondo quasi trasparente */
    backdrop-filter: blur(24px);              /* sfoca ciò che c'è dietro */
    border: 1px solid rgba(255, 255, 255, 0.08);  /* bordo sottilissimo */
    box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);   /* ombra profonda */
}
```

**Particelle animate** — 10 cerchini verdi che fluttuano dal basso verso l'alto:

```css
@keyframes float {
    0%   { transform: translateY(100vh) scale(0); opacity: 0; }  /* parte dal basso, invisibile */
    10%  { opacity: 1; }                                          /* diventa visibile */
    90%  { opacity: 1; }
    100% { transform: translateY(-10vh) scale(1); opacity: 0; }  /* arriva in alto, scompare */
}
```

Le particelle hanno durate diverse (`animation-duration`) e ritardi diversi (`animation-delay`) per sembrare casuali.

**Animazione di errore (shake):**

```css
@keyframes shake {
    10%, 90%  { transform: translateX(-1px); }
    20%, 80%  { transform: translateX(2px); }
    30%, 50%, 70% { transform: translateX(-3px); }
    40%, 60%  { transform: translateX(3px); }
}
```

Quando la password è sbagliata, il messaggio trema da sinistra a destra per attirare l'attenzione.

#### Parte JavaScript — Interattività (righe 290-316)

**Toggle visibilità password:**

```javascript
toggleBtn.addEventListener('click', function () {
    const isPassword = passwordInput.type === 'password';
    passwordInput.type = isPassword ? 'text' : 'password';
    // Cambia anche l'icona dell'occhio
});
```

**Stato di caricamento del bottone:**

```javascript
document.getElementById('loginForm').addEventListener('submit', function () {
    const btn = document.getElementById('loginBtn');
    btn.innerHTML = '<span class="spinner-border ..."></span> Accesso in corso…';
    btn.classList.add('disabled');  // impedisce doppi click
});
```

---

### 5. `index.php` — Dashboard Principale

📍 **Percorso:** `index.php` · **544 righe**

**Scopo:** Homepage dell'applicazione dopo il login. Mostra statistiche, lista pazienti, visite recenti, note veloci e barra di ricerca.

#### Parte PHP — Controllo accesso e dati (righe 1-36)

```php
session_start();

// GUARDIA DI SICUREZZA: se non loggato, torna al login
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

// Carica le dipendenze
include __DIR__ . '/config/database.php';
include __DIR__ . '/includes/patient.php';
include __DIR__ . '/includes/Note.php';

// Crea le istanze dei "manager"
$patientManager = new Patient();
$noteManager    = new Note();

// Recupera i dati da mostrare nella dashboard
$totalPatients = $patientManager->countPatients();  // numero totale
$allPatients   = $patientManager->getAllPatients();  // lista completa
$recentVisits  = $patientManager->getRecentVisits(); // visite recenti
$noteText      = $noteManager->getNote();            // testo nota veloce
```

> `__DIR__` è una costante magica PHP che restituisce il percorso della directory del file corrente. Garantisce che i percorsi di inclusione funzionino correttamente indipendentemente da dove viene eseguito lo script.

#### Sidebar — Navigazione laterale fissa (righe 78-311)

La sidebar è una barra laterale **fissa** (`position: fixed`) che rimane sempre visibile a sinistra dello schermo:

```css
.sidebar {
    position: fixed;    /* non si muove con lo scroll */
    top: 0; left: 0; bottom: 0;  /* occupa tutta l'altezza */
    width: 260px;
    z-index: 1000;      /* sopra tutto il resto */
}
.main-content {
    margin-left: 260px; /* il contenuto inizia dopo la sidebar */
}
```

**Responsività mobile** — su schermi piccoli (< 768px) la sidebar si nasconde:

```css
@media (max-width: 768px) {
    .sidebar { transform: translateX(-100%); }     /* nascosta fuori schermo */
    .sidebar.open { transform: translateX(0); }    /* visibile */
    .main-content { margin-left: 0; }              /* contenuto a piena larghezza */
}
```

Un pulsante hamburger (`☰`) appare solo su mobile e alterna la classe `open`:

```html
<button onclick="document.querySelector('.sidebar').classList.toggle('open')">☰</button>
```

#### Dashboard Content — Card informative (righe 314-471)

La dashboard usa il **sistema a griglia di Bootstrap** (`row` + `col-md-*`):

```
┌────────────┬───────────────────────────┬────────────┐
│ col-md-3   │ col-md-6                  │ col-md-3   │
│ Totale     │ Pazienti Registrati       │ Nuovo      │
│ Pazienti   │ (lista scrollabile)       │ Paziente   │
│ [42]       │ Mario Rossi, 35 anni      │ [+ Crea]   │
│            │ Luigi Verdi, 28 anni      │            │
│            │ ...                       │            │
├────────────┴───────────────────────────┴────────────┤
│ col-12: Barra di ricerca                            │
├────────────┬────────────────┬───────────────────────┤
│ col-md-4   │ col-md-4       │ col-md-4              │
│ Note       │ Visite Recenti │ Archivio Medicinali   │
│ Veloci     │                │ [link]                │
└────────────┴────────────────┴───────────────────────┘
```

**Iniziale del paziente come avatar:**

```php
<?= strtoupper(substr($patient['nome_cognome'], 0, 1)) ?>
```

- `substr($nome, 0, 1)` → prende il primo carattere ("Mario" → "M")
- `strtoupper()` → lo rende maiuscolo

#### JavaScript — Ricerca live e salvataggio note (righe 477-542)

**Ricerca pazienti in tempo reale:**

```javascript
searchInput.addEventListener('input', function (e) {
    if (typeof searchPatients === 'function') {
        searchPatients(e.target.value);    // chiama funzione esterna (main.js)
    }
});
```

**Salvataggio automatico note con debounce:**

```javascript
let typingTimer;
const doneTypingInterval = 1000;  // 1 secondo

notesTextarea.addEventListener('input', function () {
    clearTimeout(typingTimer);                         // cancella timer precedente
    saveStatus.textContent = "Salvataggio in corso..."; // feedback visuale
    typingTimer = setTimeout(saveNotes, doneTypingInterval);  // salva tra 1s
});
```

**Cos'è il "debounce"?**  
Se l'utente scrive "ciao", vengono generati 4 eventi `input` (c, i, a, o). Senza debounce faremmo 4 chiamate al server. Con il debounce, aspettiamo 1 secondo di inattività e poi facciamo **una sola** chiamata. Ogni nuovo tasto premuto **resetta** il timer.

```
c → [timer 1s] ← cancellato da "i"
i → [timer 1s] ← cancellato da "a"
a → [timer 1s] ← cancellato da "o"
o → [timer 1s] → SCADUTO → saveNotes() → fetch POST → ajax_notes.php
```

**Funzione `saveNotes()` con Fetch API:**

```javascript
fetch('ajax_notes.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ testo: text })
})
.then(response => response.json())
.then(data => {
    if (data.success) {
        saveStatus.textContent = "Salvato alle " + timeString + " ✅";
    }
})
.catch(error => {
    saveStatus.textContent = "Errore durante il salvataggio! ❌";
});
```

La **Fetch API** è il modo moderno di fare richieste HTTP da JavaScript. Si usa per comunicare con il server senza ricaricare la pagina (AJAX). La catena `.then()` gestisce la risposta in modo **asincrono**.

---

### 6. `paziente_nuovo.php` — Registrazione Nuovo Paziente

📍 **Percorso:** `paziente_nuovo.php` · **384 righe**

**Scopo:** Form per inserire un nuovo paziente nel database.

#### Parte PHP — Validazione e salvataggio (righe 1-44)

```php
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $data = [
        'nome_cognome' => trim($_POST['nome_cognome'] ?? ''),
        'data_nascita' => $_POST['data_nascita'] ?? null,
        'telefono'     => trim($_POST['telefono'] ?? ''),
        // ...
    ];

    // Validazione
    if (empty($data['nome_cognome'])) {
        $errore = "Il campo Nome e Cognome è obbligatorio.";
    } elseif (empty($data['data_nascita'])) {
        $errore = "Il campo Data di Nascita è obbligatorio.";
    } else {
        $newId = $patientManager->createPatient($data);
        if ($newId) {
            header("Location: index.php");  // redirect alla dashboard
            exit;
        }
    }
}
```

**`trim()`** rimuove spazi vuoti all'inizio e alla fine: `" Mario "` → `"Mario"`.

**Flusso:**
1. Prima visita (GET) → mostra il form vuoto
2. Submit (POST) → valida i dati
3. Se validi → salva nel DB e redirect alla dashboard
4. Se errore → mostra il messaggio e **mantiene i dati inseriti** nel form

**Come i dati restano nel form dopo un errore?**

```php
<input ... value="<?= htmlspecialchars($_POST['nome_cognome'] ?? '') ?>">
```

`$_POST` contiene ancora i dati inviati, quindi li reiniettiamo nel `value` dell'input. `htmlspecialchars()` previene attacchi **XSS** escapando caratteri HTML speciali.

#### Bootstrap Datepicker — Selezione data di nascita (righe 353-372)

```javascript
$('#data_visuale').datepicker({
    language: 'it',              // calendario in italiano
    format: 'dd/mm/yyyy',        // formato visualizzato (es. 15/03/1990)
    startView: 2,                // apre sulla vista DECENNI → per trovare l'anno velocemente
    endDate: new Date(),         // non permette date future
    startDate: '01/01/1920',     // data minima
    autoclose: true              // si chiude dopo la selezione
}).on('changeDate', function(e) {
    // Converte la data in formato MySQL (YYYY-MM-DD)
    document.getElementById('data_nascita').value = year + '-' + month + '-' + day;
});
```

**Doppio campo data:**
- `#data_visuale` → campo visibile dall'utente, formato italiano (`15/03/1990`)
- `#data_nascita` → campo nascosto (`type="hidden"`), formato MySQL (`1990-03-15`)

Quando l'utente seleziona una data nel datepicker, JavaScript converte il formato e popola il campo nascosto.

---

### 7. `ajax_notes.php` — API per il Salvataggio Note

📍 **Percorso:** `ajax_notes.php` · **28 righe**

**Scopo:** Endpoint API che riceve le note dalla dashboard e le salva nel database. Comunica esclusivamente in formato **JSON**.

```php
session_start();
require_once __DIR__ . '/includes/Note.php';

// 1. CONTROLLO AUTORIZZAZIONE
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    http_response_code(403);  // 403 Forbidden
    echo json_encode(['success' => false, 'error' => 'Non autorizzato']);
    exit;
}

// 2. GESTIONE RICHIESTA POST
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Legge il corpo della richiesta come JSON
    $data = json_decode(file_get_contents('php://input'), true);
    $testo = $data['testo'] ?? '';

    // 3. SALVATAGGIO
    $noteManager = new Note();
    $result = $noteManager->updateNote($testo);

    // 4. RISPOSTA JSON
    header('Content-Type: application/json');
    if ($result) {
        echo json_encode(['success' => true]);
    } else {
        http_response_code(500);  // 500 Internal Server Error
        echo json_encode(['success' => false, 'error' => 'Errore nel database']);
    }
}
```

**`php://input`** è uno stream speciale di PHP che legge i dati "grezzi" del corpo della richiesta. Lo usiamo perché il frontend invia JSON (non un form tradizionale), quindi i dati non sono in `$_POST`.

**`json_decode(..., true)`** converte la stringa JSON in un array associativo PHP:

```
'{"testo":"Ciao"}' → ['testo' => 'Ciao']
```

**Codici HTTP usati:**
- `200` (default) → tutto ok
- `403` → utente non autorizzato (non loggato)
- `500` → errore del server (database)

---

### 8. `logout.php` — Disconnessione

📍 **Percorso:** `logout.php` · **7 righe**

```php
session_start();                      // riapre la sessione corrente
session_destroy();                    // elimina tutti i dati della sessione
header('Location: login.php');        // reindirizza alla pagina di login
exit;
```

Il file più corto del progetto. Tre istruzioni fondamentali:

1. `session_start()` — necessario per accedere alla sessione (anche per distruggerla)
2. `session_destroy()` — cancella `$_SESSION['logged_in']` e tutti gli altri dati
3. `header('Location: ...')` — reindirizza il browser. L'utente si ritrova sulla pagina di login

---

### 9. `db.sql` — Script di Creazione Database

📍 **Percorso:** `db.sql` · **103 righe**

**Scopo:** Crea il database e tutte le 7 tabelle da zero. Si esegue **una sola volta** per inizializzare il sistema.

```sql
DROP DATABASE IF EXISTS terranova_naturopata;  -- elimina se esiste già (reset completo)
CREATE DATABASE terranova_naturopata;          -- crea il database
USE terranova_naturopata;                      -- selezionalo come attivo
```

**Tabella `pazienti`** — Il cuore del sistema:

```sql
CREATE TABLE pazienti (
    id INT AUTO_INCREMENT PRIMARY KEY,    -- chiave primaria, incremento automatico
    nome_cognome VARCHAR(255) NOT NULL,   -- obbligatorio (NOT NULL)
    data_nascita DATE,                    -- formato: YYYY-MM-DD
    telefono VARCHAR(20),
    indirizzo VARCHAR(255),
    email VARCHAR(255),
    professione VARCHAR(255),
    data_creazione TIMESTAMP DEFAULT CURRENT_TIMESTAMP,           -- data inserimento automatica
    data_modifica TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP  -- si aggiorna da sola
);
```

`CURRENT_TIMESTAMP` e `ON UPDATE CURRENT_TIMESTAMP` sono funzionalità MySQL che **assegnano automaticamente** la data/ora corrente all'inserimento e ad ogni modifica.

---

## 🚀 Come installare e avviare il progetto

### Prerequisiti

- **XAMPP** installato (include Apache + MySQL + PHP)

### Passi

1. **Clona il repository** nella cartella `htdocs` di XAMPP:
   ```bash
   cd C:\xampp\htdocs
   git clone https://github.com/TUO-USERNAME/Ultimate_Terranova.git
   ```

2. **Avvia XAMPP**: apri il pannello di controllo e avvia **Apache** e **MySQL**

3. **Crea il database**: apri **phpMyAdmin** (`http://localhost/phpmyadmin`) e:
   - Vai su "SQL"
   - Incolla il contenuto di `db.sql`
   - Clicca "Esegui"

4. **Accedi all'applicazione**: apri il browser e vai su:
   ```
   http://localhost/Ultimate_Terranova/login.php
   ```

5. **Password di accesso:** `naturopata`

---

## 👥 Autori

Progetto sviluppato come esercitazione scolastica per un gestionale di naturopatia.

---

> **Nota:** Questa documentazione è stata scritta per facilitare la comprensione del codice da parte di tutti i membri del team e per rispondere alle domande tecniche durante le presentazioni.
