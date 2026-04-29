<?php
session_start();
include('../connexion.php');

$erreur = "";
$success = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nom      = trim($_POST['nom']);
    $prenom   = trim($_POST['prenom']);
    $cin      = trim($_POST['cin']);
    $email    = trim($_POST['email']);
    $adresse  = trim($_POST['adresse']);
    $pseudo   = trim($_POST['pseudo']);
    $password = trim($_POST['password']);

    // Vérifier si le pseudo existe déjà
    $check = $conn->prepare("SELECT * FROM agriculteur WHERE pseudo = ?");
    $check->execute([$pseudo]);

    if ($check->rowCount() > 0) {
        $erreur = "Ce pseudo est déjà utilisé, choisissez un autre.";
    } else {
        $stmt = $conn->prepare("INSERT INTO agriculteur 
            (nom, prenom, CIN, email, adresse, pseudo, password) 
            VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$nom, $prenom, $cin, $email, $adresse, $pseudo, $password]);
        $success = "Inscription réussie ! Vous pouvez vous connecter.";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Inscription Agriculteur</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body class="bg-light">

<div class="container mt-5" style="max-width: 500px;">
  <div class="card shadow p-4">
    <h3 class="text-center text-success mb-4"> Inscription Agriculteur</h3>

    <?php if($erreur): ?>
      <div class="alert alert-danger"><?= $erreur ?></div>
    <?php endif; ?>

    <?php if($success): ?>
      <div class="alert alert-success"><?= $success ?>
        <a href="login.php">Se connecter</a>
      </div>
    <?php endif; ?>

    <form method="POST" onsubmit="return valider()">

      <div class="mb-2">
        <label>Nom</label>
        <input type="text" name="nom" class="form-control" required>
      </div>

      <div class="mb-2">
        <label>Prénom</label>
        <input type="text" name="prenom" class="form-control" required>
      </div>

      <div class="mb-2">
        <label>CIN (8 chiffres)</label>
        <input type="text" id="cin" name="cin" class="form-control" required>
      </div>

      <div class="mb-2">
        <label>Email</label>
        <input type="email" name="email" class="form-control" required>
      </div>

      <div class="mb-2">
        <label>Adresse</label>
        <input type="text" name="adresse" class="form-control" required>
      </div>

      <div class="mb-2">
        <label>Pseudo (lettres uniquement)</label>
        <input type="text" id="pseudo" name="pseudo" class="form-control" required>
      </div>

      <div class="mb-3">
        <label>Mot de passe (min 8 caractères, finit par $ ou #)</label>
        <input type="password" id="password" name="password" class="form-control" required>
      </div>

      <button type="submit" class="btn btn-success w-100">S'inscrire</button>
    </form>

    <p class="text-center mt-3">
      Déjà inscrit ? <a href="login.php">Se connecter</a>
    </p>
  </div>
</div>

<script>
function valider() {
    const cin = document.getElementById('cin').value;
    const pseudo = document.getElementById('pseudo').value;
    const password = document.getElementById('password').value;

    // CIN = exactement 8 chiffres
    if (!/^\d{8}$/.test(cin)) {
        alert(" CIN doit contenir exactement 8 chiffres !");
        return false;
    }

    // Pseudo = lettres uniquement
    if (!/^[a-zA-Z]+$/.test(pseudo)) {
        alert(" Pseudo doit contenir uniquement des lettres !");
        return false;
    }

    // Mot de passe = min 8 caractères et finit par $ ou #
    if (!/^[a-zA-Z0-9]{7,}[$#]$/.test(password)) {
        alert(" Mot de passe : min 8 caractères et doit finir par $ ou #");
        return false;
    }

    return true;
}
</script>

</body>
</html>