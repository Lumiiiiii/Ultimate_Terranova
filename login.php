<?php
session_start();

// Configurazione password fissa
$password_corretta = "naturopata";
$errore = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $password_inserita = $_POST['password'] ?? '';

    if ($password_inserita === $password_corretta) {
        $_SESSION['logged_in'] = true;
        header('Location: index.php');
        exit;
    } else {
        $errore = "Password non corretta. Riprova.";
    }
}
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Accesso Riservato</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body {
            background-color: #f8f9fa;
        }
    </style>
</head>
<body>

<div class="container">
    <div class="row justify-content-center align-items-center vh-100">
        <div class="col-12 col-sm-8 col-md-6 col-lg-4">
            <div class="card shadow">
                <div class="card-body p-4">

                    <h3 class="text-center mb-4">Accesso Riservato</h3>

                    <?php if (!empty($errore)): ?>
                        <div class="alert alert-danger">
                            <?php echo $errore; ?>
                        </div>
                    <?php endif; ?>

                    <form method="POST" action="login.php">
                        <div class="mb-3">
                            <label for="password" class="form-label">Password</label>
                            <input 
                                type="password" 
                                name="password" 
                                id="password" 
                                class="form-control" 
                                placeholder="Inserisci la password"
                                required
                            >
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary">
                                Entra
                            </button>
                        </div>
                    </form>

                </div>

                <div class="card-footer text-center text-muted">
                    &copy; <?php echo date("Y"); ?> Area Riservata
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
