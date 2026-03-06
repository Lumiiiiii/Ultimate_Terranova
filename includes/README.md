# 📁 Cartella `includes/` — Classi PHP (Logica Applicativa)

Questa cartella contiene le **classi PHP** che gestiscono la logica di business dell'applicazione. Ogni classe è responsabile di un'area specifica del gestionale.

---

## `Patient.php` — Gestione Pazienti (CRUD completo)

### Cosa fa questa classe?

La classe `Patient` implementa tutte le operazioni **CRUD** (Create, Read, Update, Delete) per la tabella `pazienti` del database, più metodi di ricerca e recupero visite.

### Struttura della classe

```php
class Patient
{
    private $db;  // proprietà privata: connessione al database

    public function __construct()
    {
        $this->db = getDB();  // ottiene la connessione dal Singleton
    }
}
```

`$this->db` è una proprietà **privata**: accessibile solo dall'interno della classe. I metodi esterni non possono accedere direttamente alla connessione, proteggendo il principio di **incapsulamento**.

### Tutti i metodi spiegati

#### `createPatient($data)` — INSERT

| Aspetto | Dettaglio |
|---|---|
| **Operazione SQL** | `INSERT INTO pazienti (...) VALUES (...)` |
| **Parametro** | `$data` = array associativo con i campi del paziente |
| **Ritorno** | L'`id` del nuovo paziente (intero) oppure `false` se errore |
| **Sicurezza** | Prepared statement con segnaposto `:nome_cognome`, `:telefono`, ecc. |

**Operatore `??` (null coalescing):**
`$data['telefono'] ?? null` significa: "se `$data['telefono']` esiste e non è `null`, usalo; altrimenti usa `null`".

**`lastInsertId()`:** metodo PDO che restituisce il valore `AUTO_INCREMENT` generato dall'ultimo `INSERT`. Utile per reindirizzare l'utente alla pagina del paziente appena creato.

---

#### `getPatient($id)` — SELECT (singolo)

| Aspetto | Dettaglio |
|---|---|
| **Operazione SQL** | `SELECT *, TIMESTAMPDIFF(...) AS eta FROM pazienti WHERE id = :id` |
| **Parametro** | `$id` = identificativo numerico del paziente |
| **Ritorno** | Array associativo con tutti i campi, inclusa `eta` calcolata |

**`TIMESTAMPDIFF(YEAR, data_nascita, CURDATE())`** è una funzione MySQL che calcola la differenza tra due date in anni. `CURDATE()` restituisce la data di oggi. Questo calcola l'età del paziente **direttamente in SQL**, evitando di farlo in PHP.

**`->fetch()`** restituisce una **sola riga** come array associativo. Usato quando cerchiamo un record specifico per ID.

---

#### `updatePatient($id, $data)` — UPDATE

| Aspetto | Dettaglio |
|---|---|
| **Operazione SQL** | `UPDATE pazienti SET ... WHERE id = :id` |
| **Parametri** | `$id` = chi aggiornare, `$data` = nuovi valori |
| **Ritorno** | `true` se aggiornato, `false` se errore |

La clausola `WHERE id = :id` è **fondamentale**: senza di essa, l'UPDATE modificherebbe **tutti** i record della tabella.

---

#### `deletePatient($id)` — DELETE

| Aspetto | Dettaglio |
|---|---|
| **Operazione SQL** | `DELETE FROM pazienti WHERE id = :id` |
| **Parametro** | `$id` = paziente da eliminare |
| **Effetto collaterale** | Grazie a `ON DELETE CASCADE`, elimina anche visite, anamnesi e prescrizioni associate |

---

#### `getRecentPatients($limit)` — SELECT con ORDER BY e LIMIT

| Aspetto | Dettaglio |
|---|---|
| **Operazione SQL** | `SELECT ... ORDER BY data_creazione DESC LIMIT :limit` |
| **Parametro** | `$limit` = numero massimo di risultati (default: 10) |
| **Ritorno** | Array di pazienti ordinati dal più recente |

**Perché `bindValue` invece di `execute`?**
```php
$query->bindValue(':limit', (int) $limit, PDO::PARAM_INT);
```
La clausola `LIMIT` in MySQL accetta solo interi. Con `execute()`, PDO converte tutto in stringhe: `LIMIT '10'` causerebbe un errore. `bindValue` con `PDO::PARAM_INT` forza il tipo intero: `LIMIT 10`.

---

#### `searchPatients($searchTerm)` — SELECT con LIKE

```php
$term = "%$searchTerm%";  // Aggiunge i jolly SQL

WHERE nome_cognome LIKE ? OR telefono LIKE ? OR email LIKE ?
```

**Come funziona `LIKE`:**
- `%` = qualsiasi sequenza di caratteri (0 o più)
- `_` = un singolo carattere qualsiasi
- `%Mario%` = trova "Mario", "Mario Rossi", "De Mario", ecc.

La ricerca avviene su **3 campi contemporaneamente** (nome, telefono, email) con `OR`.

---

#### `getAllPatients()` — SELECT tutti

Identico a `getRecentPatients` ma **senza LIMIT**: restituisce tutti i pazienti, ordinati per ID decrescente.

---

#### `getRecentVisits($limit)` — SELECT con JOIN

```sql
SELECT v.*, p.nome_cognome
FROM visite v
JOIN pazienti p ON v.paziente_id = p.id
ORDER BY v.data_visita DESC, v.id DESC
```

**Cos'è una JOIN?**
Una JOIN combina righe di **due tabelle diverse** basandosi su una condizione. In questo caso:
- `visite v` — alias `v` per la tabella visite
- `JOIN pazienti p` — unisci con la tabella pazienti (alias `p`)
- `ON v.paziente_id = p.id` — dove il `paziente_id` della visita corrisponde all'`id` del paziente

Il risultato contiene sia i campi della visita (`v.*`) che il nome del paziente (`p.nome_cognome`).

---

### Domande frequenti dei professori

**D: Perché tutte le query sono dentro blocchi try/catch?**
> Per gestire gli errori in modo controllato. Se una query fallisce (es. database disconnesso), l'eccezione viene catturata e il metodo restituisce `false` o un array vuoto, invece di far crashare l'applicazione.

**D: Perché usate una classe invece di funzioni semplici?**
> La classe permette di: (1) raggruppare tutte le operazioni sui pazienti in un unico posto, (2) condividere la connessione `$db` tra tutti i metodi senza passarla come parametro, (3) proteggere i dati interni con visibilità `private`.

**D: Qual è la differenza tra `fetch()` e `fetchAll()`?**
> `fetch()` restituisce **una sola riga** (la prima). Usato quando cerchiamo per ID (risultato unico). `fetchAll()` restituisce **tutte le righe** come array di array. Usato per liste di risultati.

---

## `Note.php` — Gestione Promemoria Veloce

### Cosa fa questa classe?

Gestisce un **unico promemoria** condiviso nella dashboard. Ha solo 2 metodi: leggere e aggiornare il testo.

### Architettura a riga singola

Invece di creare nuove righe ad ogni salvataggio, il sistema usa **sempre la stessa riga** (id = 1):

```
┌─────────────────────────────────┐
│ promemoria_veloce               │
├────┬────────────────┬───────────┤
│ id │ testo          │ data_mod  │
├────┼────────────────┼───────────┤
│ 1  │ "Ricordare..." │ 14:32:00  │  ← sempre aggiornata, mai nuove righe
└────┴────────────────┴───────────┘
```

Questa riga viene creata dallo script `db.sql`:
```sql
INSERT INTO promemoria_veloce (id, testo) VALUES (1, '');
```

### Metodi

#### `getNote()` — Legge la nota

```php
$query = $this->db->query("SELECT testo FROM promemoria_veloce WHERE id = 1");
$result = $query->fetch();
return $result ? $result['testo'] : '';
```

Nota: qui usiamo `->query()` invece di `->prepare()` perché non ci sono parametri utente nella query (il valore `1` è fisso nel codice). Non c'è rischio di SQL Injection.

**`$result ? $result['testo'] : ''`** — operatore ternario: se `$result` esiste (la riga è stata trovata), restituisci il testo; altrimenti restituisci una stringa vuota.

#### `updateNote($testo)` — Salva la nota

```php
$queryText = "UPDATE promemoria_veloce SET testo = :testo WHERE id = 1";
$query = $this->db->prepare($queryText);
return $query->execute([':testo' => $testo]);
```

Qui usiamo `->prepare()` perché `$testo` viene dall'utente e deve essere sanificato. Restituisce `true`/`false`.

---

### Domande frequenti dei professori

**D: Perché non salvare le note in un file di testo?**
> Un database offre: (1) sicurezza nelle scritture concorrenti, (2) backup automatici, (3) il campo `data_modifica` si aggiorna da solo grazie a `ON UPDATE CURRENT_TIMESTAMP`, (4) coerenza con il resto dell'applicazione.

**D: Perché `getNote()` non usa un prepared statement?**
> Perché il valore `WHERE id = 1` è **hardcoded** (fisso nel codice sorgente), non proviene dall'utente. Non c'è rischio di SQL Injection. `->query()` è più semplice e performante per query senza parametri.
