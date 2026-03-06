# 📁 Cartella `config/` — Configurazione

Questa cartella contiene i file di configurazione dell'applicazione.

---

## `database.php` — Connessione al Database

### Cosa fa questo file?

Stabilisce la connessione tra l'applicazione PHP e il database MySQL. È il **primo file che viene incluso** da tutte le pagine che necessitano di accedere ai dati.

### Concetti chiave implementati

#### 1. Costanti di configurazione

```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'terranova_naturopata');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');
```

`define()` crea costanti globali immutabili. A differenza delle variabili (`$var`), le costanti:
- Non hanno il `$` davanti al nome
- Non possono essere modificate dopo la definizione
- Sono accessibili da **qualsiasi scope** (anche dentro funzioni e classi)

#### 2. Singleton Pattern

Il Singleton è un **design pattern creazionale** che garantisce una sola istanza di una classe.

**Come funziona nel nostro codice:**

```
Prima chiamata: getDB()
  └─ Database::getInstance()
       └─ self::$instance è null → crea new Database()
            └─ __construct() → new PDO(...) → connessione creata
                 └─ salva in self::$instance
                      └─ restituisce la connessione

Seconda chiamata: getDB()
  └─ Database::getInstance()
       └─ self::$instance NON è null → restituisce quella già creata
            └─ NESSUNA nuova connessione (performance!)
```

**Tre meccanismi di protezione:**
1. `private function __construct()` → nessuno può fare `new Database()`
2. `private function __clone()` → nessuno può fare `clone $database`
3. `public function __wakeup()` → nessuno può deserializzare l'oggetto

#### 3. PDO (PHP Data Objects)

PDO è l'interfaccia standard PHP per accedere ai database. Vantaggi rispetto a `mysqli`:
- Supporta **12+ database** diversi (MySQL, PostgreSQL, SQLite...)
- **Prepared statements** nativi (sicurezza)
- API orientata agli oggetti più pulita

**Opzioni PDO configurate:**

| Opzione | Valore | Effetto |
|---|---|---|
| `ATTR_ERRMODE` | `ERRMODE_EXCEPTION` | Gli errori SQL lanciano eccezioni PHP (facili da gestire con try/catch) |
| `ATTR_DEFAULT_FETCH_MODE` | `FETCH_ASSOC` | I risultati sono array associativi: `$row['nome']` |
| `ATTR_EMULATE_PREPARES` | `false` | Usa prepared statements reali del database (più sicuri) |

#### 4. Funzione helper `getDB()`

```php
function getDB() {
    return Database::getInstance()->getConnection();
}
```

Semplifica il codice: invece di scrivere `Database::getInstance()->getConnection()` ogni volta, basta `getDB()`.

---

### Domande frequenti dei professori

**D: Perché usate il Singleton e non una semplice variabile globale?**
> Il Singleton incapsula la logica di connessione nella classe stessa. Una variabile globale sarebbe accessibile e modificabile da chiunque, violando il principio di **incapsulamento** della programmazione orientata agli oggetti.

**D: Cosa succede se il database non è raggiungibile?**
> Il blocco `try/catch` nel costruttore cattura l'eccezione `PDOException` e mostra un messaggio di errore con `die()`, fermando l'applicazione in modo controllato.

**D: Perché `utf8mb4` e non `utf8`?**
> `utf8` in MySQL supporta solo caratteri fino a 3 byte. `utf8mb4` supporta fino a 4 byte, necessari per emoji (🌿) e alcuni caratteri CJK (cinese, giapponese, coreano).
